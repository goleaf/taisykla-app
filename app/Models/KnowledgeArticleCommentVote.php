<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KnowledgeArticleCommentVote extends Model
{
    use HasFactory;

    protected $fillable = [
        'knowledge_article_comment_id',
        'user_id',
        'value',
    ];

    public function comment()
    {
        return $this->belongsTo(KnowledgeArticleComment::class, 'knowledge_article_comment_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
