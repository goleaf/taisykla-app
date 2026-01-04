<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceRequestAttachment extends Model
{
    use HasFactory;

    protected $fillable = [
        'service_request_id',
        'file_path',
        'file_name',
        'file_type',
        'file_size',
        'uploaded_by',
    ];

    public function serviceRequest()
    {
        return $this->belongsTo(ServiceRequest::class);
    }
}
