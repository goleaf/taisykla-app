<?php

namespace App\Traits;

use App\Services\AuditLogger;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

trait Auditable
{
    public static function bootAuditable()
    {
        static::created(function (Model $model) {
            $model->audit('created', 'Record created', $model->getAuditData('created'));
        });

        static::updated(function (Model $model) {
            $changes = $model->getChanges();
            $original = $model->getOriginal();
            
            $oldValues = [];
            $newValues = [];
            
            foreach ($changes as $key => $value) {
                if ($key === 'updated_at') continue;
                if (in_array($key, $model->getHidden())) continue;

                $oldValues[$key] = $original[$key] ?? null;
                $newValues[$key] = $value;
            }

            if (empty($newValues)) {
                return;
            }
            
            $model->audit('updated', 'Record updated', [
                'old' => $oldValues,
                'new' => $newValues,
            ]);
        });

        static::deleted(function (Model $model) {
            // Check if it's a soft delete
            $action = 'deleted';
            if (in_array(SoftDeletes::class, class_uses_recursive($model)) && !$model->isForceDeleting()) {
                $action = 'soft_deleted';
            }

            $model->audit($action, 'Record deleted', [
                'old' => $model->filterAuditData($model->toArray())
            ]);
        });
        
        if (method_exists(static::class, 'restored')) {
            static::restored(function (Model $model) {
                $model->audit('restored', 'Record restored');
            });
        }
    }

    protected function audit(string $action, ?string $description = null, array $meta = [])
    {
        if ($this instanceof \App\Models\AuditLog) {
            return;
        }

        try {
            app(AuditLogger::class)->log(
                action: $action,
                subject: $this,
                description: $description,
                meta: $meta
            );
        } catch (\Exception $e) {
            // Fail silently to not break the app if logging fails, 
            // but ideally we should log this error to system logs.
            \Illuminate\Support\Facades\Log::error('Audit log failed: ' . $e->getMessage());
        }
    }
    
    protected function getAuditData(string $event): array
    {
        if ($event === 'created') {
             return ['new' => $this->filterAuditData($this->toArray())];
        }
        return [];
    }

    protected function filterAuditData(array $data): array
    {
        $hidden = $this->getHidden();
        return array_diff_key($data, array_flip($hidden));
    }
}
