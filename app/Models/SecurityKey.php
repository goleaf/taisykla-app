<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SecurityKey extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'credential_id',
        'public_key',
        'sign_count',
        'transports',
        'last_used_at',
    ];

    protected $casts = [
        'transports' => 'array',
        'last_used_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
