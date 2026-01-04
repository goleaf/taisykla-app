<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaxOverride extends Model
{
    use HasFactory;

    protected $fillable = [
        'entity_type',
        'entity_id',
        'rate',
        'reason',
        'applied_by_user_id',
        'applied_at',
    ];

    protected $casts = [
        'rate' => 'decimal:4',
        'applied_at' => 'datetime',
    ];

    public function appliedBy()
    {
        return $this->belongsTo(User::class, 'applied_by_user_id');
    }
}
