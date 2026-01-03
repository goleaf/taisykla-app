<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CalendarBlock extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'provider',
        'external_event_id',
        'title',
        'starts_at',
        'ends_at',
        'is_busy',
        'source',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'is_busy' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
