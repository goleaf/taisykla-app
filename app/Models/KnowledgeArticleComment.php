<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KnowledgeArticleComment extends Model
{
    use HasFactory;

    protected $fillable = [
        'knowledge_article_id',
        'user_id',
        'body',
        'is_helpful',
        'is_approved',
        'is_flagged',
        'flag_reason',
        'upvotes',
        'downvotes',
    ];

    protected $casts = [
        'is_helpful' => 'boolean',
        'is_approved' => 'boolean',
        'is_flagged' => 'boolean',
    ];

    public function article()
    {
        return $this->belongsTo(KnowledgeArticle::class, 'knowledge_article_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function votes()
    {
        return $this->hasMany(KnowledgeArticleCommentVote::class, 'knowledge_article_comment_id');
    }
}
