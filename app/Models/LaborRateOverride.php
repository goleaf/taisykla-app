<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LaborRateOverride extends Model
{
    use HasFactory;

    protected $fillable = [
        'organization_id',
        'rate_tier_id',
        'service_type',
        'time_category',
        'fixed_rate',
        'multiplier',
        'valid_from',
        'valid_until',
        'is_active',
        'notes',
    ];

    protected $casts = [
        'fixed_rate' => 'decimal:2',
        'multiplier' => 'decimal:3',
        'valid_from' => 'date',
        'valid_until' => 'date',
        'is_active' => 'boolean',
    ];

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function rateTier()
    {
        return $this->belongsTo(LaborRateTier::class, 'rate_tier_id');
    }
}
