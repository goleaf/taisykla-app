<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MessageThread extends Model
{
    use HasFactory;

    protected $fillable = [
        'subject',
        'type',
        'status',
        'organization_id',
        'work_order_id',
        'created_by_user_id',
    ];

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function workOrder()
    {
        return $this->belongsTo(WorkOrder::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function participants()
    {
        return $this->hasMany(MessageThreadParticipant::class, 'thread_id');
    }

    public function messages()
    {
        return $this->hasMany(Message::class, 'thread_id');
    }
}
