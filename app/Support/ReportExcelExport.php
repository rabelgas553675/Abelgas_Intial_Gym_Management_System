<?php

namespace App\Support;

use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Builds an Excel-friendly export from the same $report array the Reports page and
 * PDF view already use (keys: title, rows). It fixes the garbled ₱ by writing a UTF-8
 * BOM, and exports Amount as a real number so Excel can sum it.
 *
 * Usage inside your ReportController export action, replacing the current Excel branch:
 *
 *   use App\Support\ReportExcelExport;
 *
 *   if ($format === 'excel') {
 *       return ReportExcelExport::download($report, $type, $range);
 *   }
 *
 * ($type is 'payment' | 'walkin' | 'attendance' | 'member' | 'workout',
 *  $report is the same array you already pass to the PDF view.)
 */
class ReportExcelExport
{
    /** [column heading, row key, is-money] per report type — mirrors the Blade tables. */
    private const COLUMNS = [
        'payment' => [
            ['Member', 'name', false], ['Type', 'type', false], ['Date', 'date', false],
            ['Method', 'method', false], ['Amount (PHP)', 'amount', true],
        ],
        'attendance' => [
            ['Name', 'name', false], ['Role', 'role', false], ['Date', 'date', false],
            ['Time In', 'time_in', false], ['Time Out', 'time_out', false], ['Duration', 'duration', false],
        ],
        'walkin' => [
            ['Receipt #', 'receipt', false], ['Customer', 'name', false], ['Date', 'date', false],
            ['Method', 'method', false], ['Status', 'status', false], ['Amount (PHP)', 'amount', true],
        ],
        'member' => [
            ['Name', 'name', false], ['Email', 'email', false], ['Date Joined', 'date', false],
            ['Plan', 'plan', false], ['Status', 'status', false],
        ],
        'workout' => [
            ['Member', 'name', false], ['Session', 'title', false], ['Date', 'date', false],
            ['Status', 'status', false], ['Instructor', 'instructor', false],
        ],
    ];

    public static function download(array $report, string $type, string $range = 'report'): StreamedResponse
    {
        $columns  = self::COLUMNS[$type] ?? self::COLUMNS['workout'];
        $rows     = $report['rows'] ?? [];
        $filename = $type . '-report-' . $range . '-' . now()->format('Ymd-His') . '.csv';

        return response()->streamDownload(function () use ($columns, $rows) {
            $out = fopen('php://output', 'w');

            // UTF-8 BOM so Excel reads the file as UTF-8 (prevents garbled characters)
            fwrite($out, "\xEF\xBB\xBF");

            fputcsv($out, array_column($columns, 0));

            foreach ($rows as $row) {
                $line = [];
                foreach ($columns as [$heading, $key, $isMoney]) {
                    $value = $row[$key] ?? '';
                    if ($isMoney) {
                        // "₱1,000" / "₱ 300.50" -> 1000.00 / 300.50 (a real number Excel can total)
                        $number = preg_replace('/[^0-9.\-]/', '', (string) $value);
                        $value  = $number === '' ? '' : number_format((float) $number, 2, '.', '');
                    }
                    $line[] = $value;
                }
                fputcsv($out, $line);
            }

            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}