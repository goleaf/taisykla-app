<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MessageThreadParticipant extends Model
{
    use HasFactory;

    protected $fillable = [
        'thread_id',
        'user_id',
        'folder',
        'is_starred',
        'is_archived',
        'is_muted',
        'last_read_at',
        'deleted_at',
    ];

    protected $casts = [
        'last_read_at' => 'datetime',
        'deleted_at' => 'datetime',
        'is_starred' => 'boolean',
        'is_archived' => 'boolean',
        'is_muted' => 'boolean',
    ];

    public function thread()
    {
        return $this->belongsTo(MessageThread::class, 'thread_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
