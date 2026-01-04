<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BillingReminder extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_id',
        'organization_id',
        'reminder_type',
        'channel',
        'scheduled_at',
        'sent_at',
        'status',
        'attempts',
        'template_id',
        'last_error',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'sent_at' => 'datetime',
        'attempts' => 'integer',
    ];

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function template()
    {
        return $this->belongsTo(CommunicationTemplate::class, 'template_id');
    }
}
