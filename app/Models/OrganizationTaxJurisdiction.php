<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrganizationTaxJurisdiction extends Model
{
    use HasFactory;

    protected $fillable = [
        'organization_id',
        'tax_jurisdiction_id',
        'is_default',
        'priority',
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'priority' => 'integer',
    ];

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function jurisdiction()
    {
        return $this->belongsTo(TaxJurisdiction::class, 'tax_jurisdiction_id');
    }
}
