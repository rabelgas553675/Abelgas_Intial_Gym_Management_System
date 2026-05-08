<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Carbon\Carbon;
use App\Services\Algorithms\BinarySearch;
use App\Services\Algorithms\MergeSort;
use App\Services\Algorithms\GreedyScheduler;
use App\Services\Algorithms\GraphManager;

/**
 * BenchmarkDsa — Optimized DSA Benchmark Command
 *
 * Benchmarks the following optimized algorithms implemented in the
 * IRONFORGE Gym Management System:
 *
 *   1. Binary Search Lookup          — O(log n) member retrieval
 *   2. Indexed Payment Retrieval     — O(log n) payment lookup via binary search
 *   3. Merge Sort Listing            — O(n log n) stable member/payment sorting
 *   4. Greedy Membership Scheduling  — O(1) expiry computation per member
 *   5. BFS/DFS Instructor Traversal  — O(V+E) adjacency-list graph traversal
 *
 * Usage:
 *   php artisan benchmark:dsa
 *   php artisan benchmark:dsa --size=small      (100 records,  default)
 *   php artisan benchmark:dsa --size=medium     (500 records)
 *   php artisan benchmark:dsa --size=large      (1000 records)
 *   php artisan benchmark:dsa --size=all        (runs all three sizes)
 *   php artisan benchmark:dsa --iterations=5    (default: 5 runs per operation)
 *   php artisan benchmark:dsa --format=csv      (CSV output for spreadsheet)
 *
 * Timing: microtime(true) — results reported in milliseconds (ms)
 */
class BenchmarkDsa extends Command
{
    protected $signature = 'benchmark:dsa
                            {--size=all        : Dataset size: small|medium|large|all}
                            {--iterations=5    : Number of timed runs per operation (default 5)}
                            {--format=table    : Output format: table|csv}';

    protected $description = 'Benchmark optimized DSA algorithms for IRONFORGE Gym Management System';

    // ── Terminal colour codes ────────────────────────────────────────────────
    private const C_HEADER  = "\e[1;36m";   // bold cyan
    private const C_TITLE   = "\e[1;37m";   // bold white
    private const C_LABEL   = "\e[0;33m";   // yellow
    private const C_VALUE   = "\e[0;32m";   // green
    private const C_DIM     = "\e[0;90m";   // dark grey
    private const C_RESET   = "\e[0m";

    // ── Dataset size map ─────────────────────────────────────────────────────
    private const SIZES = [
        'small'  => 100,
        'medium' => 500,
        'large'  => 1000,
    ];

    // ── Collected results per dataset size ───────────────────────────────────
    private array $allResults = [];

    // ────────────────────────────────────────────────────────────────────────
    //  Entry point
    // ────────────────────────────────────────────────────────────────────────

    public function handle(): int
    {
        $sizeOpt   = strtolower($this->option('size'));
        $iterations = max(1, (int) $this->option('iterations'));
        $format    = $this->option('format');

        $this->printBanner($iterations);

        $sizesToRun = $sizeOpt === 'all'
            ? array_keys(self::SIZES)
            : (array_key_exists($sizeOpt, self::SIZES) ? [$sizeOpt] : null);

        if ($sizesToRun === null) {
            $this->error("Unknown size '{$sizeOpt}'. Use: small | medium | large | all");
            return 1;
        }

        foreach ($sizesToRun as $size) {
            $count   = self::SIZES[$size];
            $dataset = $this->generateDataset($count);

            $this->line('');
            $this->line(self::C_HEADER . "  ══ " . strtoupper($size) . " DATASET ({$count} records) ══" . self::C_RESET);
            $this->line('');

            $results = $this->runAllBenchmarks($dataset, $count, $iterations);
            $this->allResults[$size] = ['count' => $count, 'results' => $results];

            if ($format === 'table') {
                $this->printTable($results, $size, $count);
            }
        }

        if ($format === 'csv') {
            $this->printCsvAll();
        }

        $this->line('');
        $this->line(self::C_HEADER . '  Benchmark complete.' . self::C_RESET);
        $this->line('');

        return 0;
    }

    // ────────────────────────────────────────────────────────────────────────
    //  Dataset Generator
    //
    //  Automatically generates synthetic member and payment records.
    //  No database connection required — all data is generated in memory.
    //  Dataset sizes: small=100, medium=500, large=1000.
    // ────────────────────────────────────────────────────────────────────────

