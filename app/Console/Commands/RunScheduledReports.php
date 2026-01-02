<?php

namespace App\Console\Commands;

use App\Models\ReportRun;
use App\Models\ReportSchedule;
use App\Services\ReportService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class RunScheduledReports extends Command
{
    protected $signature = 'reports:run-scheduled';
    protected $description = 'Run scheduled reports and store exports.';

    public function handle(ReportService $reportService): int
    {
        $now = now();
        $schedules = ReportSchedule::with('report')
            ->where('is_active', true)
            ->where(function ($query) use ($now) {
                $query->whereNull('next_run_at')
                    ->orWhere('next_run_at', '<=', $now);
            })
            ->get();

        if ($schedules->isEmpty()) {
            $this->info('No scheduled reports due.');
            return self::SUCCESS;
        }

        Storage::makeDirectory('reports');

        foreach ($schedules as $schedule) {
            $report = $schedule->report;
            if (! $report) {
                continue;
            }

            $payload = $reportService->generateForReport($report);
            $columns = $payload['columns'] ?? [];
            $rows = $payload['rows'] ?? [];

            $filename = sprintf(
                'reports/report-%s-schedule-%s-%s.csv',
                $report->id,
                $schedule->id,
                $now->format('Ymd-His')
            );

            $contents = $this->buildCsv($columns, $rows);
            Storage::put($filename, $contents);

            ReportRun::create([
                'report_id' => $report->id,
                'status' => 'completed',
                'format' => 'csv',
                'file_path' => $filename,
                'row_count' => count($rows),
                'meta' => [
                    'schedule_id' => $schedule->id,
                    'recipients' => $schedule->recipients,
                ],
                'run_at' => $now,
            ]);

            $schedule->update([
                'last_run_at' => $now,
                'next_run_at' => $this->calculateNextRun($schedule, $now),
            ]);
        }

        $this->info('Scheduled reports completed.');

        return self::SUCCESS;
    }

    private function buildCsv(array $columns, array $rows): string
    {
        $handle = fopen('php://temp', 'w+');

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

        rewind($handle);
        $contents = stream_get_contents($handle);
        fclose($handle);

        return $contents ?: '';
    }

    private function calculateNextRun(ReportSchedule $schedule, Carbon $from): Carbon
    {
        $frequency = $schedule->frequency;
        $timeOfDay = $schedule->time_of_day;
        $hour = 9;
        $minute = 0;

        if ($timeOfDay) {
            [$hour, $minute] = array_pad(explode(':', $timeOfDay), 2, 0);
        }

        if ($frequency === 'daily') {
            $candidate = $from->copy()->setTime((int) $hour, (int) $minute);
            if ($candidate->lessThanOrEqualTo($from)) {
                $candidate->addDay();
            }

            return $candidate;
        }

        if ($frequency === 'weekly') {
            $targetDay = $schedule->day_of_week ?? 1;
            $candidate = $from->copy()->setTime((int) $hour, (int) $minute);
            $daysUntil = ($targetDay - $candidate->dayOfWeek + 7) % 7;
            if ($daysUntil === 0 && $candidate->lessThanOrEqualTo($from)) {
                $daysUntil = 7;
            }

            return $candidate->addDays($daysUntil);
        }

        if ($frequency === 'monthly') {
            $day = $schedule->day_of_month ?? 1;
            $candidate = $from->copy()->setTime((int) $hour, (int) $minute);
            $day = min($day, $candidate->daysInMonth);
            $candidate->setDay($day);

            if ($candidate->lessThanOrEqualTo($from)) {
                $candidate = $from->copy()->addMonthNoOverflow()->setTime((int) $hour, (int) $minute);
                $day = min($schedule->day_of_month ?? 1, $candidate->daysInMonth);
                $candidate->setDay($day);
            }

            return $candidate;
        }

        return $from->copy()->addDay();
    }
}
