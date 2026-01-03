<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KnowledgeArticleRelation extends Model
{
    use HasFactory;

    protected $fillable = [
        'knowledge_article_id',
        'related_article_id',
        'relation_type',
        'sort_order',
    ];

    public function article()
    {
        return $this->belongsTo(KnowledgeArticle::class, 'knowledge_article_id');
    }

    public function relatedArticle()
    {
        return $this->belongsTo(KnowledgeArticle::class, 'related_article_id');
    }
}
