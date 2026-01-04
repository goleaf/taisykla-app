<?php

namespace App\Jobs;

use App\Models\ReportExport;
use App\Services\ReportExportService;
use App\Services\ReportService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Throwable;

class GenerateReportExport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public int $exportId)
    {
    }

    public function handle(ReportService $reportService, ReportExportService $exportService): void
    {
        $export = ReportExport::find($this->exportId);
        if (! $export) {
            return;
        }

        $export->update([
            'status' => 'processing',
            'started_at' => now(),
        ]);

        try {
            $report = $export->report;
            if (! $report) {
                $export->update([
                    'status' => 'failed',
                    'error_message' => 'Report not found.',
                    'completed_at' => now(),
                ]);
                return;
            }

            $overrides = array_merge($export->filters ?? [], $export->parameters ?? []);
            $payload = $reportService->generateForReport(
                $report,
                $overrides,
                $export->requestedBy
            );

            $columns = $payload['columns'] ?? [];
            $rows = $payload['rows'] ?? [];
            $format = $export->format ?: 'csv';
            $extension = $exportService->extension($format);
            $filename = sprintf('report-exports/report-%s-export-%s.%s', $report->id, $export->id, $extension);

            $contents = $exportService->build($format, $columns, $rows);
            Storage::disk(config('reporting.export.storage_disk', 'local'))->put($filename, $contents);

            $export->update([
                'status' => 'completed',
                'file_path' => $filename,
                'row_count' => count($rows),
                'completed_at' => now(),
            ]);
        } catch (Throwable $exception) {
            $export->update([
                'status' => 'failed',
                'error_message' => $exception->getMessage(),
                'completed_at' => now(),
            ]);

            throw $exception;
        }
    }
}
