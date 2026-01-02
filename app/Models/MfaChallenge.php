<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MfaChallenge extends Model
{
    protected $fillable = [
        'user_id',
        'method',
        'code_hash',
        'expires_at',
        'consumed_at',
    ];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'consumed_at' => 'datetime',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
