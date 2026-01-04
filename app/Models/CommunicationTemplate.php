<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CommunicationTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'category',
        'channel',
        'subject',
        'body',
        'is_active',
        'is_shared',
        'merge_fields',
        'created_by_user_id',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_shared' => 'boolean',
        'merge_fields' => 'array',
    ];

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }
}
