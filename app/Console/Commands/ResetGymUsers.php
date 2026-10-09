<?php

namespace App\Console\Commands;

use App\Models\Member;
use App\Models\User;
use App\Services\AuditLogger;
use Database\Seeders\GymDemoUsersSeeder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Symfony\Component\Process\ExecutableFinder;
use Symfony\Component\Process\Process;
use Throwable;

/**
 * gym:reset-users
 *
 * Leaves exactly 1 admin, 1 staff, 2 instructors and 4 members (the accounts in
 * GymDemoUsersSeeder::ACCOUNTS) and removes every other user account together
 * with the data that belongs ONLY to those accounts.
 *
 * What it will never do
 *   - run on APP_ENV=production (no override exists)
 *   - run in a non local/testing environment without --allow-non-local
 *   - delete anything without a verified, fresh backup
 *   - delete payments, attendance, workout plans, coach requests, coach fees,
 *     walk-in payments, payment settings, audit logs or failed jobs
 *   - touch the schema, run migrate:fresh / db:wipe or truncate a table
 *
 * If a removable account owns history that the database protects (RESTRICT) or
 * that would be destroyed by a CASCADE (coach fees, workout plans, coach
 * requests, coach-fee payments), the command STOPS and changes nothing.
 *
 * Usage
 *   php artisan gym:reset-users --dry-run        # show the plan only
 *   php artisan gym:reset-users                  # backup, confirm, reset, verify
 *   php artisan gym:reset-users --verify-only    # run the checks, change nothing
 */
class ResetGymUsers extends Command
{
    protected $signature = 'gym:reset-users
        {--dry-run : Show exactly what would be removed and stop. Changes nothing}
        {--verify-only : Only run the post-reset verification checks. Changes nothing}
        {--backup-file= : Path to a backup you just made yourself (used instead of the automatic backup)}
        {--mysqldump= : Full path to mysqldump / mariadb-dump if it is not on PATH}
        {--yes : Skip the typed confirmation (the backup is still required)}
        {--allow-non-local : Required when APP_ENV is anything other than local or testing}';

    protected $description = 'Reset users to the 8 designated demo accounts (guarded, backed up, transactional)';

    /** Tables whose row counts must be identical before and after the reset. */
    private const PRESERVED = [
        'payments', 'walk_in_payments', 'payment_settings', 'instructor_fees',
        'coach_requests', 'workout_plans', 'attendances', 'attendance',
        'audit_logs', 'failed_jobs',
    ];

    /** History that makes a removable instructor/user un-removable. [table, column, label] */
    private const USER_BLOCKERS = [
        ['instructor_fees', 'instructor_id', 'coach fee record(s)'],
        ['coach_requests',  'instructor_id', 'coach request(s) as instructor'],
        ['workout_plans',   'instructor_id', 'workout plan(s)'],
        ['payments',        'instructor_id', 'coach-fee payment(s)'],
    ];

    /** Rows that SURVIVE but lose the user link (FK ON DELETE SET NULL). */
    private const USER_DETACH = [
        ['payments',         'processed_by',  'payment(s) processed'],
        ['attendances',      'staff_user_id', 'attendance entries scanned'],
        ['walk_in_payments', 'processed_by',  'walk-in payment(s) processed'],
        ['coach_requests',   'requested_by',  'coach request(s) created'],
        ['audit_logs',       'user_id',       'audit-log entries'],
        ['members',          'instructor_id', 'kept member(s) assigned as coach'],
    ];

    // ═════════════════════════════════════════════════════════════════════
    //  Entry point
    // ═════════════════════════════════════════════════════════════════════

