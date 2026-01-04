<?php

namespace App\Http\Controllers;

use App\Jobs\GenerateReportExport;
use App\Models\Report;
use App\Models\ReportExport;
use App\Services\ReportExportService;
use App\Services\ReportService;
use App\Support\PermissionCatalog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportExportController extends Controller
{
    public function __invoke(Request $request, Report $report, ReportService $reportService, ReportExportService $exportService): StreamedResponse
    {
        abort_unless($request->user()?->can(PermissionCatalog::REPORTS_EXPORT), 403);

        $format = strtolower($request->query('format', 'csv'));
        $allowed = config('reporting.export.formats', ['csv']);
        if (! in_array($format, $allowed, true)) {
            abort(400, 'Unsupported export format.');
        }

        if ($request->boolean('async')) {
            $export = ReportExport::create([
                'report_id' => $report->id,
                'format' => $format,
                'status' => 'queued',
                'filters' => $report->filters ?? [],
                'requested_by_user_id' => $request->user()?->id,
                'queued_at' => now(),
            ]);

            GenerateReportExport::dispatch($export->id);

            return response()->streamDownload(function () {
                echo 'Export queued. Check the exports panel for status.';
            }, sprintf('report-%s-queued.txt', $report->id), [
                'Content-Type' => 'text/plain',
            ]);
        }

        $payload = $reportService->generateForReport($report, [], $request->user());
        $columns = $payload['columns'] ?? [];
        $rows = $payload['rows'] ?? [];

        $maxSyncRows = (int) config('reporting.export.max_sync_rows', 750);
        if (count($rows) > $maxSyncRows) {
            $export = ReportExport::create([
                'report_id' => $report->id,
                'format' => $format,
                'status' => 'queued',
                'filters' => $report->filters ?? [],
                'requested_by_user_id' => $request->user()?->id,
                'queued_at' => now(),
                'row_count' => count($rows),
            ]);

            GenerateReportExport::dispatch($export->id);

            return response()->streamDownload(function () {
                echo 'Export queued due to size. Check the exports panel for status.';
            }, sprintf('report-%s-queued.txt', $report->id), [
                'Content-Type' => 'text/plain',
            ]);
        }

        $contents = $exportService->build($format, $columns, $rows);
        $extension = $exportService->extension($format);
        $filename = sprintf('report-%s-%s.%s', $report->id, now()->format('Ymd-His'), $extension);
        $filePath = sprintf('report-exports/%s', $filename);

        Storage::disk(config('reporting.export.storage_disk', 'local'))->put($filePath, $contents);

        $export = ReportExport::create([
            'report_id' => $report->id,
            'format' => $format,
            'status' => 'completed',
            'filters' => $report->filters ?? [],
            'requested_by_user_id' => $request->user()?->id,
            'row_count' => count($rows),
            'file_path' => $filePath,
            'completed_at' => now(),
        ]);

        return response()->streamDownload(function () use ($contents) {
            echo $contents;
        }, $filename, [
            'Content-Type' => $exportService->contentType($format),
        ]);
    }
}
