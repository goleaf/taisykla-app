<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ScheduleRule extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'rule_type',
        'priority',
        'conditions',
        'actions',
        'state',
        'is_active',
        'created_by_user_id',
        'last_applied_at',
    ];

    protected $casts = [
        'conditions' => 'array',
        'actions' => 'array',
        'state' => 'array',
        'is_active' => 'boolean',
        'last_applied_at' => 'datetime',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }
}
