<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AccountingExport extends Model
{
    use HasFactory;

    protected $fillable = [
        'integration',
        'export_type',
        'status',
        'payload',
        'file_path',
        'attempted_at',
        'completed_at',
        'error_message',
        'created_by_user_id',
    ];

    protected $casts = [
        'payload' => 'array',
        'attempted_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }
}
