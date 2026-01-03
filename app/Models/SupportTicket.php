<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SupportTicket extends Model
{
    use HasFactory;

    protected $fillable = [
        'organization_id',
        'work_order_id',
        'submitted_by_user_id',
        'assigned_to_user_id',
        'status',
        'priority',
        'subject',
        'description',
        'resolution_notes',
        'resolved_at',
    ];

    protected $casts = [
        'resolved_at' => 'datetime',
    ];

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function workOrder()
    {
        return $this->belongsTo(WorkOrder::class);
    }

    public function submittedBy()
    {
        return $this->belongsTo(User::class, 'submitted_by_user_id');
    }

    public function assignedTo()
    {
        return $this->belongsTo(User::class, 'assigned_to_user_id');
    }

    public function knowledgeArticles()
    {
        return $this->belongsToMany(KnowledgeArticle::class, 'knowledge_article_support_ticket')
            ->withPivot(['context', 'added_by_user_id'])
            ->withTimestamps();
    }
}
