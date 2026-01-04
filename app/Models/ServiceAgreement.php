<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceAgreement extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'agreement_type',
        'response_time_minutes',
        'resolution_time_minutes',
        'included_visits_per_month',
        'monthly_fee',
        'includes_parts',
        'includes_labor',
        'billing_terms',
        'coverage_details',
        'is_active',
    ];

    protected $casts = [
        'includes_parts' => 'boolean',
        'includes_labor' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function organizations()
    {
        return $this->hasMany(Organization::class);
    }

    protected static function booted(): void
    {
        static::saved(function () {
            app(\App\Services\ReferenceDataService::class)->clearServiceAgreementsCache();
        });

        static::deleted(function () {
            app(\App\Services\ReferenceDataService::class)->clearServiceAgreementsCache();
        });
    }
}
