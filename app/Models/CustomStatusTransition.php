<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomStatusTransition extends Model
{
    use HasFactory;

    protected $fillable = [
        'from_status_id',
        'to_status_id',
    ];

    public function fromStatus()
    {
        return $this->belongsTo(CustomStatus::class, 'from_status_id');
    }

    public function toStatus()
    {
        return $this->belongsTo(CustomStatus::class, 'to_status_id');
    }
}
