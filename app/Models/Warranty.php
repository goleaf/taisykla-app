<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Warranty extends Model
{
    use HasFactory;

    protected $fillable = [
        'equipment_id',
        'provider_name',
        'coverage_type',
        'coverage_details',
        'starts_at',
        'ends_at',
        'claim_instructions',
    ];

    protected $casts = [
        'starts_at' => 'date',
        'ends_at' => 'date',
    ];

    public function equipment()
    {
        return $this->belongsTo(Equipment::class);
    }

    public function claims()
    {
        return $this->hasMany(WarrantyClaim::class);
    }
}
