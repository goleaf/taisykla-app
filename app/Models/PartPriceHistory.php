<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PartPriceHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'part_id',
        'unit_price',
        'currency',
        'effective_at',
        'note',
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'effective_at' => 'datetime',
    ];

    public function part()
    {
        return $this->belongsTo(Part::class);
    }
}
