<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KnowledgeArticleResource extends Model
{
    use HasFactory;

    protected $fillable = [
        'knowledge_article_id',
        'label',
        'resource_type',
        'url',
        'file_type',
        'file_size',
        'is_downloadable',
        'meta',
    ];

    protected $casts = [
        'is_downloadable' => 'boolean',
        'meta' => 'array',
    ];

    public function article()
    {
        return $this->belongsTo(KnowledgeArticle::class, 'knowledge_article_id');
    }
}
