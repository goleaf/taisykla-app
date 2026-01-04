<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NotificationPreference extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'channel',
        'frequency',
        'message_types',
        'vip_senders',
        'quiet_hours_start',
        'quiet_hours_end',
        'is_enabled',
    ];

    protected $casts = [
        'message_types' => 'array',
        'vip_senders' => 'array',
        'quiet_hours_start' => 'datetime:H:i',
        'quiet_hours_end' => 'datetime:H:i',
        'is_enabled' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