    public function handle(): int
    {
        if (! $this->environmentAllowed()) {
            return self::FAILURE;
        }

        if ($this->option('verify-only')) {
            $checks = $this->verify(null);
            $this->renderChecks($checks);

            return $this->allPassed($checks) ? self::SUCCESS : self::FAILURE;
        }

        $plan = $this->buildPlan();
        $this->renderPlan($plan);

        if ($plan['blockers'] !== []) {
            $this->newLine();
            $this->error('STOPPED — protected history was found. Nothing was changed.');
            $this->line('Those accounts own records this reset is not allowed to destroy. Resolve them');
            $this->line('(reassign / archive through the application) and run the command again.');

            return self::FAILURE;
        }

        if ($this->option('dry-run')) {
            $this->newLine();
            $this->info('Dry run complete. Nothing was changed.');

            return self::SUCCESS;
        }

        $this->printWarning($plan);

        $backup = $this->ensureBackup();
        if ($backup === null) {
            return self::FAILURE;
        }

        if (! $this->confirmed()) {
            $this->warn('Aborted. Nothing was changed.');

            return self::FAILURE;
        }

        $before = $this->snapshot();

        try {
            $result = $this->performReset($before);
        } catch (Throwable $e) {
            $this->newLine();
            $this->error('RESET FAILED AND WAS ROLLED BACK — no data was changed.');
            $this->line('Reason: ' . $e->getMessage());
            $this->line('Backup kept at: ' . $backup);

            return self::FAILURE;
        }

        // Post-commit: files, one honest audit entry, then re-verify from scratch.
        $this->deleteFiles($result['files']);

        AuditLogger::log(
            'users_reset',
            'Users',
            sprintf(
                'Reset to 8 designated accounts via gym:reset-users: removed %d user(s) and %d member profile(s). Backup: %s',
                $result['users_removed'],
                $result['members_removed'],
                basename($backup)
            )
        );

        $this->newLine();
        $this->info('Reset committed. Re-verifying from a clean read…');
        $checks = $this->verify($before);
        $this->renderChecks($checks);
        $this->renderReport($result, $backup);

        return $this->allPassed($checks) ? self::SUCCESS : self::FAILURE;
    }

    // ═════════════════════════════════════════════════════════════════════
    //  Safety gates
    // ═════════════════════════════════════════════════════════════════════

    private function environmentAllowed(): bool
    {
        if (app()->environment('production')) {
            $this->error('Refusing to run: APP_ENV is "production". There is no override for this.');

            return false;
        }

        if (! app()->environment(['local', 'testing']) && ! $this->option('allow-non-local')) {
            $this->error('Refusing to run: APP_ENV is "' . app()->environment() . '", not local/testing.');
            $this->line('If you really mean it, add --allow-non-local.');

            return false;
        }

        return true;
    }

    private function confirmed(): bool
    {
        if ($this->option('yes')) {
            return true;
        }

        if (! $this->input->isInteractive()) {
            $this->error('Non-interactive run: pass --yes to confirm.');

            return false;
        }

        $db = DB::connection()->getDatabaseName();
        $typed = $this->ask("Type the database name ({$db}) to proceed");

        return is_string($typed) && trim($typed) === $db;
    }

    private function printWarning(array $plan): void
    {
        $db = DB::connection()->getDatabaseName();

        $this->newLine();
        $this->warn('╔══════════════════════════════════════════════════════════════════╗');
        $this->warn('║  DESTRUCTIVE OPERATION                                           ║');
        $this->warn('╚══════════════════════════════════════════════════════════════════╝');
        $this->line("  Database : <fg=yellow>{$db}</> (" . DB::connection()->getDriverName() . ')');
        $this->line('  Will permanently delete ' . count($plan['removeUsers']) . ' user account(s) and '
            . count($plan['removeMembers']) . ' member profile(s).');
        $this->line('  Payments, attendance, audit logs and failed jobs are NOT touched.');
        $this->newLine();
    }

    // ═════════════════════════════════════════════════════════════════════
    //  Backup
    // ═════════════════════════════════════════════════════════════════════

