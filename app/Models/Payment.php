<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Payment extends Model
{
    use HasFactory, SoftDeletes, \App\Traits\Auditable;

    protected $fillable = [
        'invoice_id',
        'amount',
        'method',
        'reference',
        'status',
        'gateway',
        'fee_amount',
        'currency',
        'paid_at',
        'processed_at',
        'refund_amount',
        'refunded_at',
        'payment_method_id',
        'check_number',
        'deposited_at',
        'cleared_at',
        'bounced_at',
        'bounce_reason',
        'overpayment_amount',
        'applied_amount',
        'notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'fee_amount' => 'decimal:2',
        'refund_amount' => 'decimal:2',
        'overpayment_amount' => 'decimal:2',
        'applied_amount' => 'decimal:2',
        'paid_at' => 'datetime',
        'processed_at' => 'datetime',
        'refunded_at' => 'datetime',
        'deposited_at' => 'datetime',
        'cleared_at' => 'datetime',
        'bounced_at' => 'datetime',
    ];

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function paymentMethod()
    {
        return $this->belongsTo(PaymentMethod::class);
    }

    public function applications()
    {
        return $this->hasMany(PaymentApplication::class);
    }

    public function auditLogs()
    {
        return $this->morphMany(AuditLog::class, 'subject');
    }
}
