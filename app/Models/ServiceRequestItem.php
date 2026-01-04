<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceRequestItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'service_request_id',
        'description',
        'quantity',
        'unit_price',
        'total_price',
    ];

    public function serviceRequest()
    {
        return $this->belongsTo(ServiceRequest::class);
    }
}
