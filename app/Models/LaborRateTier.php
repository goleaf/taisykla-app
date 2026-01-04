<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LaborRateTier extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'base_rate',
        'currency',
        'is_active',
        'created_by_user_id',
    ];

    protected $casts = [
        'base_rate' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function rules()
    {
        return $this->hasMany(LaborRateRule::class, 'rate_tier_id');
    }

    public function overrides()
    {
        return $this->hasMany(LaborRateOverride::class, 'rate_tier_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }
}
