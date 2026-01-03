<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryCountItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'inventory_count_id',
        'part_id',
        'expected_quantity',
        'counted_quantity',
        'variance',
        'status',
        'notes',
    ];

    public function inventoryCount()
    {
        return $this->belongsTo(InventoryCount::class);
    }

    public function part()
    {
        return $this->belongsTo(Part::class);
    }
}
