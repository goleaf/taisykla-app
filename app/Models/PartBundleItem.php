<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PartBundleItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'bundle_id',
        'part_id',
        'quantity',
        'unit_price',
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
    ];

    public function bundle()
    {
        return $this->belongsTo(PartBundle::class, 'bundle_id');
    }

    public function part()
    {
        return $this->belongsTo(Part::class);
    }
}
