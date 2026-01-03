<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KnowledgeArticleFeedback extends Model
{
    use HasFactory;

    protected $fillable = [
        'knowledge_article_id',
        'user_id',
        'feedback_type',
        'is_helpful',
        'rating',
        'notes',
        'status',
    ];

    protected $casts = [
        'is_helpful' => 'boolean',
    ];

    public function article()
    {
        return $this->belongsTo(KnowledgeArticle::class, 'knowledge_article_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
