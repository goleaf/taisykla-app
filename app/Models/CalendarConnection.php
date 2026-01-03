<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CalendarConnection extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'provider',
        'external_calendar_id',
        'config',
        'is_active',
        'two_way_sync',
        'conflict_policy',
        'sync_status',
        'last_synced_at',
    ];

    protected $casts = [
        'config' => 'array',
        'is_active' => 'boolean',
        'two_way_sync' => 'boolean',
        'last_synced_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
