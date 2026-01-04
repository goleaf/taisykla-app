<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReportDashboard extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'dashboard_type',
        'description',
        'filters',
        'layout',
        'is_default',
        'is_public',
        'created_by_user_id',
    ];

    protected $casts = [
        'filters' => 'array',
        'layout' => 'array',
        'is_default' => 'boolean',
        'is_public' => 'boolean',
    ];

    public function widgets()
    {
        return $this->hasMany(ReportDashboardWidget::class, 'dashboard_id')->orderBy('sort_order');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }
}
