<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KnowledgeSearchLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'query',
        'filters',
        'results_count',
        'had_click',
        'clicked_article_id',
    ];

    protected $casts = [
        'filters' => 'array',
        'had_click' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function clickedArticle()
    {
        return $this->belongsTo(KnowledgeArticle::class, 'clicked_article_id');
    }
}
