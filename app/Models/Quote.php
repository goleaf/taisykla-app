<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Quote extends Model
{
    use HasFactory, SoftDeletes, \App\Traits\Auditable;

    protected $fillable = [
        'quote_number',
        'work_order_id',
        'organization_id',
        'status',
        'quote_type',
        'version',
        'subtotal',
        'tax',
        'total',
        'labor_subtotal',
        'parts_subtotal',
        'fees_subtotal',
        'discount_total',
        'tax_total',
        'valid_until',
        'expires_at',
        'approved_at',
        'approved_by_user_id',
        'rejected_at',
        'rejection_reason',
        'signature_name',
        'signature_data',
        'signature_ip',
        'revision_of_quote_id',
        'sent_at',
        'terms',
        'currency',
        'notes',
    ];

    protected $casts = [
        'valid_until' => 'date',
        'expires_at' => 'datetime',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
        'sent_at' => 'datetime',
        'subtotal' => 'decimal:2',
        'tax' => 'decimal:2',
        'total' => 'decimal:2',
        'labor_subtotal' => 'decimal:2',
        'parts_subtotal' => 'decimal:2',
        'fees_subtotal' => 'decimal:2',
        'discount_total' => 'decimal:2',
        'tax_total' => 'decimal:2',
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
        return $this->hasMany(QuoteItem::class);
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by_user_id');
    }

    public function revisionOf()
    {
        return $this->belongsTo(Quote::class, 'revision_of_quote_id');
    }

    public function revisions()
    {
        return $this->hasMany(Quote::class, 'revision_of_quote_id');
    }

    public function auditLogs()
    {
        return $this->morphMany(AuditLog::class, 'subject');
    }
}
