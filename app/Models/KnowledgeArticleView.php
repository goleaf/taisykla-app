<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KnowledgeArticleView extends Model
{
    use HasFactory;

    protected $fillable = [
        'knowledge_article_id',
        'user_id',
        'session_id',
        'ip_address',
        'user_agent',
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
