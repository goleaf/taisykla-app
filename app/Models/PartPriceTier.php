<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PartPriceTier extends Model
{
    use HasFactory;

    protected $fillable = [
        'part_id',
        'min_quantity',
        'unit_cost',
        'unit_price',
        'currency',
    ];

    protected $casts = [
        'unit_cost' => 'decimal:2',
        'unit_price' => 'decimal:2',
    ];

    public function part()
    {
        return $this->belongsTo(Part::class);
    }
}
