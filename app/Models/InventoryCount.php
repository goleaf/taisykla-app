<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryCount extends Model
{
    use HasFactory;

    protected $fillable = [
        'location_id',
        'status',
        'scheduled_for',
        'created_by_user_id',
        'counted_by_user_id',
        'counted_at',
        'notes',
    ];

    protected $casts = [
        'scheduled_for' => 'date',
        'counted_at' => 'datetime',
    ];

    public function location()
    {
        return $this->belongsTo(InventoryLocation::class, 'location_id');
    }

    public function items()
    {
        return $this->hasMany(InventoryCountItem::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function countedBy()
    {
        return $this->belongsTo(User::class, 'counted_by_user_id');
    }
}