    /** @return string|null path of a verified backup, or null (error already printed) */
    private function ensureBackup(): ?string
    {
        if ($given = $this->option('backup-file')) {
            return $this->validateBackup($given, true) ? realpath($given) : null;
        }

        $driver = DB::connection()->getDriverName();
        $dir    = storage_path('app/backups');
        if (! is_dir($dir) && ! @mkdir($dir, 0775, true) && ! is_dir($dir)) {
            $this->error("Cannot create backup directory {$dir}.");

            return null;
        }

        $stamp = now()->format('Ymd_His');
        $name  = preg_replace('/[^A-Za-z0-9_.-]/', '_', basename((string) DB::connection()->getDatabaseName()));

        if ($driver === 'sqlite') {
            $src = DB::connection()->getDatabaseName();
            $dst = "{$dir}/{$name}_before_reset_{$stamp}.sqlite";
            if (! is_file($src) || ! @copy($src, $dst)) {
                $this->error('Could not copy the SQLite database for the backup.');

                return null;
            }
            $this->info("Backup written: {$dst}");

            return $dst;
        }

        if (! in_array($driver, ['mysql', 'mariadb'], true)) {
            $this->error("Automatic backup is not supported for driver '{$driver}'. Make one and pass --backup-file=...");

            return null;
        }

        $dst = "{$dir}/{$name}_before_reset_{$stamp}.sql";
        if (! $this->mysqldump($dst)) {
            $this->error('Automatic backup failed, so the reset will NOT run.');
            $this->line('Make a backup yourself (e.g. phpMyAdmin → Export, or mysqldump) and re-run with');
            $this->line('  --backup-file="path\\to\\backup.sql"      (must be less than 60 minutes old)');
            $this->line('or point to the binary with --mysqldump="C:\\xampp\\mysql\\bin\\mysqldump.exe".');

            return null;
        }

        return $this->validateBackup($dst, false) ? $dst : null;
    }

    private function mysqldump(string $dst): bool
    {
        $cfg = config('database.connections.' . DB::getDefaultConnection());

        $bin = $this->option('mysqldump') ?: (new ExecutableFinder())->find('mysqldump')
            ?: (new ExecutableFinder())->find('mariadb-dump');

        if (! $bin) {
            $this->warn('mysqldump / mariadb-dump was not found.');

            return false;
        }

        $conn = ! empty($cfg['unix_socket'])
            ? ['--socket=' . $cfg['unix_socket']]
            : ['--host=' . $cfg['host'], '--port=' . $cfg['port']];

        // --no-tablespaces first (avoids a PROCESS privilege error); retry without if unsupported.
        foreach ([['--no-tablespaces'], []] as $extra) {
            @unlink($dst);
            $cmd = array_merge(
                [$bin, '--single-transaction', '--routines', '--triggers', '--result-file=' . $dst],
                $extra, $conn,
                ['--user=' . $cfg['username'], $cfg['database']]
            );

            $p = new Process($cmd, null, ['MYSQL_PWD' => (string) ($cfg['password'] ?? '')]);
            $p->setTimeout(900);
            $p->run();

            if ($p->isSuccessful()) {
                return true;
            }
        }

        $this->line('mysqldump said: ' . trim($p->getErrorOutput()));

        return false;
    }

    private function validateBackup(string $path, bool $userSupplied): bool
    {
        if (! is_file($path) || filesize($path) < 512) {
            $this->error("Backup file missing or empty: {$path}");

            return false;
        }

        if ($userSupplied && (time() - filemtime($path)) > 3600) {
            $this->error('That backup is more than 60 minutes old. Make a fresh one so it matches the current data.');

            return false;
        }

        if (str_ends_with(strtolower($path), '.sql')) {
            $head = file_get_contents($path, false, null, 0, 4_000_000);
            $tail = file_get_contents($path, false, null, max(0, filesize($path) - 4096));

            if (stripos($head, 'CREATE TABLE') === false || ! preg_match('/`?users`?/i', $head)) {
                $this->error('The backup does not look like a dump of this database (no CREATE TABLE ... users).');

                return false;
            }

            // mysqldump ends with "-- Dump completed"; phpMyAdmin exports end with COMMIT/SET lines.
            if ($tail !== false && stripos($tail, 'Dump completed') === false
                && stripos($tail, 'COMMIT') === false && stripos($tail, 'SET ') === false) {
                $this->error('The backup file looks truncated (no end-of-dump marker).');

                return false;
            }
        }

        $this->info('Verified backup: ' . $path . ' (' . number_format(filesize($path)) . ' bytes)');

        return true;
    }