    private function generateDataset(int $count): array
    {
        $membershipTypes = ['Monthly', 'Quarterly', 'Semi-Annual', 'Annually'];
        $members  = [];
        $payments = [];

        for ($i = 1; $i <= $count; $i++) {
            $startDate = Carbon::now()->subDays(rand(1, 365))->toDateString();

            $members[] = [
                'id'              => $i,
                'name'            => 'Member_' . str_pad($i, 4, '0', STR_PAD_LEFT),
                'email'           => "member{$i}@ironforge.ph",
                'membership_type' => $membershipTypes[$i % 4],
                'start_date'      => $startDate,
                'instructor_id'   => ($i % 10) + 1,
                'status'          => $i % 5 === 0 ? 'inactive' : 'active',
            ];

            $payments[] = [
                'id'           => $i,
                'member_id'    => $i,
                'amount'       => rand(500, 5000),
                'payment_date' => Carbon::now()->subDays(rand(1, 90))->toDateString(),
                'method'       => ['Cash', 'GCash', 'Card'][$i % 3],
            ];
        }

        // Shuffle members to make search non-trivial
        shuffle($members);

        return compact('members', 'payments');
    }

    // ────────────────────────────────────────────────────────────────────────
    //  Run All Benchmarks
    // ────────────────────────────────────────────────────────────────────────

    private function runAllBenchmarks(array $dataset, int $count, int $iterations): array
    {
        $members  = $dataset['members'];
        $payments = $dataset['payments'];
        $results  = [];

        // 1. Binary Search Lookup — O(log n)
        $this->line(self::C_LABEL . "  [1/5] Binary Search Lookup..." . self::C_RESET);
        $sorted = MergeSort::sortBy($members, 'name', 'asc');
        $target = 'Member_' . str_pad((int)($count / 2), 4, '0', STR_PAD_LEFT);
        $runs   = $this->benchmark($iterations, function () use ($sorted, $target) {
            BinarySearch::searchByField($sorted, 'name', $target);
        });
        $results[] = ['operation' => 'Binary Search Lookup', 'runs' => $runs];

        // 2. Indexed Payment Retrieval — Binary Search on sorted payments
        $this->line(self::C_LABEL . "  [2/5] Indexed Payment Retrieval..." . self::C_RESET);
        $sortedPayments = MergeSort::sortBy($payments, 'member_id', 'asc');
        $targetMemberId = (int)($count / 2);
        $runs = $this->benchmark($iterations, function () use ($sortedPayments, $targetMemberId) {
            BinarySearch::searchByField($sortedPayments, 'member_id', $targetMemberId);
        });
        $results[] = ['operation' => 'Indexed Payment Retrieval', 'runs' => $runs];

        // 3. Merge Sort Listing — O(n log n)
        $this->line(self::C_LABEL . "  [3/5] Merge Sort Listing..." . self::C_RESET);
        $runs = $this->benchmark($iterations, function () use ($members) {
            MergeSort::sortBy($members, 'name', 'asc');
        });
        $results[] = ['operation' => 'Merge Sort Listing', 'runs' => $runs];

        // 4. Greedy Membership Scheduling — O(1) per member
        $this->line(self::C_LABEL . "  [4/5] Greedy Membership Scheduling..." . self::C_RESET);
        $runs = $this->benchmark($iterations, function () use ($members) {
            foreach ($members as $m) {
                $start = Carbon::parse($m['start_date']);
                GreedyScheduler::computeEndDate($start, $m['membership_type']);
            }
        });
        $results[] = ['operation' => 'Greedy Membership Scheduling', 'runs' => $runs];

        // 5. BFS/DFS Instructor Graph Traversal — O(V+E)
        // GraphManager::buildFromMembers() uses object-property access ($member->id,
        // $member->instructor_id), so we cast each synthetic array to stdClass first.
        $this->line(self::C_LABEL . "  [5/5] BFS/DFS Instructor Graph Traversal..." . self::C_RESET);
        $instructorIds  = array_unique(array_column($members, 'instructor_id'));
        $memberObjects  = array_map(fn($m) => (object) $m, $members);
        $runs = $this->benchmark($iterations, function () use ($memberObjects, $instructorIds) {
            $graph = GraphManager::buildFromMembers($memberObjects);
            foreach ($instructorIds as $id) {
                $graph->bfsData($id);
            }
        });
        $results[] = ['operation' => 'BFS/DFS Instructor Graph Traversal', 'runs' => $runs];

        return $results;
    }

    // ────────────────────────────────────────────────────────────────────────
    //  Timing Core
    //
    //  Executes $fn exactly $iterations times.
    //  Uses microtime(true) for sub-millisecond accuracy.
    //  Returns times in milliseconds (ms).
    // ────────────────────────────────────────────────────────────────────────

