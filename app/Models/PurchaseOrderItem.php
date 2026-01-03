<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseOrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'purchase_order_id',
        'part_id',
        'description',
        'quantity',
        'unit_cost',
        'received_quantity',
        'status',
        'expected_at',
        'notes',
    ];

    protected $casts = [
        'expected_at' => 'date',
        'unit_cost' => 'decimal:2',
    ];

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function part()
    {
        return $this->belongsTo(Part::class);
    }
}