    // ═════════════════════════════════════════════════════════════════════
    //  Plan (read-only)
    // ═════════════════════════════════════════════════════════════════════

    private function buildPlan(): array
    {
        $designated = GymDemoUsersSeeder::emails();
        $memberMail = GymDemoUsersSeeder::memberEmails();

        $users = User::orderBy('id')->get();
        $keep  = $users->filter(fn ($u) => in_array(strtolower($u->email), $designated, true));
        $drop  = $users->reject(fn ($u) => in_array(strtolower($u->email), $designated, true));

        // Member profiles to keep = profile of a kept designated MEMBER user.
        $keepMemberUserIds = $keep->filter(fn ($u) => in_array(strtolower($u->email), $memberMail, true))
            ->pluck('id')->all();

        $allMembers  = Member::orderBy('id')->get();
        $dropMembers = $allMembers->reject(fn ($m) => $m->user_id !== null && in_array($m->user_id, $keepMemberUserIds, true));

        $dropUserIds   = $drop->pluck('id')->all();
        $dropMemberIds = $dropMembers->pluck('id')->all();

        // ── per-user dependency counts (one grouped query per table) ─────
        $blockCounts  = $this->groupedCounts(self::USER_BLOCKERS, $dropUserIds, null);
        $detachCounts = $this->groupedCounts(self::USER_DETACH, $dropUserIds, $dropMemberIds);

        $blockers = [];
        $userRows = [];
        foreach ($drop as $u) {
            $b = $blockCounts[$u->id] ?? [];
            $d = $detachCounts[$u->id] ?? [];

            foreach ($b as $label => $n) {
                $blockers[] = "User #{$u->id} {$u->name} <{$u->email}> ({$u->role}) owns {$n} {$label}";
            }
            $userRows[] = ['user' => $u, 'blockers' => $b, 'detach' => $d];
        }

        $memberRows = [];
        foreach ($dropMembers as $m) {
            $b = $m->deletionBlockers();
            foreach ($b as $label => $n) {
                $blockers[] = "Member #{$m->id} {$m->name} <{$m->email}> has {$n} {$label} record(s)";
            }
            $memberRows[] = ['member' => $m, 'blockers' => $b];
        }

        return [
            'keep'          => $keep,
            'removeUsers'   => $userRows,
            'removeMembers' => $memberRows,
            'blockers'      => $blockers,
            'notifications' => $this->tableHas('notifications')
                ? DB::table('notifications')->where('notifiable_type', User::class)->whereIn('notifiable_id', $dropUserIds ?: [0])->count() : 0,
            'sessions'      => $this->tableHas('sessions', 'user_id')
                ? DB::table('sessions')->whereIn('user_id', $dropUserIds ?: [0])->count() : 0,
            'qrTokens'      => $this->tableHas('user_qr_tokens')
                ? DB::table('user_qr_tokens')->whereIn('user_id', $dropUserIds ?: [0])->count() : 0,
        ];
    }

    /**
     * @param  array<int,array{0:string,1:string,2:string}> $defs
     * @return array<int,array<string,int>>  user_id => [label => count]
     */
    private function groupedCounts(array $defs, array $userIds, ?array $excludeMemberIds): array
    {
        $out = [];
        if ($userIds === []) {
            return $out;
        }

        foreach ($defs as [$table, $col, $label]) {
            if (! $this->tableHas($table, $col)) {
                continue;
            }

            $q = DB::table($table)->selectRaw("{$col} as uid, COUNT(*) as c")->whereIn($col, $userIds);

            if ($table === 'members' && $excludeMemberIds) {
                $q->whereNotIn('id', $excludeMemberIds);
            }

            foreach ($q->groupBy($col)->get() as $row) {
                if ((int) $row->c > 0) {
                    $out[(int) $row->uid][$label] = (int) $row->c;
                }
            }
        }

        return $out;
    }

