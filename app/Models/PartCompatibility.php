<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PartCompatibility extends Model
{
    use HasFactory;

    protected $fillable = [
        'part_id',
        'equipment_id',
        'note',
    ];

    public function part()
    {
        return $this->belongsTo(Part::class);
    }

    public function equipment()
    {
        return $this->belongsTo(Equipment::class);
    }
}
