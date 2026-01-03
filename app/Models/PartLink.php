<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PartLink extends Model
{
    use HasFactory;

    protected $fillable = [
        'part_id',
        'label',
        'url',
        'link_type',
        'is_primary',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
    ];

    public function part()
    {
        return $this->belongsTo(Part::class);
    }
}