    private function renderPlan(array $plan): void
    {
        $this->newLine();
        $this->line('<options=bold>RESET PLAN</>  (' . DB::connection()->getDatabaseName() . ')');
        $this->newLine();

        $this->line('<fg=green>KEEP</> — designated accounts already present: ' . $plan['keep']->count() . ' of 8');
        $present = $plan['keep']->pluck('email')->map('strtolower')->all();
        foreach (GymDemoUsersSeeder::ACCOUNTS as $a) {
            $has = in_array(strtolower($a['email']), $present, true);
            $this->line(sprintf('   %s %-11s %-22s %s', $has ? '✔' : '＋', $a['role'], $a['name'], $a['email'])
                . ($has ? '' : '   (will be created)'));
        }

        $this->newLine();
        $this->line('<fg=red>REMOVE</> — ' . count($plan['removeUsers']) . ' user account(s):');
        if ($plan['removeUsers'] === []) {
            $this->line('   (none)');
        }
        foreach ($plan['removeUsers'] as $r) {
            $u = $r['user'];
            $this->line(sprintf('   ✘ #%-4d %-11s %-24s %s', $u->id, $u->role, $u->name, $u->email));
            foreach ($r['detach'] as $label => $n) {
                $this->line("        <fg=gray>keeps {$n} {$label} (user link cleared)</>");
            }
            foreach ($r['blockers'] as $label => $n) {
                $this->line("        <fg=red>BLOCKED by {$n} {$label}</>");
            }
        }

        $this->newLine();
        $this->line('<fg=red>REMOVE</> — ' . count($plan['removeMembers']) . ' member profile(s):');
        if ($plan['removeMembers'] === []) {
            $this->line('   (none)');
        }
        foreach ($plan['removeMembers'] as $r) {
            $m = $r['member'];
            $this->line(sprintf('   ✘ #%-4d %-24s %s', $m->id, $m->name, $m->email));
            foreach ($r['blockers'] as $label => $n) {
                $this->line("        <fg=red>BLOCKED by {$n} {$label} record(s)</>");
            }
        }

        $this->newLine();
        $this->line("Also removed with them: {$plan['qrTokens']} staff QR token(s), {$plan['notifications']} notification(s), {$plan['sessions']} session(s).");
        $this->line('<fg=green>PRESERVED untouched:</> ' . implode(', ', array_filter(self::PRESERVED, fn ($t) => $this->tableHas($t))) . ', storage/logs/*');

        if ($plan['blockers'] !== []) {
            $this->newLine();
            $this->line('<fg=red;options=bold>PROTECTED HISTORY FOUND</>');
            foreach ($plan['blockers'] as $b) {
                $this->line('   • ' . $b);
            }
        }
    }

    // ═════════════════════════════════════════════════════════════════════
    //  Execution
    // ═════════════════════════════════════════════════════════════════════

    private function performReset(array $before): array
    {
        return DB::transaction(function () use ($before) {
            // Re-plan INSIDE the transaction so nothing can slip in between the
            // dry view above and the delete.
            $plan = $this->buildPlan();
            if ($plan['blockers'] !== []) {
                throw new RuntimeException('Protected history appeared while resetting: ' . implode('; ', $plan['blockers']));
            }

            $dropUserIds = array_map(fn ($r) => $r['user']->id, $plan['removeUsers']);
            $files       = $this->collectFiles($plan);

            // 1) Member profiles first (model guard + RESTRICT FKs still apply).
            foreach ($plan['removeMembers'] as $r) {
                $locked = Member::whereKey($r['member']->getKey())->lockForUpdate()->first();
                $locked?->delete();
            }

            // 2) Rows that belong only to the removed users.
            $notifs = $sessions = 0;
            if ($dropUserIds) {
                if ($this->tableHas('notifications')) {
                    $notifs = DB::table('notifications')->where('notifiable_type', User::class)
                        ->whereIn('notifiable_id', $dropUserIds)->delete();
                }
                if ($this->tableHas('sessions', 'user_id')) {
                    $sessions = DB::table('sessions')->whereIn('user_id', $dropUserIds)->delete();
                }
                if ($this->tableHas('password_reset_tokens', 'email')) {
                    DB::table('password_reset_tokens')
                        ->whereIn('email', array_map(fn ($r) => $r['user']->email, $plan['removeUsers']))->delete();
                }
            }

            // 3) The users (cascades only user_qr_tokens — verified not to hit history).
            foreach ($plan['removeUsers'] as $r) {
                User::whereKey($r['user']->getKey())->first()?->delete();
            }

            // 4) Create / align the eight designated accounts.
            app(GymDemoUsersSeeder::class)->run();

            // 5) Verify BEFORE committing — any failure rolls everything back.
            $checks = $this->verify($before, inTransaction: true);
            if (! $this->allPassed($checks)) {
                $failed = collect($checks)->where('ok', false)->pluck('name')->implode(', ');
                throw new RuntimeException("Post-reset verification failed ({$failed}).");
            }

            return [
                'users_removed'   => count($plan['removeUsers']),
                'members_removed' => count($plan['removeMembers']),
                'removed_users'   => collect($plan['removeUsers'])->map(fn ($r) => "{$r['user']->name} <{$r['user']->email}> ({$r['user']->role})")->all(),
                'removed_members' => collect($plan['removeMembers'])->map(fn ($r) => "{$r['member']->name} <{$r['member']->email}>")->all(),
                'qr_tokens'       => $plan['qrTokens'],
                'notifications'   => $notifs,
                'sessions'        => $sessions,
                'files'           => $files,
            ];
        });
    }

