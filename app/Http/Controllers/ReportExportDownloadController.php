<?php

namespace App\Http\Controllers;

use App\Models\ReportExport;
use App\Services\ReportExportService;
use App\Support\PermissionCatalog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportExportDownloadController extends Controller
{
    public function __invoke(Request $request, ReportExport $export, ReportExportService $exportService): StreamedResponse
    {
        abort_unless($request->user()?->can(PermissionCatalog::REPORTS_EXPORT), 403);

        if ($export->requested_by_user_id
            && $export->requested_by_user_id !== $request->user()?->id
            && ! $request->user()?->can(PermissionCatalog::REPORTS_MANAGE)) {
            abort(403);
        }

        $disk = config('reporting.export.storage_disk', 'local');
        if (! $export->file_path || ! Storage::disk($disk)->exists($export->file_path)) {
            abort(404, 'Export file not found.');
        }

        return Storage::disk($disk)->download(
            $export->file_path,
            basename($export->file_path),
            ['Content-Type' => $exportService->contentType($export->format)]
        );
    }
}
