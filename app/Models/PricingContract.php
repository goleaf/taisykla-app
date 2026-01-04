<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PricingContract extends Model
{
    use HasFactory;

    protected $fillable = [
        'organization_id',
        'name',
        'description',
        'labor_rate_tier_id',
        'discount_percent',
        'parts_markup_percent',
        'valid_from',
        'valid_until',
        'is_active',
        'terms',
        'created_by_user_id',
    ];

    protected $casts = [
        'discount_percent' => 'decimal:2',
        'parts_markup_percent' => 'decimal:2',
        'valid_from' => 'date',
        'valid_until' => 'date',
        'is_active' => 'boolean',
    ];

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function laborRateTier()
    {
        return $this->belongsTo(LaborRateTier::class, 'labor_rate_tier_id');
    }

    public function items()
    {
        return $this->hasMany(PricingContractItem::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }
}
