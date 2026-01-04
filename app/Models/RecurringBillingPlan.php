<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RecurringBillingPlan extends Model
{
    use HasFactory;

    protected $fillable = [
        'organization_id',
        'service_agreement_id',
        'name',
        'description',
        'amount',
        'currency',
        'frequency',
        'bill_day',
        'start_date',
        'end_date',
        'auto_charge',
        'payment_method_id',
        'next_invoice_at',
        'status',
        'created_by_user_id',
        'metadata',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'bill_day' => 'integer',
        'start_date' => 'date',
        'end_date' => 'date',
        'auto_charge' => 'boolean',
        'next_invoice_at' => 'datetime',
        'metadata' => 'array',
    ];

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function serviceAgreement()
    {
        return $this->belongsTo(ServiceAgreement::class);
    }

    public function items()
    {
        return $this->hasMany(RecurringBillingItem::class, 'plan_id');
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
