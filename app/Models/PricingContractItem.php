<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PricingContractItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'pricing_contract_id',
        'item_type',
        'service_type',
        'part_id',
        'part_category_id',
        'rate_override',
        'discount_percent',
        'fixed_price',
        'min_quantity',
        'max_quantity',
        'is_active',
    ];

    protected $casts = [
        'rate_override' => 'decimal:2',
        'discount_percent' => 'decimal:2',
        'fixed_price' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function contract()
    {
        return $this->belongsTo(PricingContract::class, 'pricing_contract_id');
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
