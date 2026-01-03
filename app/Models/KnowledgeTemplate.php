<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KnowledgeTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'content_type',
        'description',
        'sections',
        'body',
        'is_active',
    ];

    protected $casts = [
        'sections' => 'array',
        'is_active' => 'boolean',
    ];
}
