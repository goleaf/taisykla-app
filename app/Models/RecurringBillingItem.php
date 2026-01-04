<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RecurringBillingItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'plan_id',
        'description',
        'quantity',
        'unit_price',
        'tax_rate',
        'is_taxable',
        'total',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_price' => 'decimal:2',
        'tax_rate' => 'decimal:4',
        'is_taxable' => 'boolean',
        'total' => 'decimal:2',
    ];

    public function plan()
    {
        return $this->belongsTo(RecurringBillingPlan::class, 'plan_id');
    }
}
