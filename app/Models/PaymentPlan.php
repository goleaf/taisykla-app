<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentPlan extends Model
{
    use HasFactory;

    protected $fillable = [
        'organization_id',
        'invoice_id',
        'total_amount',
        'status',
        'frequency',
        'interval_count',
        'installment_count',
        'start_date',
        'next_run_at',
        'auto_charge',
        'payment_method_id',
        'created_by_user_id',
        'notes',
        'metadata',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'interval_count' => 'integer',
        'installment_count' => 'integer',
        'start_date' => 'date',
        'next_run_at' => 'datetime',
        'auto_charge' => 'boolean',
        'metadata' => 'array',
    ];

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function installments()
    {
        return $this->hasMany(PaymentPlanInstallment::class);
    }

    public function paymentMethod()
    {
        return $this->belongsTo(PaymentMethod::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }
}
