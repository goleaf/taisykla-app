<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Equipment extends Model
{
    use HasFactory;

    protected $fillable = [
        'organization_id',
        'equipment_category_id',
        'name',
        'type',
        'manufacturer',
        'model',
        'serial_number',
        'asset_tag',
        'purchase_date',
        'purchase_price',
        'purchase_vendor',
        'status',
        'location_name',
        'location_address',
        'location_building',
        'location_floor',
        'location_room',
        'assigned_user_id',
        'notes',
        'specifications',
        'custom_fields',
    ];

    protected $casts = [
        'purchase_date' => 'date',
        'last_service_at' => 'datetime',
        'specifications' => 'array',
        'custom_fields' => 'array',
    ];

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function category()
    {
        return $this->belongsTo(EquipmentCategory::class, 'equipment_category_id');
    }

    public function assignedUser()
    {
        return $this->belongsTo(User::class, 'assigned_user_id');
    }

    public function warranties()
    {
        return $this->hasMany(Warranty::class);
    }

    public function workOrders()
    {
        return $this->hasMany(WorkOrder::class);
    }

    public function attachments()
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }
}
