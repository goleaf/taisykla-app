<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaxJurisdiction extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'jurisdiction_type',
        'code',
        'rate',
        'priority',
        'is_active',
        'starts_at',
        'ends_at',
    ];

    protected $casts = [
        'rate' => 'decimal:4',
        'priority' => 'integer',
        'is_active' => 'boolean',
        'starts_at' => 'date',
        'ends_at' => 'date',
    ];

    public function rules()
    {
        return $this->hasMany(TaxRule::class, 'tax_jurisdiction_id');
    }
}
