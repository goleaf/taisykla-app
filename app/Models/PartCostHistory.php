<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PartCostHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'part_id',
        'supplier_id',
        'unit_cost',
        'currency',
        'effective_at',
        'note',
    ];

    protected $casts = [
        'unit_cost' => 'decimal:2',
        'effective_at' => 'datetime',
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
