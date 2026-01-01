<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryLocation extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'address',
        'notes',
    ];

    public function items()
    {
        return $this->hasMany(InventoryItem::class, 'location_id');
    }

    public function transactions()
    {
        return $this->hasMany(InventoryTransaction::class, 'location_id');
    }
}
