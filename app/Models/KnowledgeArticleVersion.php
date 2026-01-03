<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KnowledgeArticleVersion extends Model
{
    use HasFactory;

    protected $fillable = [
        'knowledge_article_id',
        'version',
        'title',
        'summary',
        'content',
        'content_format',
        'meta',
        'created_by_user_id',
    ];

    protected $casts = [
        'meta' => 'array',
    ];

    public function article()
    {
        return $this->belongsTo(KnowledgeArticle::class, 'knowledge_article_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }
}
