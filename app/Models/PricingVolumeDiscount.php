<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PricingVolumeDiscount extends Model
{
    use HasFactory;

    protected $fillable = [
        'organization_id',
        'applies_to',
        'threshold_type',
        'threshold_value',
        'discount_type',
        'discount_value',
        'service_type',
        'is_active',
    ];

    protected $casts = [
        'threshold_value' => 'decimal:2',
        'discount_value' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }
}
