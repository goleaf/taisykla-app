<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MessageParticipant extends Model
{
    use HasFactory;

    protected $fillable = [
        'message_id',
        'user_id',
        'role',
        'delivery_status',
        'read_at',
        'channel',
    ];

    protected $casts = [
        'read_at' => 'datetime',
    ];

    public function message()
    {
        return $this->belongsTo(Message::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
