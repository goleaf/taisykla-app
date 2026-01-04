<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaxExemption extends Model
{
    use HasFactory;

    protected $fillable = [
        'organization_id',
        'reason',
        'certificate_number',
        'valid_until',
        'notes',
    ];

    protected $casts = [
        'valid_until' => 'date',
    ];

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }
}