    private function benchmark(int $iterations, callable $fn): array
    {
        $times = [];

        for ($i = 0; $i < $iterations; $i++) {
            $start   = microtime(true);
            $fn();
            $end     = microtime(true);
            $times[] = round(($end - $start) * 1000, 4); // convert to ms
        }

        return $times;
    }

    // ────────────────────────────────────────────────────────────────────────
    //  Output: Console Table
    // ────────────────────────────────────────────────────────────────────────

    private function printTable(array $results, string $size, int $count): void
    {
        $label = strtoupper($size) . " DATASET ({$count} RECORDS)";

        $this->line('');
        $this->line(self::C_HEADER
            . "  ┌─────────────────────────────────────────────────────────────────────────────────────┐"
            . self::C_RESET);
        $this->line(self::C_HEADER
            . "  │  OPTIMIZED SYSTEM — {$label}" . str_repeat(' ', max(0, 67 - strlen($label))) . "│"
            . self::C_RESET);
        $this->line(self::C_HEADER
            . "  ├─────────────────────────────┬──────────┬──────────┬──────────┬──────────┬──────────┬────────────┤"
            . self::C_RESET);
        $this->line(self::C_HEADER
            . "  │ Operation                   │  Run 1   │  Run 2   │  Run 3   │  Run 4   │  Run 5   │  Avg (ms)  │"
            . self::C_RESET);
        $this->line(self::C_HEADER
            . "  ├─────────────────────────────┼──────────┼──────────┼──────────┼──────────┼──────────┼────────────┤"
            . self::C_RESET);

        foreach ($results as $r) {
            $avg  = round(array_sum($r['runs']) / count($r['runs']), 4);
            $name = $this->pad($r['operation'], 27);
            $row  = sprintf(
                "  │ %s │ %s │ %s │ %s │ %s │ %s │ %s │",
                $name,
                $this->pad(number_format($r['runs'][0], 4), 8),
                $this->pad(number_format($r['runs'][1], 4), 8),
                $this->pad(number_format($r['runs'][2], 4), 8),
                $this->pad(number_format($r['runs'][3], 4), 8),
                $this->pad(number_format($r['runs'][4], 4), 8),
                self::C_VALUE . $this->pad(number_format($avg, 4), 10) . self::C_RESET
            );
            $this->line($row);
        }

        $this->line(self::C_HEADER
            . "  └─────────────────────────────┴──────────┴──────────┴──────────┴──────────┴──────────┴────────────┘"
            . self::C_RESET);
        $this->line('');
        $this->line(self::C_DIM . "  Timing unit: ms = milliseconds | Measured using microtime(true)" . self::C_RESET);
    }

    // ────────────────────────────────────────────────────────────────────────
    //  Output: CSV
    // ────────────────────────────────────────────────────────────────────────

    private function printCsvAll(): void
    {
        $this->line('');
        $this->line('size,count,operation,run_1_ms,run_2_ms,run_3_ms,run_4_ms,run_5_ms,average_ms');
        foreach ($this->allResults as $size => $data) {
            foreach ($data['results'] as $r) {
                $avg = round(array_sum($r['runs']) / count($r['runs']), 4);
                $this->line(implode(',', [
                    $size,
                    $data['count'],
                    '"' . $r['operation'] . '"',
                    ...$r['runs'],
                    $avg,
                ]));
            }
        }
    }

    // ────────────────────────────────────────────────────────────────────────
    //  Banner
    // ────────────────────────────────────────────────────────────────────────

    private function printBanner(int $iterations): void
    {
        $this->line('');
        $this->line(self::C_HEADER . '  ╔════════════════════════════════════════════════════════════════╗' . self::C_RESET);
        $this->line(self::C_HEADER . '  ║     IRONFORGE GYM — Optimized DSA Benchmark Suite             ║' . self::C_RESET);
        $this->line(self::C_HEADER . '  ╚════════════════════════════════════════════════════════════════╝' . self::C_RESET);
        $this->line('');
        $this->line(self::C_DIM . '  Algorithms : Binary Search | Merge Sort | Greedy Scheduler | BFS/DFS Graph' . self::C_RESET);
        $this->line(self::C_DIM . '  Timing     : microtime(true) → milliseconds (ms)' . self::C_RESET);
        $this->line(self::C_DIM . '  Runs       : ' . $iterations . ' iterations per operation (averaged)' . self::C_RESET);
        $this->line(self::C_DIM . '  Dataset    : auto-generated synthetic records (no DB required)' . self::C_RESET);
    }

    // ── Utility ──────────────────────────────────────────────────────────────
    private function pad(string $str, int $len): string
    {
        return mb_strlen($str) > $len
            ? mb_substr($str, 0, $len - 1) . '…'
            : str_pad($str, $len);
    }
}