    /** Files that only the removed records referenced (deleted after commit). */
    private function collectFiles(array $plan): array
    {
        $files = [];
        foreach ($plan['removeMembers'] as $r) {
            $files[] = $r['member']->photo;
            $files[] = $r['member']->qr_code_path;
        }
        $ids = [];
        foreach ($plan['removeUsers'] as $r) {
            $files[] = $r['user']->photo;
            $ids[]   = $r['user']->id;
        }
        if ($ids && $this->tableHas('user_qr_tokens', 'qr_code_path')) {
            $files = array_merge($files, DB::table('user_qr_tokens')->whereIn('user_id', $ids)->pluck('qr_code_path')->all());
        }

        return array_values(array_unique(array_filter($files)));
    }

    private function deleteFiles(array $files): void
    {
        // Never delete a file a surviving record still points at.
        $stillUsed = collect(array_merge(
            User::pluck('photo')->all(),
            Member::pluck('photo')->all(),
            Member::pluck('qr_code_path')->all(),
            $this->tableHas('user_qr_tokens', 'qr_code_path') ? DB::table('user_qr_tokens')->pluck('qr_code_path')->all() : []
        ))->filter()->all();

        foreach ($files as $f) {
            if (! in_array($f, $stillUsed, true)) {
                Storage::disk('public')->delete($f);
            }
        }
    }

    // ═════════════════════════════════════════════════════════════════════
    //  Verification
    // ═════════════════════════════════════════════════════════════════════

