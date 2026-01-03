<?php

namespace App\Livewire\KnowledgeBase;

use App\Models\KnowledgeArticle;
use App\Models\KnowledgeArticleComment;
use App\Models\KnowledgeArticleCommentVote;
use App\Models\KnowledgeArticleFeedback;
use App\Models\KnowledgeArticleView;
use App\Models\KnowledgeSearchLog;
use App\Support\PermissionCatalog;
use Illuminate\Support\Str;
use Livewire\Component;

class Show extends Component
{
    public KnowledgeArticle $article;
    public string $newComment = '';
    public int $rating = 0;
    public bool $helpfulSubmitted = false;
    public bool $ratingSubmitted = false;
    public string $reportNotes = '';
    public string $updateRequestNotes = '';

    public function mount(KnowledgeArticle $article): void
    {
        $user = auth()->user();
        abort_unless($user?->can(PermissionCatalog::KNOWLEDGE_BASE_VIEW), 403);

        if (! $user?->canManageKnowledgeBase()) {
            $canView = KnowledgeArticle::query()
                ->visibleTo($user)
                ->where('is_published', true)
                ->whereKey($article->id)
                ->exists();
            abort_unless($canView, 403);
        }

        $this->article = $article->load(['category', 'tags', 'resources', 'translations']);
        $this->trackView();
        $this->trackSearchClick();

        $this->helpfulSubmitted = KnowledgeArticleFeedback::query()
            ->where('knowledge_article_id', $article->id)
            ->where('user_id', $user?->id)
            ->where('feedback_type', 'helpful')
            ->exists();
        $this->ratingSubmitted = KnowledgeArticleFeedback::query()
            ->where('knowledge_article_id', $article->id)
            ->where('user_id', $user?->id)
            ->where('feedback_type', 'rating')
            ->exists();
    }

    public function render()
    {
        $article = $this->article->fresh(['comments.user', 'relations', 'category', 'tags', 'resources', 'createdBy']);

        $content = $this->renderContent($article->content, $article->content_format ?? 'html');
        $toc = $this->buildToc($article->content, $article->content_format ?? 'html');

        $relations = $article->relations()->get();
        $related = $relations->where('relation_type', 'related');
        $seeAlso = $relations->where('relation_type', 'see_also');
        $prerequisites = $relations->where('relation_type', 'prerequisite');
        $series = $relations->where('relation_type', 'series')->sortBy('sort_order');

        $autoRelated = KnowledgeArticle::query()
            ->where('id', '!=', $article->id)
            ->whereHas('tags', function ($builder) use ($article) {
                $builder->whereIn('knowledge_tags.id', $article->tags->pluck('id')->all());
            })
            ->orderByDesc('view_count')
            ->take(4)
            ->get();

        $translations = $this->translationsFor($article);

        $relatedVideos = $article->resources->where('resource_type', 'video');

        return view('livewire.knowledge-base.show', [
            'article' => $article,
            'content' => $content,
            'toc' => $toc,
            'related' => $related,
            'seeAlso' => $seeAlso,
            'prerequisites' => $prerequisites,
            'series' => $series,
            'autoRelated' => $autoRelated,
            'translations' => $translations,
            'relatedVideos' => $relatedVideos,
            'canManage' => auth()->user()?->canManageKnowledgeBase() ?? false,
        ]);
    }

    public function rateHelpful(bool $isHelpful): void
    {
        if ($this->helpfulSubmitted) {
            return;
        }

        KnowledgeArticleFeedback::create([
            'knowledge_article_id' => $this->article->id,
            'user_id' => auth()->id(),
            'feedback_type' => 'helpful',
            'is_helpful' => $isHelpful,
        ]);

        $this->article->increment($isHelpful ? 'helpful_count' : 'unhelpful_count');
        $this->helpfulSubmitted = true;
    }

    public function submitRating(int $rating): void
    {
        if ($this->ratingSubmitted) {
            return;
        }

        $rating = max(1, min(5, $rating));
        $currentCount = $this->article->rating_count;
        $currentAvg = (float) $this->article->rating_avg;
        $newAvg = (($currentAvg * $currentCount) + $rating) / ($currentCount + 1);

        KnowledgeArticleFeedback::create([
            'knowledge_article_id' => $this->article->id,
            'user_id' => auth()->id(),
            'feedback_type' => 'rating',
            'rating' => $rating,
        ]);

        $this->article->update([
            'rating_count' => $currentCount + 1,
            'rating_avg' => round($newAvg, 2),
        ]);

        $this->rating = $rating;
        $this->ratingSubmitted = true;
    }

