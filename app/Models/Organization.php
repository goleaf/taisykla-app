<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Organization extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'type',
        'status',
        'primary_contact_name',
        'primary_contact_email',
        'primary_contact_phone',
        'billing_contact_name',
        'billing_phone',
        'billing_email',
        'billing_address',
        'billing_currency',
        'payment_terms',
        'credit_limit',
        'credit_balance',
        'allow_over_limit',
        'is_tax_exempt',
        'tax_exempt_reason',
        'tax_exempt_valid_until',
        'default_labor_rate_tier_id',
        'pricing_contract_id',
        'service_agreement_id',
        'notes',
    ];

    protected $casts = [
        'credit_limit' => 'decimal:2',
        'credit_balance' => 'decimal:2',
        'allow_over_limit' => 'boolean',
        'is_tax_exempt' => 'boolean',
        'tax_exempt_valid_until' => 'date',
    ];

    public function serviceAgreement()
    {
        return $this->belongsTo(ServiceAgreement::class);
    }

    public function defaultLaborRateTier()
    {
        return $this->belongsTo(LaborRateTier::class, 'default_labor_rate_tier_id');
    }

    public function pricingContract()
    {
        return $this->belongsTo(PricingContract::class, 'pricing_contract_id');
    }

    public function taxExemptions()
    {
        return $this->hasMany(TaxExemption::class);
    }

    public function taxJurisdictions()
    {
        return $this->hasMany(OrganizationTaxJurisdiction::class);
    }

    public function paymentMethods()
    {
        return $this->hasMany(PaymentMethod::class);
    }

    public function paymentPlans()
    {
        return $this->hasMany(PaymentPlan::class);
    }

    public function recurringBillingPlans()
    {
        return $this->hasMany(RecurringBillingPlan::class);
    }

    public function creditMemos()
    {
        return $this->hasMany(CreditMemo::class);
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function equipment()
    {
        return $this->hasMany(Equipment::class);
    }

    public function workOrders()
    {
        return $this->hasMany(WorkOrder::class);
    }

    public function quotes()
    {
        return $this->hasMany(Quote::class);
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    public function messageThreads()
    {
        return $this->hasMany(MessageThread::class);
    }

    public function supportTickets()
    {
        return $this->hasMany(SupportTicket::class);
    }
}