    /** @return array<int,array{name:string,ok:bool|null,detail:string}>  ok=null means "note" */
    private function verify(?array $before, bool $inTransaction = false): array
    {
        $checks = [];
        $add = function (string $name, ?bool $ok, string $detail = '') use (&$checks) {
            $checks[] = ['name' => $name, 'ok' => $ok, 'detail' => $detail];
        };

        $users = User::orderBy('id')->get();
        $emails = $users->pluck('email')->map('strtolower')->sort()->values()->all();
        $want = collect(GymDemoUsersSeeder::emails())->sort()->values()->all();

        $add('Exactly 8 user accounts', $users->count() === 8, 'found ' . $users->count());

        $byRole = $users->countBy('role');
        $roleOk = ($byRole['admin'] ?? 0) === 1 && ($byRole['staff'] ?? 0) === 1
            && ($byRole['instructor'] ?? 0) === 2 && ($byRole['member'] ?? 0) === 4 && $byRole->count() === 4;
        $add('Roles: 1 admin / 1 staff / 2 instructors / 4 members', $roleOk, $byRole->map(fn ($n, $r) => "$r=$n")->implode(' '));

        $add('Account e-mails are exactly the designated set', $emails === $want,
            $emails === $want ? '' : 'diff: ' . implode(', ', array_merge(array_diff($emails, $want), array_diff($want, $emails))));

        $names = true;
        foreach (GymDemoUsersSeeder::ACCOUNTS as $a) {
            $u = $users->first(fn ($x) => strtolower($x->email) === strtolower($a['email']));
            if (! $u || $u->name !== $a['name'] || $u->role !== $a['role']) {
                $names = false;
            }
        }
        $add('Names and role values match the spec', $names);

        // Manage Users page = User::where('role', …) lists
        $add('Manage Users counts (admin/staff/instructor/member)',
            User::where('role', 'admin')->count() === 1 && User::where('role', 'staff')->count() === 1
            && User::where('role', 'instructor')->count() === 2 && User::where('role', 'member')->count() === 4,
            'admin ' . User::where('role', 'admin')->count() . ', staff ' . User::where('role', 'staff')->count()
            . ', instructors ' . User::where('role', 'instructor')->count() . ', members ' . User::where('role', 'member')->count());

        // Members index = Member model
        $memberUserIds = $users->where('role', 'member')->pluck('id')->sort()->values()->all();
        $profiles = Member::orderBy('id')->get();
        $profileIds = $profiles->pluck('user_id')->sort()->values()->all();
        $add('Members index contains only the 4 designated members',
            $profiles->count() === 4 && $profileIds === $memberUserIds,
            $profiles->pluck('name')->implode(', '));

        $instr = User::where('role', 'instructor')->orderBy('name')->pluck('name')->all();
        $add('Instructor list is only Rico Valdez and Carla Mendoza', $instr === ['Carla Mendoza', 'Rico Valdez'], implode(', ', $instr));

        // Password hashing + authentication
        $unhashed = $users->filter(fn ($u) => (Hash::info($u->password)['algoName'] ?? 'unknown') === 'unknown')->pluck('email')->all();
        $add('All passwords are hashed', $unhashed === [], $unhashed ? 'plain: ' . implode(', ', $unhashed) : '');

        $badLogin = [];
        foreach ($users as $u) {
            if (! Auth::guard('web')->validate(['email' => $u->email, 'password' => GymDemoUsersSeeder::PASSWORD])) {
                $badLogin[] = $u->email;
            }
        }
        // After a reset the default password MUST work; in --verify-only on a live DB a user may
        // legitimately have changed it, so that is only a note.
        $add('Authentication works with the default password',
            $badLogin === [] ? true : ($before === null ? null : false),
            $badLogin ? 'not default: ' . implode(', ', $badLogin) : 'all 8 validate');

        // Profiles
        $bad = [];
        foreach ($profiles as $m) {
            $u = $users->firstWhere('id', $m->user_id);
            if (! $u || strtolower($u->email) !== strtolower($m->email) || ! $m->qr_token || ! $m->qr_id) {
                $bad[] = $m->email;
            }
        }
        $add('Member profiles valid (linked user, matching e-mail, QR id/token)', $bad === [], implode(', ', $bad));

        $noTok = [];
        foreach ($users->whereIn('role', ['admin', 'staff', 'instructor']) as $u) {
            $t = DB::table('user_qr_tokens')->where('user_id', $u->id)->first();
            if (! $t || $t->role !== $u->role || ! $t->qr_token) {
                $noTok[] = $u->email;
            }
        }
        $add('Admin/staff/instructor QR tokens present', $noTok === [], implode(', ', $noTok));

        // Referential integrity
        $orph = $this->orphans();
        $add('No orphaned foreign-key rows in any table', $orph === [], implode('; ', $orph));

        $fkOn = DB::connection()->getDriverName() === 'sqlite'
            ? true
            : (bool) (DB::selectOne('SELECT @@foreign_key_checks AS v')->v ?? 1);
        $add('Foreign-key checks are enabled', $fkOn);

        $weak = [];
        foreach (['payments', 'attendances', 'instructor_fees', 'workout_plans', 'coach_requests'] as $t) {
            if (! Schema::hasTable($t)) {
                continue;
            }
            $fk = collect(Schema::getForeignKeys($t))->first(fn ($f) => $f['columns'] === ['member_id'] && $f['foreign_table'] === 'members');
            if (! $fk || ! in_array(strtolower((string) $fk['on_delete']), ['restrict', 'no action'], true)) {
                $weak[] = $t;
            }
        }
        $add('Delete protections intact (member_id RESTRICT on history tables + model guard)',
            $weak === [] && method_exists(Member::class, 'deletionBlockers'), $weak ? 'changed: ' . implode(', ', $weak) : '');

        // Preservation (reset runs only)
        if ($before !== null) {
            $now = $this->snapshot();
            $changed = [];
            foreach (self::PRESERVED as $t) {
                if (! isset($before[$t])) {
                    continue;
                }
                // audit_logs may legitimately gain the single "users_reset" entry after commit.
                $ok = $t === 'audit_logs' && ! $inTransaction ? $now[$t] >= $before[$t] : $now[$t] === $before[$t];
                if (! $ok) {
                    $changed[] = "{$t} {$before[$t]}→{$now[$t]}";
                }
            }
            $add('Payments / attendance / audit / failed-jobs / settings row counts preserved', $changed === [],
                $changed ? implode(', ', $changed) : collect(self::PRESERVED)->filter(fn ($t) => isset($before[$t]))
                    ->map(fn ($t) => "$t={$before[$t]}")->implode(' '));
        }

        return $checks;
    }

