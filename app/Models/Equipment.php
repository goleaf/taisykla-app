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
        'status',
        'location_name',
        'location_address',
        'assigned_user_id',
        'notes',
    ];

    protected $casts = [
        'purchase_date' => 'date',
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
