<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\SoftDeletes;

class Part extends Model
{
    use HasFactory, SoftDeletes, \App\Traits\Auditable;

    protected $fillable = [
        'part_category_id',
        'sku',
        'name',
        'description',
        'manufacturer',
        'manufacturer_part_number',
        'unit_of_measure',
        'barcode',
        'rfid_tag',
        'specifications',
        'compatibility_notes',
        'unit_cost',
        'unit_price',
        'vendor',
        'reorder_level',
        'reorder_point',
        'reorder_quantity',
        'average_cost',
        'costing_method',
        'markup_percentage',
        'annual_demand',
        'ordering_cost',
        'holding_cost',
        'primary_supplier_id',
        'is_active',
        'is_obsolete',
        'obsolete_reason',
        'obsoleted_at',
        'last_received_at',
        'last_used_at',
    ];

    protected $casts = [
        'specifications' => 'array',
        'is_active' => 'boolean',
        'is_obsolete' => 'boolean',
        'average_cost' => 'decimal:2',
        'markup_percentage' => 'decimal:2',
        'obsoleted_at' => 'datetime',
        'last_received_at' => 'datetime',
        'last_used_at' => 'datetime',
    ];

    public function category()
    {
        return $this->belongsTo(PartCategory::class, 'part_category_id');
    }

    public function primarySupplier()
    {
        return $this->belongsTo(Supplier::class, 'primary_supplier_id');
    }

    public function inventoryItems()
    {
        return $this->hasMany(InventoryItem::class);
    }

    public function inventoryTransactions()
    {
        return $this->hasMany(InventoryTransaction::class);
    }

    public function workOrderParts()
    {
        return $this->hasMany(WorkOrderPart::class);
    }

    public function attachments()
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }

    public function auditLogs()
    {
        return $this->morphMany(AuditLog::class, 'subject');
    }

    public function links()
    {
        return $this->hasMany(PartLink::class);
    }

    public function suppliers()
    {
        return $this->belongsToMany(Supplier::class, 'part_suppliers');
    }

    public function supplierOptions()
    {
        return $this->hasMany(PartSupplier::class);
    }

    public function substitutions()
    {
        return $this->hasMany(PartSubstitution::class);
    }

    public function compatibilities()
    {
        return $this->hasMany(PartCompatibility::class);
    }

    public function priceTiers()
    {
        return $this->hasMany(PartPriceTier::class);
    }

    public function costHistories()
    {
        return $this->hasMany(PartCostHistory::class);
    }

    public function priceHistories()
    {
        return $this->hasMany(PartPriceHistory::class);
    }

    public function purchaseOrderItems()
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }
}