    /** @return array<string,int> */
    private function snapshot(): array
    {
        $out = [];
        foreach (self::PRESERVED as $t) {
            if (Schema::hasTable($t)) {
                $out[$t] = DB::table($t)->count();
            }
        }

        return $out;
    }

    /** @return string[] */
    private function orphans(): array
    {
        $found = [];
        foreach (Schema::getTables() as $t) {
            $child = $t['name'];
            foreach (Schema::getForeignKeys($child) as $fk) {
                if (count($fk['columns']) !== 1) {
                    continue;
                }
                [$col, $parent, $pcol] = [$fk['columns'][0], $fk['foreign_table'], $fk['foreign_columns'][0]];

                $n = DB::table($child)->whereNotNull($col)
                    ->whereNotExists(fn ($q) => $q->from($parent)->whereColumn("{$parent}.{$pcol}", "{$child}.{$col}"))
                    ->count();

                if ($n > 0) {
                    $found[] = "{$child}.{$col}→{$parent}: {$n}";
                }
            }
        }

        return $found;
    }

    private function allPassed(array $checks): bool
    {
        return collect($checks)->every(fn ($c) => $c['ok'] !== false);
    }

    private function renderChecks(array $checks): void
    {
        $this->newLine();
        $this->line('<options=bold>VERIFICATION</>');
        foreach ($checks as $c) {
            $mark = $c['ok'] === true ? '<fg=green>PASS</>' : ($c['ok'] === false ? '<fg=red>FAIL</>' : '<fg=yellow>NOTE</>');
            $this->line(sprintf('  [%s] %s%s', $mark, $c['name'], $c['detail'] !== '' ? "  — {$c['detail']}" : ''));
        }
    }

    private function renderReport(array $r, string $backup): void
    {
        $this->newLine();
        $this->line('<options=bold>REPORT</>');
        $this->line("  Deleted users      : {$r['users_removed']}");
        foreach ($r['removed_users'] as $x) {
            $this->line("      - {$x}");
        }
        $this->line("  Deleted members    : {$r['members_removed']}");
        foreach ($r['removed_members'] as $x) {
            $this->line("      - {$x}");
        }
        $this->line("  Also removed       : {$r['qr_tokens']} QR token(s), {$r['notifications']} notification(s), {$r['sessions']} session(s)");
        $this->line('  Final user counts  : admin ' . User::where('role', 'admin')->count()
            . ', staff ' . User::where('role', 'staff')->count()
            . ', instructor ' . User::where('role', 'instructor')->count()
            . ', member ' . User::where('role', 'member')->count()
            . '  (total ' . User::count() . ')');
        $this->line("  Backup             : {$backup}");
    }

    // ═════════════════════════════════════════════════════════════════════

    private function tableHas(string $table, ?string $column = null): bool
    {
        return Schema::hasTable($table) && ($column === null || Schema::hasColumn($table, $column));
    }
}