    public function submitComment(): void
    {
        if (! $this->article->allow_comments) {
            return;
        }

        $this->validate([
            'newComment' => ['required', 'string', 'max:2000'],
        ]);

        KnowledgeArticleComment::create([
            'knowledge_article_id' => $this->article->id,
            'user_id' => auth()->id(),
            'body' => trim($this->newComment),
            'is_approved' => true,
        ]);

        $this->article->increment('comment_count');
        $this->newComment = '';
    }

    public function voteComment(int $commentId, int $value): void
    {
        $value = $value >= 0 ? 1 : -1;
        $comment = KnowledgeArticleComment::findOrFail($commentId);

        $vote = KnowledgeArticleCommentVote::firstOrCreate(
            [
                'knowledge_article_comment_id' => $comment->id,
                'user_id' => auth()->id(),
            ],
            ['value' => $value]
        );

        if ($vote->wasRecentlyCreated) {
            if ($value > 0) {
                $comment->increment('upvotes');
            } else {
                $comment->increment('downvotes');
            }
        }
    }

    public function markCommentHelpful(int $commentId): void
    {
        if (! auth()->user()?->canManageKnowledgeBase()) {
            return;
        }

        $comment = KnowledgeArticleComment::findOrFail($commentId);
        $comment->update(['is_helpful' => true]);
    }

    public function reportInaccuracy(): void
    {
        $this->validate([
            'reportNotes' => ['required', 'string', 'max:2000'],
        ]);

        KnowledgeArticleFeedback::create([
            'knowledge_article_id' => $this->article->id,
            'user_id' => auth()->id(),
            'feedback_type' => 'report',
            'notes' => trim($this->reportNotes),
        ]);

        $this->reportNotes = '';
    }

    public function requestUpdate(): void
    {
        $this->validate([
            'updateRequestNotes' => ['required', 'string', 'max:2000'],
        ]);

        KnowledgeArticleFeedback::create([
            'knowledge_article_id' => $this->article->id,
            'user_id' => auth()->id(),
            'feedback_type' => 'request_update',
            'notes' => trim($this->updateRequestNotes),
        ]);

        $this->updateRequestNotes = '';
    }

    public function trackShare(): void
    {
        $this->article->increment('share_count');
    }

    public function trackDownload(): void
    {
        $this->article->increment('download_count');
    }

    private function buildToc(string $content, string $format): array
    {
        $toc = [];
        if ($format === 'markdown') {
            $lines = preg_split('/\r?\n/', $content);
            foreach ($lines as $line) {
                if (preg_match('/^(#{1,3})\s+(.*)/', $line, $matches)) {
                    $level = strlen($matches[1]);
                    $title = trim($matches[2]);
                    $toc[] = [
                        'level' => $level,
                        'title' => $title,
                        'anchor' => Str::slug($title),
                    ];
                }
            }
        } else {
            if (preg_match_all('/<h([1-3])[^>]*>(.*?)<\/h[1-3]>/', $content, $matches, PREG_SET_ORDER)) {
                foreach ($matches as $match) {
                    $title = strip_tags($match[2]);
                    $toc[] = [
                        'level' => (int) $match[1],
                        'title' => $title,
                        'anchor' => Str::slug($title),
                    ];
                }
            }
        }

        return $toc;
    }

    private function renderContent(string $content, string $format): string
    {
        $html = $format === 'markdown' ? Str::markdown($content) : $content;

        $html = preg_replace_callback('/<h([1-3])([^>]*)>(.*?)<\/h[1-3]>/', function ($matches) {
            $title = strip_tags($matches[3]);
            $anchor = Str::slug($title);
            return sprintf('<h%s%s id="%s">%s</h%s>', $matches[1], $matches[2], $anchor, $matches[3], $matches[1]);
        }, $html);

        return $html;
    }

    private function translationsFor(KnowledgeArticle $article)
    {
        $rootId = $article->translation_of_id ?? $article->id;
        return KnowledgeArticle::query()
            ->where('id', $rootId)
            ->orWhere('translation_of_id', $rootId)
            ->get();
    }

    private function trackView(): void
    {
        KnowledgeArticleView::create([
            'knowledge_article_id' => $this->article->id,
            'user_id' => auth()->id(),
            'session_id' => session()->getId(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        $this->article->increment('view_count');
    }

    private function trackSearchClick(): void
    {
        $logId = request()->query('search_log_id');
        if (! $logId) {
            return;
        }

        $log = KnowledgeSearchLog::find($logId);
        if (! $log) {
            return;
        }

        $log->update([
            'had_click' => true,
            'clicked_article_id' => $this->article->id,
        ]);
    }
}
