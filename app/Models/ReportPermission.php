<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReportPermission extends Model
{
    use HasFactory;

    protected $fillable = [
        'report_id',
        'user_id',
        'role',
        'can_view',
        'can_edit',
        'can_share',
        'allowed_fields',
    ];

    protected $casts = [
        'can_view' => 'boolean',
        'can_edit' => 'boolean',
        'can_share' => 'boolean',
        'allowed_fields' => 'array',
    ];

    public function report()
    {
        return $this->belongsTo(Report::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
