<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Organization extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type',
        'status',
        'primary_contact_name',
        'primary_contact_email',
        'primary_contact_phone',
        'billing_email',
        'billing_address',
        'service_agreement_id',
        'notes',
    ];

    public function serviceAgreement()
    {
        return $this->belongsTo(ServiceAgreement::class);
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
