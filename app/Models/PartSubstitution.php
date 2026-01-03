<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PartSubstitution extends Model
{
    use HasFactory;

    protected $fillable = [
        'part_id',
        'substitute_part_id',
        'is_preferred',
        'note',
    ];

    protected $casts = [
        'is_preferred' => 'boolean',
    ];

    public function part()
    {
        return $this->belongsTo(Part::class);
    }

    public function substitute()
    {
        return $this->belongsTo(Part::class, 'substitute_part_id');
    }
}
