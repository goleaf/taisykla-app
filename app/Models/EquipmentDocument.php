<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EquipmentDocument extends Model
{
    use HasFactory;

    public const TYPE_MANUAL = 'manual';
    public const TYPE_SERVICE_MANUAL = 'service_manual';
    public const TYPE_WARRANTY_DOC = 'warranty_doc';
    public const TYPE_RECEIPT = 'receipt';
    public const TYPE_CONFIG = 'config';
    public const TYPE_TRAINING = 'training';
    public const TYPE_OTHER = 'other';

    protected $fillable = [
        'equipment_id',
        'uploaded_by_user_id',
        'type',
        'title',
        'file_path',
        'file_name',
        'file_size',
        'mime_type',
        'version',
        'notes',
    ];

    public function equipment(): BelongsTo
    {
        return $this->belongsTo(Equipment::class);
    }

    public function uploadedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by_user_id');
    }

    public static function typeOptions(): array
    {
        return [
            self::TYPE_MANUAL => 'User Manual',
            self::TYPE_SERVICE_MANUAL => 'Service Manual',
            self::TYPE_WARRANTY_DOC => 'Warranty Document',
            self::TYPE_RECEIPT => 'Purchase Receipt',
            self::TYPE_CONFIG => 'Configuration',
            self::TYPE_TRAINING => 'Training Material',
            self::TYPE_OTHER => 'Other',
        ];
    }

    public function getTypeLabel(): string
    {
        return self::typeOptions()[$this->type] ?? $this->type;
    }
}
