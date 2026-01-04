<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaxRule extends Model
{
    use HasFactory;

    protected $fillable = [
        'tax_jurisdiction_id',
        'applies_to',
        'service_type',
        'part_category_id',
        'fee_type',
        'is_taxable',
        'is_active',
    ];

    protected $casts = [
        'is_taxable' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function jurisdiction()
    {
        return $this->belongsTo(TaxJurisdiction::class, 'tax_jurisdiction_id');
    }

    public function partCategory()
    {
        return $this->belongsTo(PartCategory::class, 'part_category_id');
    }
}
