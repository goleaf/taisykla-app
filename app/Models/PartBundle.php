<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PartBundle extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'bundle_price',
        'is_active',
    ];

    protected $casts = [
        'bundle_price' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function items()
    {
        return $this->hasMany(PartBundleItem::class, 'bundle_id');
    }
}
