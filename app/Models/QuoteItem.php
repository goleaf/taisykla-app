<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuoteItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'quote_id',
        'item_type',
        'part_id',
        'service_type',
        'labor_minutes',
        'description',
        'quantity',
        'unit_cost',
        'unit_price',
        'discount_amount',
        'tax_rate',
        'tax_amount',
        'is_taxable',
        'position',
        'metadata',
        'total',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'labor_minutes' => 'integer',
        'unit_cost' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'tax_rate' => 'decimal:4',
        'tax_amount' => 'decimal:2',
        'is_taxable' => 'boolean',
        'position' => 'integer',
        'metadata' => 'array',
        'total' => 'decimal:2',
    ];

    public function quote()
    {
        return $this->belongsTo(Quote::class);
    }

    public function part()
    {
        return $this->belongsTo(Part::class);
    }
}
