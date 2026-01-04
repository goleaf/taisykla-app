<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    use HasFactory;

    protected $fillable = [
        'thread_id',
        'user_id',
        'sender_id',
        'recipient_id',
        'subject',
        'body',
        'timestamp',
        'is_read',
        'related_work_order_id',
        'parent_message_id',
        'message_type',
        'channel',
        'metadata',
    ];

    protected $casts = [
        'timestamp' => 'datetime',
        'is_read' => 'boolean',
        'metadata' => 'array',
    ];

    public function thread()
    {
        return $this->belongsTo(MessageThread::class, 'thread_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function recipient()
    {
        return $this->belongsTo(User::class, 'recipient_id');
    }

    public function relatedWorkOrder()
    {
        return $this->belongsTo(WorkOrder::class, 'related_work_order_id');
    }

    public function parent()
    {
        return $this->belongsTo(Message::class, 'parent_message_id');
    }

    public function replies()
    {
        return $this->hasMany(Message::class, 'parent_message_id');
    }

    public function attachments()
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }

    public function messageAttachments()
    {
        return $this->hasMany(MessageAttachment::class);
    }

    public function participants()
    {
        return $this->hasMany(MessageParticipant::class);
    }
}
