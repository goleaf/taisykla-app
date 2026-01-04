<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LaborRateRule extends Model
{
    use HasFactory;

    protected $fillable = [
        'rate_tier_id',
        'service_type',
        'time_category',
        'day_type',
        'start_time',
        'end_time',
        'multiplier',
        'fixed_rate',
        'emergency_premium',
        'is_active',
    ];

    protected $casts = [
        'multiplier' => 'decimal:3',
        'fixed_rate' => 'decimal:2',
        'emergency_premium' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function rateTier()
    {
        return $this->belongsTo(LaborRateTier::class, 'rate_tier_id');
    }
}
