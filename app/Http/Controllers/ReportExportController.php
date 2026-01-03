<?php

namespace App\Http\Controllers;

use App\Models\Report;
use App\Models\ReportRun;
use App\Services\ReportService;
use App\Support\PermissionCatalog;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportExportController extends Controller
{
    public function __invoke(Request $request, Report $report, ReportService $reportService): StreamedResponse
    {
        abort_unless($request->user()?->can(PermissionCatalog::REPORTS_EXPORT), 403);

        $format = $request->query('format', 'csv');
        $payload = $reportService->generateForReport($report);

        $columns = $payload['columns'] ?? [];
        $rows = $payload['rows'] ?? [];

        ReportRun::create([
            'report_id' => $report->id,
            'status' => 'completed',
            'format' => $format,
            'row_count' => count($rows),
            'meta' => $payload['meta'] ?? null,
            'run_at' => now(),
        ]);

        $filename = sprintf('report-%s-%s.%s', $report->id, now()->format('Ymd-His'), $format);

        return response()->streamDownload(function () use ($columns, $rows) {
            $handle = fopen('php://output', 'w');

            if ($columns !== []) {
                fputcsv($handle, $columns);
            }

            foreach ($rows as $row) {
                $line = [];
                foreach ($columns as $column) {
                    $line[] = $row[$column] ?? null;
                }
                fputcsv($handle, $line);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }
}
