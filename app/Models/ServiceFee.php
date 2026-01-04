<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceFee extends Model
{
    use HasFactory;

    protected $fillable = [
        'fee_type',
        'description',
        'rate_type',
        'amount',
        'minimum_amount',
        'maximum_amount',
        'is_taxable',
        'is_active',
        'config',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'minimum_amount' => 'decimal:2',
        'maximum_amount' => 'decimal:2',
        'is_taxable' => 'boolean',
        'is_active' => 'boolean',
        'config' => 'array',
    ];
}
