<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'contact_name',
        'email',
        'phone',
        'address',
        'payment_terms',
        'lead_time_days',
        'min_order_quantity',
        'shipping_cost',
        'performance_rating',
        'is_preferred',
        'external_id',
        'notes',
    ];

    protected $casts = [
        'is_preferred' => 'boolean',
        'shipping_cost' => 'decimal:2',
        'performance_rating' => 'decimal:2',
    ];

    public function partSuppliers()
    {
        return $this->hasMany(PartSupplier::class);
    }

    public function parts()
    {
        return $this->belongsToMany(Part::class, 'part_suppliers');
    }

    public function purchaseOrders()
    {
        return $this->hasMany(PurchaseOrder::class);
    }
}
