<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KnowledgeArticle extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'category',
        'summary',
        'content',
        'content_type',
        'content_format',
        'category_id',
        'visibility',
        'visibility_roles',
        'is_published',
        'status',
        'is_featured',
        'is_promoted',
        'featured_at',
        'featured_order',
        'published_at',
        'author_name',
        'author_title',
        'reading_time_minutes',
        'language',
        'translation_status',
        'is_machine_translated',
        'translation_of_id',
        'seo_title',
        'seo_description',
        'template_key',
        'allow_comments',
        'review_due_at',
        'reviewed_at',
        'reviewed_by_user_id',
        'review_notes',
        'expires_at',
        'archived_at',
        'scheduled_for',
        'view_count',
        'helpful_count',
        'unhelpful_count',
        'rating_count',
        'rating_avg',
        'comment_count',
        'share_count',
        'download_count',
        'current_version',
        'meta',
        'created_by_user_id',
        'updated_by_user_id',
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'is_featured' => 'boolean',
        'is_promoted' => 'boolean',
        'allow_comments' => 'boolean',
        'is_machine_translated' => 'boolean',
        'visibility_roles' => 'array',
        'meta' => 'array',
        'published_at' => 'datetime',
        'featured_at' => 'datetime',
        'review_due_at' => 'datetime',
        'reviewed_at' => 'datetime',
        'expires_at' => 'datetime',
        'archived_at' => 'datetime',
        'scheduled_for' => 'datetime',
    ];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function category()
    {
        return $this->belongsTo(KnowledgeCategory::class, 'category_id');
    }

    public function tags()
    {
        return $this->belongsToMany(KnowledgeTag::class, 'knowledge_article_tag');
    }

    public function relations()
    {
        return $this->hasMany(KnowledgeArticleRelation::class, 'knowledge_article_id');
    }

    public function versions()
    {
        return $this->hasMany(KnowledgeArticleVersion::class, 'knowledge_article_id');
    }

    public function resources()
    {
        return $this->hasMany(KnowledgeArticleResource::class, 'knowledge_article_id');
    }

    public function feedback()
    {
        return $this->hasMany(KnowledgeArticleFeedback::class, 'knowledge_article_id');
    }

    public function comments()
    {
        return $this->hasMany(KnowledgeArticleComment::class, 'knowledge_article_id');
    }

    public function views()
    {
        return $this->hasMany(KnowledgeArticleView::class, 'knowledge_article_id');
    }

    public function translations()
    {
        return $this->hasMany(self::class, 'translation_of_id');
    }

    public function translationRoot()
    {
        return $this->belongsTo(self::class, 'translation_of_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by_user_id');
    }

    public function reviewedBy()
    {
        return $this->belongsTo(User::class, 'reviewed_by_user_id');
    }

    public function supportTickets()
    {
        return $this->belongsToMany(SupportTicket::class, 'knowledge_article_support_ticket')
            ->withPivot(['context', 'added_by_user_id'])
            ->withTimestamps();
    }

    public function attachments()
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }

    public function scopeVisibleTo($query, ?User $user)
    {
        if (!$user) {
            return $query->where('status', 'published')->where('visibility', 'public');
        }

        $query->where('status', 'published');

        return $query->where(function ($builder) use ($user) {
            $builder->where('visibility', 'public')
                ->orWhere(function ($sub) use ($user) {
                    $sub->where('visibility', 'customer')
                        ->where(function ($customerScope) use ($user) {
                            $customerScope->whereRaw('? = 1', [$user->isCustomer() ? 1 : 0]);
                        });
                })
                ->orWhere(function ($sub) use ($user) {
                    $sub->where('visibility', 'internal')
                        ->where(function ($internalScope) use ($user) {
                            $internalScope->whereRaw('? = 1', [$user->isOperations() ? 1 : 0]);
                        });
                })
                ->orWhere(function ($sub) use ($user) {
                    $sub->where('visibility', 'role')
                        ->where(function ($roleScope) use ($user) {
                            $roles = $user->getRoleNames()->toArray();
                            if (empty($roles)) {
                                $roleScope->whereRaw('1 = 0');
                                return;
                            }
                            $roleScope->where(function ($roleQuery) use ($roles) {
                                foreach ($roles as $role) {
                                    $roleQuery->orWhereJsonContains('visibility_roles', $role);
                                }
                            });
                        });
                });
        });
    }
}
