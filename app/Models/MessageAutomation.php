<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MessageAutomation extends Model
{
    use HasFactory;

    protected $fillable = [
        'trigger',
        'name',
        'is_enabled',
        'channels',
        'template_id',
        'schedule_offset_minutes',
        'conditions',
    ];

    protected $casts = [
        'is_enabled' => 'boolean',
        'channels' => 'array',
        'conditions' => 'array',
    ];

    public function template()
    {
        return $this->belongsTo(CommunicationTemplate::class, 'template_id');
    }
}
