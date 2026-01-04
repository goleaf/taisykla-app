<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Invoice extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'invoice_number',
        'work_order_id',
        'organization_id',
        'status',
        'invoice_type',
        'issued_at',
        'subtotal',
        'tax',
        'total',
        'labor_subtotal',
        'parts_subtotal',
        'fees_subtotal',
        'discount_total',
        'adjustment_total',
        'tax_total',
        'balance_due',
        'currency',
        'parent_invoice_id',
        'credit_applied',
        'due_date',
        'sent_at',
        'paid_at',
        'terms',
        'notes',
    ];

    protected $casts = [
        'due_date' => 'date',
        'issued_at' => 'datetime',
        'sent_at' => 'datetime',
        'paid_at' => 'datetime',
        'subtotal' => 'decimal:2',
        'tax' => 'decimal:2',
        'total' => 'decimal:2',
        'labor_subtotal' => 'decimal:2',
        'parts_subtotal' => 'decimal:2',
        'fees_subtotal' => 'decimal:2',
        'discount_total' => 'decimal:2',
        'adjustment_total' => 'decimal:2',
        'tax_total' => 'decimal:2',
        'balance_due' => 'decimal:2',
        'credit_applied' => 'decimal:2',
    ];

    public function workOrder()
    {
        return $this->belongsTo(WorkOrder::class);
    }

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function items()
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function parentInvoice()
    {
        return $this->belongsTo(Invoice::class, 'parent_invoice_id');
    }

    public function childInvoices()
    {
        return $this->hasMany(Invoice::class, 'parent_invoice_id');
    }

    public function creditMemos()
    {
        return $this->hasMany(CreditMemo::class);
    }

    public function paymentApplications()
    {
        return $this->hasMany(PaymentApplication::class);
    }
}
