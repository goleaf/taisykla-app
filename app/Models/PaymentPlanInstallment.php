<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentPlanInstallment extends Model
{
    use HasFactory;

    protected $fillable = [
        'payment_plan_id',
        'due_date',
        'amount',
        'status',
        'paid_at',
        'payment_id',
        'attempt_count',
        'last_attempt_at',
        'notes',
    ];

    protected $casts = [
        'due_date' => 'date',
        'amount' => 'decimal:2',
        'paid_at' => 'datetime',
        'attempt_count' => 'integer',
        'last_attempt_at' => 'datetime',
    ];

    public function plan()
    {
        return $this->belongsTo(PaymentPlan::class, 'payment_plan_id');
    }

    public function payment()
    {
        return $this->belongsTo(Payment::class);
    }
}
