<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PartSupplier extends Model
{
    use HasFactory;

    protected $fillable = [
        'part_id',
        'supplier_id',
        'supplier_part_number',
        'unit_cost',
        'currency',
        'lead_time_days',
        'min_order_quantity',
        'is_primary',
        'last_cost_at',
        'notes',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
        'unit_cost' => 'decimal:2',
        'last_cost_at' => 'datetime',
    ];

    public function part()
    {
        return $this->belongsTo(Part::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }
}
