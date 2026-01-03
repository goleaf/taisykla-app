<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class CustomStatus extends Model
{
    use HasFactory;

    protected $fillable = [
        'context',
        'key',
        'label',
        'state',
        'color',
        'text_color',
        'icon',
        'is_default',
        'is_terminal',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'is_terminal' => 'boolean',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function transitionsFrom()
    {
        return $this->hasMany(CustomStatusTransition::class, 'from_status_id');
    }

    public function transitionsTo()
    {
        return $this->hasMany(CustomStatusTransition::class, 'to_status_id');
    }

    public function scopeForContext(Builder $query, string $context): Builder
    {
        return $query->where('context', $context);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}
