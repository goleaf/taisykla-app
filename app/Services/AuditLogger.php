<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class AuditLogger
{
    public function log(string $action, ?Model $subject = null, ?string $description = null, array $meta = [], ?int $userId = null): AuditLog
    {
        return DB::transaction(function () use ($action, $subject, $description, $meta, $userId) {
            $previous = AuditLog::query()->latest('id')->lockForUpdate()->first();
            $previousHash = $previous?->hash;

            $log = AuditLog::create([
                'user_id' => $userId ?? auth()->id(),
                'action' => $action,
                'subject_type' => $subject?->getMorphClass(),
                'subject_id' => $subject?->getKey(),
                'description' => $description,
                'meta' => $meta,
                'ip_address' => request()?->ip(),
                'user_agent' => request()?->userAgent(),
                'previous_hash' => $previousHash,
            ]);

            $log->update([
                'hash' => $this->buildHash($log),
            ]);

            return $log;
        });
    }

    private function buildHash(AuditLog $log): string
    {
        $payload = [
            'id' => $log->id,
            'user_id' => $log->user_id,
            'action' => $log->action,
            'subject_type' => $log->subject_type,
            'subject_id' => $log->subject_id,
            'description' => $log->description,
            'meta' => $log->meta,
            'ip_address' => $log->ip_address,
            'user_agent' => $log->user_agent,
            'created_at' => $log->created_at?->toIso8601String(),
            'previous_hash' => $log->previous_hash,
        ];

        return hash('sha256', json_encode($payload));
    }
}
