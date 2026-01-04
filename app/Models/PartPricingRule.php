<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PartPricingRule extends Model
{
    use HasFactory;

    protected $fillable = [
        'organization_id',
        'part_id',
        'part_category_id',
        'cost_basis',
        'markup_type',
        'markup_value',
        'fixed_price',
        'min_quantity',
        'max_quantity',
        'is_active',
    ];

    protected $casts = [
        'markup_value' => 'decimal:2',
        'fixed_price' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function part()
    {
        return $this->belongsTo(Part::class);
    }

    public function partCategory()
    {
        return $this->belongsTo(PartCategory::class, 'part_category_id');
    }
}
