<?php

namespace App\Livewire\KnowledgeBase;

use App\Models\KnowledgeArticle;
use App\Models\KnowledgeArticleRelation;
use App\Models\KnowledgeArticleResource;
use App\Models\KnowledgeArticleVersion;
use App\Models\KnowledgeCategory;
use App\Models\KnowledgeSearchLog;
use App\Models\KnowledgeTag;
use App\Models\KnowledgeTemplate;
use App\Models\SupportTicket;
use App\Models\User;
use App\Support\PermissionCatalog;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public bool $showForm = false;
    public string $search = '';
    public ?int $categoryFilter = null;
    public array $tagFilters = [];
    public string $contentTypeFilter = '';
    public string $authorFilter = '';
    public string $languageFilter = '';
    public string $visibilityFilter = '';
    public string $statusFilter = 'published';
    public string $sort = 'relevance';
    public ?string $dateFrom = null;
    public ?string $dateTo = null;
    public array $form = [];
    public ?int $editingId = null;
    public array $selectedArticles = [];
    public string $bulkAction = '';
    public ?int $bulkCategoryId = null;

    public bool $showCategoryForm = false;
    public array $categoryForm = [];
    public ?int $editingCategoryId = null;

    public bool $showTagForm = false;
    public array $tagForm = [];
    public ?int $editingTagId = null;
    public ?int $mergeTargetTagId = null;

    public bool $showTemplateForm = false;
    public array $templateForm = [];
    public ?int $editingTemplateId = null;

    public array $suggestedTags = [];
    public string $lastLoggedHash = '';
    public ?int $lastLoggedSearchId = null;

    public bool $showSubmissionForm = false;
    public array $submission = [];

    protected $paginationTheme = 'tailwind';

    public array $statusOptions = [
        'all' => 'All',
        'draft' => 'Draft',
        'review' => 'In review',
        'published' => 'Published',
        'archived' => 'Archived',
    ];

    public array $contentTypeOptions = [
        'how_to' => 'How-to guide',
        'troubleshooting' => 'Troubleshooting',
        'faq' => 'FAQ',
        'documentation' => 'Product documentation',
        'best_practice' => 'Best practice',
        'video' => 'Video tutorial',
        'download' => 'Downloadable resource',
        'quick_reference' => 'Quick reference',
    ];

    public array $visibilityOptions = [
        'public' => 'Public',
        'customer' => 'Customer-only',
        'internal' => 'Internal-only',
        'role' => 'Role-based',
    ];

    public array $sortOptions = [
        'relevance' => 'Relevance',
        'updated' => 'Recently updated',
        'popular' => 'Most viewed',
    ];

    public array $translationStatusOptions = [
        'draft' => 'Draft',
        'in_review' => 'In review',
        'published' => 'Published',
    ];

    public array $languageOptions = [
        'en' => 'English',
        'es' => 'Spanish',
        'fr' => 'French',
        'de' => 'German',
        'lt' => 'Lithuanian',
    ];

    public function mount(): void
    {
        abort_unless(auth()->user()?->can(PermissionCatalog::KNOWLEDGE_BASE_VIEW), 403);

        $this->resetForm();
        $this->resetCategoryForm();
        $this->resetTagForm();
        $this->resetTemplateForm();
        $this->resetSubmission();

        if (! $this->canManage) {
            $this->statusFilter = 'published';
        }
    }

    public function resetForm(): void
    {
        $this->form = [
            'title' => '',
            'summary' => '',
            'content' => '',
            'content_format' => 'html',
            'content_type' => 'how_to',
            'category_id' => null,
            'tags' => [],
            'visibility' => 'public',
            'visibility_roles' => [],
            'status' => 'draft',
            'is_featured' => false,
            'is_promoted' => false,
            'author_name' => '',
            'author_title' => '',
            'language' => 'en',
            'translation_status' => 'published',
            'translation_of_id' => null,
            'is_machine_translated' => false,
            'seo_title' => '',
            'seo_description' => '',
            'template_key' => null,
            'allow_comments' => true,
            'review_due_at' => null,
            'expires_at' => null,
            'scheduled_for' => null,
            'resources' => [
                ['label' => '', 'resource_type' => 'file', 'url' => '', 'file_type' => '', 'is_downloadable' => true],
            ],
            'related' => [],
            'see_also' => [],
            'prerequisites' => [],
            'series' => [],
        ];
        $this->suggestedTags = [];
    }

    public function resetCategoryForm(): void
    {
        $this->categoryForm = [
            'name' => '',
            'description' => '',
            'icon' => '',
            'parent_id' => null,
            'sort_order' => 0,
            'is_active' => true,
        ];
        $this->editingCategoryId = null;
    }

    public function resetTagForm(): void
    {
        $this->tagForm = [
            'name' => '',
            'slug' => '',
        ];
        $this->editingTagId = null;
        $this->mergeTargetTagId = null;
    }

    public function resetTemplateForm(): void
    {
        $this->templateForm = [
            'name' => '',
            'content_type' => 'how_to',
            'description' => '',
            'sections' => '',
            'body' => '',
            'is_active' => true,
        ];
        $this->editingTemplateId = null;
    }

    public function resetSubmission(): void
    {
        $this->submission = [
            'title' => '',
            'summary' => '',
            'content' => '',
            'category_id' => null,
            'tags' => '',
        ];
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedCategoryFilter(): void
    {
        $this->resetPage();
    }

    public function updatedStatusFilter(): void
    {
        $this->resetPage();
    }

    public function updatedContentTypeFilter(): void
    {
        $this->resetPage();
    }

    public function updatedAuthorFilter(): void
    {
        $this->resetPage();
    }

    public function updatedDateFrom(): void
    {
        $this->resetPage();
    }

    public function updatedDateTo(): void
    {
        $this->resetPage();
    }

    public function updatedFormContent(): void
    {
        $this->suggestedTags = $this->buildTagSuggestions();
    }

    public function clearFilters(): void
    {
        $this->search = '';
        $this->categoryFilter = null;
        $this->tagFilters = [];
        $this->contentTypeFilter = '';
        $this->authorFilter = '';
        $this->languageFilter = '';
        $this->visibilityFilter = '';
        $this->statusFilter = $this->canManage ? 'all' : 'published';
        $this->sort = 'relevance';
        $this->dateFrom = null;
        $this->dateTo = null;
        $this->resetPage();
    }

    public function startCreate(): void
    {
        if (! $this->canManage) {
            return;
        }

        $this->editingId = null;
        $this->resetForm();
        $this->showForm = true;
    }

    public function editArticle(int $articleId): void
    {
        if (! $this->canManage) {
            return;
        }

        $article = KnowledgeArticle::with(['tags', 'resources', 'relations'])->findOrFail($articleId);
        $this->editingId = $article->id;
        $this->form = [
            'title' => $article->title,
            'summary' => $article->summary ?? '',
            'content' => $article->content,
            'content_format' => $article->content_format ?? 'html',
            'content_type' => $article->content_type ?? 'how_to',
            'category_id' => $article->category_id,
            'tags' => $article->tags->pluck('id')->all(),
            'visibility' => $article->visibility ?? 'public',
            'visibility_roles' => $article->visibility_roles ?? [],
            'status' => $article->status ?? 'draft',
            'is_featured' => (bool) $article->is_featured,
            'is_promoted' => (bool) $article->is_promoted,
            'author_name' => $article->author_name ?? '',
            'author_title' => $article->author_title ?? '',
            'language' => $article->language ?? 'en',
            'translation_status' => $article->translation_status ?? 'published',
            'translation_of_id' => $article->translation_of_id,
            'is_machine_translated' => (bool) $article->is_machine_translated,
            'seo_title' => $article->seo_title ?? '',
            'seo_description' => $article->seo_description ?? '',
            'template_key' => $article->template_key,
            'allow_comments' => (bool) $article->allow_comments,
            'review_due_at' => $article->review_due_at?->format('Y-m-d'),
            'expires_at' => $article->expires_at?->format('Y-m-d'),
            'scheduled_for' => $article->scheduled_for?->format('Y-m-d\TH:i'),
            'resources' => $article->resources->map(function ($resource) {
                return [
                    'label' => $resource->label,
                    'resource_type' => $resource->resource_type,
                    'url' => $resource->url,
                    'file_type' => $resource->file_type,
                    'is_downloadable' => (bool) $resource->is_downloadable,
                ];
            })->all(),
            'related' => $this->relationIdsFor($article, 'related'),
            'see_also' => $this->relationIdsFor($article, 'see_also'),
            'prerequisites' => $this->relationIdsFor($article, 'prerequisite'),
            'series' => $this->relationIdsFor($article, 'series'),
        ];
        if (empty($this->form['resources'])) {
            $this->form['resources'] = [
                ['label' => '', 'resource_type' => 'file', 'url' => '', 'file_type' => '', 'is_downloadable' => true],
            ];
        }
        $this->showForm = true;
    }

    public function cancelEdit(): void
    {
        $this->editingId = null;
        $this->resetForm();
        $this->showForm = false;
    }

    protected function rules(): array
    {
        return [
            'form.title' => ['required', 'string', 'max:255'],
            'form.summary' => ['nullable', 'string', 'max:600'],
            'form.content' => ['required', 'string'],
            'form.content_format' => ['required', 'string', 'max:50'],
            'form.content_type' => ['required', 'string', 'max:50'],
            'form.category_id' => ['nullable', 'exists:knowledge_categories,id'],
            'form.tags' => ['array'],
            'form.tags.*' => ['exists:knowledge_tags,id'],
            'form.visibility' => ['required', 'string', 'max:50'],
            'form.visibility_roles' => ['array'],
            'form.status' => ['required', 'string', 'max:50'],
            'form.author_name' => ['nullable', 'string', 'max:255'],
            'form.author_title' => ['nullable', 'string', 'max:255'],
            'form.language' => ['required', 'string', 'max:10'],
            'form.translation_status' => ['required', 'string', 'max:50'],
            'form.translation_of_id' => ['nullable', 'exists:knowledge_articles,id'],
            'form.is_machine_translated' => ['boolean'],
            'form.seo_title' => ['nullable', 'string', 'max:255'],
            'form.seo_description' => ['nullable', 'string', 'max:500'],
            'form.template_key' => ['nullable', 'string', 'max:255'],
            'form.allow_comments' => ['boolean'],
            'form.review_due_at' => ['nullable', 'date'],
            'form.expires_at' => ['nullable', 'date'],
            'form.scheduled_for' => ['nullable', 'date'],
            'form.resources' => ['array'],
            'form.resources.*.label' => ['nullable', 'string', 'max:255'],
            'form.resources.*.resource_type' => ['nullable', 'string', 'max:50'],
            'form.resources.*.url' => ['nullable', 'string', 'max:255'],
            'form.resources.*.file_type' => ['nullable', 'string', 'max:50'],
            'form.resources.*.is_downloadable' => ['boolean'],
        ];
    }

    public function addResourceRow(): void
    {
        $this->form['resources'][] = ['label' => '', 'resource_type' => 'file', 'url' => '', 'file_type' => '', 'is_downloadable' => true];
    }

    public function removeResourceRow(int $index): void
    {
        $resources = $this->form['resources'] ?? [];
        unset($resources[$index]);
        $this->form['resources'] = array_values($resources);
    }

    public function saveArticle(): void
    {
        if (! $this->canManage) {
            return;
        }

        $this->validate();

        $userId = auth()->id();
        $title = trim($this->form['title']);
        $category = $this->form['category_id']
            ? KnowledgeCategory::find($this->form['category_id'])
            : null;

        $status = $this->form['status'];
        $isPublished = $status === 'published';

        $payload = [
            'title' => $title,
            'summary' => trim((string) $this->form['summary']),
            'content' => $this->form['content'],
            'content_format' => $this->form['content_format'],
            'content_type' => $this->form['content_type'],
            'category_id' => $this->form['category_id'],
            'category' => $category?->name,
            'visibility' => $this->form['visibility'],
            'visibility_roles' => $this->form['visibility_roles'] ?? [],
            'status' => $status,
            'is_published' => $isPublished,
            'published_at' => $isPublished ? now() : null,
            'is_featured' => (bool) $this->form['is_featured'],
            'is_promoted' => (bool) $this->form['is_promoted'],
            'author_name' => $this->form['author_name'] !== '' ? trim($this->form['author_name']) : null,
            'author_title' => $this->form['author_title'] !== '' ? trim($this->form['author_title']) : null,
            'reading_time_minutes' => $this->estimateReadingTime($this->form['content']),
            'language' => $this->form['language'],
            'translation_status' => $this->form['translation_status'],
            'translation_of_id' => $this->form['translation_of_id'],
            'is_machine_translated' => (bool) $this->form['is_machine_translated'],
            'seo_title' => $this->form['seo_title'] !== '' ? trim($this->form['seo_title']) : null,
            'seo_description' => $this->form['seo_description'] !== '' ? trim($this->form['seo_description']) : null,
            'template_key' => $this->form['template_key'],
            'allow_comments' => (bool) $this->form['allow_comments'],
            'review_due_at' => $this->form['review_due_at'],
            'expires_at' => $this->form['expires_at'],
            'scheduled_for' => $this->form['scheduled_for'],
            'updated_by_user_id' => $userId,
        ];

        if ($this->editingId) {
            $article = KnowledgeArticle::findOrFail($this->editingId);
            $payload['slug'] = $article->title === $title
                ? $article->slug
                : $this->uniqueSlug($title, $article->id);
            $article->update($payload);
            $article->current_version = $article->current_version + 1;
            $article->save();
        } else {
            $payload['slug'] = $this->uniqueSlug($title);
            $payload['created_by_user_id'] = $userId;
            $article = KnowledgeArticle::create($payload);
        }

        $tagIds = $this->form['tags'] ?? [];
        $article->tags()->sync($tagIds);
        $this->refreshTagUsageCounts($tagIds);

        $this->syncResources($article, $this->form['resources'] ?? []);
        $this->syncRelations($article, 'related', $this->form['related'] ?? []);
        $this->syncRelations($article, 'see_also', $this->form['see_also'] ?? []);
        $this->syncRelations($article, 'prerequisite', $this->form['prerequisites'] ?? []);
        $this->syncRelations($article, 'series', $this->form['series'] ?? []);

        KnowledgeArticleVersion::create([
            'knowledge_article_id' => $article->id,
            'version' => $article->current_version,
            'title' => $article->title,
            'summary' => $article->summary,
            'content' => $article->content,
            'content_format' => $article->content_format ?? 'html',
            'meta' => ['status' => $article->status, 'visibility' => $article->visibility],
            'created_by_user_id' => $userId,
        ]);

        session()->flash('status', $this->editingId ? 'Article updated.' : 'Article created.');

        $this->resetForm();
        $this->editingId = null;
        $this->showForm = false;
        $this->resetPage();
    }

    public function submitForReview(int $articleId): void
    {
        if (! $this->canManage) {
            return;
        }

        KnowledgeArticle::whereKey($articleId)->update([
            'status' => 'review',
            'reviewed_at' => null,
            'reviewed_by_user_id' => null,
        ]);
    }

    public function approveArticle(int $articleId): void
    {
        if (! $this->canManage) {
            return;
        }

        KnowledgeArticle::whereKey($articleId)->update([
            'status' => 'published',
            'is_published' => true,
            'published_at' => now(),
            'reviewed_at' => now(),
            'reviewed_by_user_id' => auth()->id(),
        ]);
    }

    public function rejectArticle(int $articleId): void
    {
        if (! $this->canManage) {
            return;
        }

        KnowledgeArticle::whereKey($articleId)->update([
            'status' => 'draft',
            'review_notes' => 'Revision requested.',
        ]);
    }

    public function archiveArticle(int $articleId): void
    {
        if (! $this->canManage) {
            return;
        }

        KnowledgeArticle::whereKey($articleId)->update([
            'status' => 'archived',
            'archived_at' => now(),
        ]);
    }

    public function featureArticle(int $articleId): void
    {
        if (! $this->canManage) {
            return;
        }

        KnowledgeArticle::whereKey($articleId)->update([
            'is_featured' => true,
            'featured_at' => now(),
        ]);
    }

    public function applyBulkAction(): void
    {
        if (! $this->canManage || empty($this->selectedArticles) || $this->bulkAction === '') {
            return;
        }

        $query = KnowledgeArticle::whereIn('id', $this->selectedArticles);

        switch ($this->bulkAction) {
            case 'publish':
                $query->update([
                    'status' => 'published',
                    'is_published' => true,
                    'published_at' => now(),
                ]);
                break;
            case 'archive':
                $query->update([
                    'status' => 'archived',
                    'archived_at' => now(),
                ]);
                break;
            case 'feature':
                $query->update([
                    'is_featured' => true,
                    'featured_at' => now(),
                ]);
                break;
            case 'promote':
                $query->update([
                    'is_promoted' => true,
                ]);
                break;
            case 'categorize':
                if ($this->bulkCategoryId) {
                    $category = KnowledgeCategory::find($this->bulkCategoryId);
                    $query->update([
                        'category_id' => $this->bulkCategoryId,
                        'category' => $category?->name,
                    ]);
                }
                break;
        }

        $this->selectedArticles = [];
        $this->bulkAction = '';
        $this->bulkCategoryId = null;
        session()->flash('status', 'Bulk action applied.');
    }

    public function saveCategory(): void
    {
        if (! $this->canManage) {
            return;
        }

        $this->validate([
            'categoryForm.name' => ['required', 'string', 'max:255'],
            'categoryForm.description' => ['nullable', 'string'],
            'categoryForm.icon' => ['nullable', 'string', 'max:255'],
            'categoryForm.parent_id' => ['nullable', 'exists:knowledge_categories,id'],
            'categoryForm.sort_order' => ['nullable', 'integer'],
            'categoryForm.is_active' => ['boolean'],
        ]);

        $slug = Str::slug($this->categoryForm['name']);
        if ($this->editingCategoryId) {
            $category = KnowledgeCategory::findOrFail($this->editingCategoryId);
            $category->update([
                'name' => $this->categoryForm['name'],
                'slug' => $slug,
                'description' => $this->categoryForm['description'],
                'icon' => $this->categoryForm['icon'],
                'parent_id' => $this->categoryForm['parent_id'],
                'sort_order' => (int) $this->categoryForm['sort_order'],
                'is_active' => (bool) $this->categoryForm['is_active'],
            ]);
        } else {
            KnowledgeCategory::create([
                'name' => $this->categoryForm['name'],
                'slug' => $slug,
                'description' => $this->categoryForm['description'],
                'icon' => $this->categoryForm['icon'],
                'parent_id' => $this->categoryForm['parent_id'],
                'sort_order' => (int) $this->categoryForm['sort_order'],
                'is_active' => (bool) $this->categoryForm['is_active'],
            ]);
        }

        $this->resetCategoryForm();
    }

    public function editCategory(int $categoryId): void
    {
        if (! $this->canManage) {
            return;
        }

        $category = KnowledgeCategory::findOrFail($categoryId);
        $this->editingCategoryId = $category->id;
        $this->categoryForm = [
            'name' => $category->name,
            'description' => $category->description ?? '',
            'icon' => $category->icon ?? '',
            'parent_id' => $category->parent_id,
            'sort_order' => $category->sort_order,
            'is_active' => (bool) $category->is_active,
        ];
        $this->showCategoryForm = true;
    }

    public function deleteCategory(int $categoryId): void
    {
        if (! $this->canManage) {
            return;
        }

        KnowledgeCategory::whereKey($categoryId)->delete();
    }

    public function saveTag(): void
    {
        if (! $this->canManage) {
            return;
        }

        $this->validate([
            'tagForm.name' => ['required', 'string', 'max:255'],
            'tagForm.slug' => ['nullable', 'string', 'max:255'],
        ]);

        $slug = $this->tagForm['slug'] !== ''
            ? Str::slug($this->tagForm['slug'])
            : Str::slug($this->tagForm['name']);

        if ($this->editingTagId) {
            $tag = KnowledgeTag::findOrFail($this->editingTagId);
            $tag->update([
                'name' => $this->tagForm['name'],
                'slug' => $slug,
            ]);
        } else {
            KnowledgeTag::create([
                'name' => $this->tagForm['name'],
                'slug' => $slug,
            ]);
        }

        $this->resetTagForm();
    }

    public function editTag(int $tagId): void
    {
        if (! $this->canManage) {
            return;
        }

        $tag = KnowledgeTag::findOrFail($tagId);
        $this->editingTagId = $tag->id;
        $this->tagForm = [
            'name' => $tag->name,
            'slug' => $tag->slug,
        ];
        $this->showTagForm = true;
    }

    public function mergeTags(int $tagId): void
    {
        if (! $this->canManage || ! $this->mergeTargetTagId) {
            return;
        }

        $sourceTag = KnowledgeTag::findOrFail($tagId);
        $targetTag = KnowledgeTag::findOrFail($this->mergeTargetTagId);
        $articleIds = $sourceTag->articles()->pluck('knowledge_articles.id')->all();
        $targetTag->articles()->syncWithoutDetaching($articleIds);
        $sourceTag->articles()->detach();
        $sourceTag->delete();
        $this->refreshTagUsageCounts([$targetTag->id]);
        $this->mergeTargetTagId = null;
    }

    public function deleteTag(int $tagId): void
    {
        if (! $this->canManage) {
            return;
        }

        $tag = KnowledgeTag::findOrFail($tagId);
        $tag->articles()->detach();
        $tag->delete();
    }

    public function saveTemplate(): void
    {
        if (! $this->canManage) {
            return;
        }

        $sections = array_filter(array_map('trim', explode(',', $this->templateForm['sections'])));

        if ($this->editingTemplateId) {
            $template = KnowledgeTemplate::findOrFail($this->editingTemplateId);
            $template->update([
                'name' => $this->templateForm['name'],
                'content_type' => $this->templateForm['content_type'],
                'description' => $this->templateForm['description'],
                'sections' => $sections,
                'body' => $this->templateForm['body'],
                'is_active' => (bool) $this->templateForm['is_active'],
            ]);
        } else {
            KnowledgeTemplate::create([
                'name' => $this->templateForm['name'],
                'content_type' => $this->templateForm['content_type'],
                'description' => $this->templateForm['description'],
                'sections' => $sections,
                'body' => $this->templateForm['body'],
                'is_active' => (bool) $this->templateForm['is_active'],
            ]);
        }

        $this->resetTemplateForm();
    }

    public function editTemplate(int $templateId): void
    {
        if (! $this->canManage) {
            return;
        }

        $template = KnowledgeTemplate::findOrFail($templateId);
        $this->editingTemplateId = $template->id;
        $this->templateForm = [
            'name' => $template->name,
            'content_type' => $template->content_type,
            'description' => $template->description ?? '',
            'sections' => implode(', ', $template->sections ?? []),
            'body' => $template->body ?? '',
            'is_active' => (bool) $template->is_active,
        ];
        $this->showTemplateForm = true;
    }

    public function applyTemplate(int $templateId): void
    {
        $template = KnowledgeTemplate::findOrFail($templateId);
        $this->form['content'] = $template->body ?? $this->form['content'];
        $this->form['content_type'] = $template->content_type;
        $this->form['template_key'] = $template->name;
    }

    public function submitCommunityArticle(): void
    {
        $user = auth()->user();
        if (! $user) {
            return;
        }

        $this->validate([
            'submission.title' => ['required', 'string', 'max:255'],
            'submission.summary' => ['nullable', 'string', 'max:600'],
            'submission.content' => ['required', 'string'],
            'submission.category_id' => ['nullable', 'exists:knowledge_categories,id'],
            'submission.tags' => ['nullable', 'string', 'max:255'],
        ]);

        $title = trim($this->submission['title']);
        $category = $this->submission['category_id']
            ? KnowledgeCategory::find($this->submission['category_id'])
            : null;

        $article = KnowledgeArticle::create([
            'title' => $title,
            'summary' => $this->submission['summary'],
            'content' => $this->submission['content'],
            'content_type' => 'how_to',
            'content_format' => 'markdown',
            'category_id' => $this->submission['category_id'],
            'category' => $category?->name,
            'visibility' => 'public',
            'status' => 'review',
            'is_published' => false,
            'author_name' => $user->name,
            'author_title' => $user->job_title,
            'reading_time_minutes' => $this->estimateReadingTime($this->submission['content']),
            'created_by_user_id' => $user->id,
            'updated_by_user_id' => $user->id,
            'slug' => $this->uniqueSlug($title),
        ]);

        $tagNames = array_filter(array_map('trim', explode(',', (string) $this->submission['tags'])));
        $tagIds = [];
        foreach ($tagNames as $tagName) {
            $tag = KnowledgeTag::firstOrCreate(['slug' => Str::slug($tagName)], ['name' => $tagName]);
            $tagIds[] = $tag->id;
        }
        if (! empty($tagIds)) {
            $article->tags()->sync($tagIds);
            $this->refreshTagUsageCounts($tagIds);
        }

        session()->flash('status', 'Your article has been submitted for review.');
        $this->resetSubmission();
        $this->showSubmissionForm = false;
    }

    public function addSuggestedTag(int $tagId): void
    {
        $tags = $this->form['tags'] ?? [];
        if (! in_array($tagId, $tags, true)) {
            $tags[] = $tagId;
        }
        $this->form['tags'] = $tags;
    }

    public function render()
    {
        $user = auth()->user();
        $query = KnowledgeArticle::query()->with(['createdBy', 'updatedBy', 'category', 'tags']);

        if (! $this->canManage) {
            $query->visibleTo($user)->where('is_published', true);
        } elseif ($this->statusFilter !== 'all') {
            $query->where('status', $this->statusFilter);
        }

        if ($this->search !== '') {
            $this->applySearchTerms($query, $this->search);
        }

        if ($this->categoryFilter) {
            $categoryIds = $this->descendantCategoryIds($this->categoryFilter);
            $query->whereIn('category_id', $categoryIds);
        }

        if (! empty($this->tagFilters)) {
            $query->whereHas('tags', function ($builder) {
                $builder->whereIn('knowledge_tags.id', $this->tagFilters);
            });
        }

        if ($this->contentTypeFilter !== '') {
            $query->where('content_type', $this->contentTypeFilter);
        }

        if ($this->authorFilter !== '') {
            $query->where('created_by_user_id', $this->authorFilter);
        }

        if ($this->languageFilter !== '') {
            $query->where('language', $this->languageFilter);
        }

        if ($this->visibilityFilter !== '') {
            $query->where('visibility', $this->visibilityFilter);
        }

        if ($this->dateFrom) {
            $query->whereDate('updated_at', '>=', $this->dateFrom);
        }

        if ($this->dateTo) {
            $query->whereDate('updated_at', '<=', $this->dateTo);
        }

        if ($this->sort === 'popular') {
            $query->orderByDesc('view_count');
        } elseif ($this->sort === 'updated') {
            $query->orderByDesc('updated_at');
        } elseif ($this->sort === 'relevance' && $this->search !== '') {
            $like = '%' . $this->search . '%';
            $query->orderByRaw(
                'case when title like ? then 0 when summary like ? then 1 when content like ? then 2 else 3 end',
                [$like, $like, $like]
            );
            $query->orderByDesc('updated_at');
        }

        $articles = $query->paginate(10);

        $categories = KnowledgeCategory::query()
            ->with('children.children')
            ->whereNull('parent_id')
            ->orderBy('sort_order')
            ->get();
        $categoryOptions = $this->flattenCategories($categories);

        $tags = KnowledgeTag::orderBy('name')->get();
        $popularTags = KnowledgeTag::orderByDesc('usage_count')->take(12)->get();

        $authors = User::query()
            ->whereIn('id', KnowledgeArticle::query()->select('created_by_user_id')->distinct())
            ->orderBy('name')
            ->get();

        $featuredArticles = KnowledgeArticle::query()
            ->with('category')
            ->where('is_featured', true)
            ->orderBy('featured_order')
            ->orderByDesc('updated_at')
            ->take(5)
            ->get();

        $trendingArticles = KnowledgeArticle::query()
            ->where('status', 'published')
            ->orderByDesc('view_count')
            ->take(5)
            ->get();

        $recentArticles = KnowledgeArticle::query()
            ->where('status', 'published')
            ->orderByDesc('updated_at')
            ->take(5)
            ->get();

        $staffPicks = KnowledgeArticle::query()
            ->where('is_promoted', true)
            ->orderByDesc('updated_at')
            ->take(4)
            ->get();

        $videoResources = KnowledgeArticleResource::query()
            ->with('article')
            ->where('resource_type', 'video')
            ->latest()
            ->take(6)
            ->get();

        $articleOptions = KnowledgeArticle::query()
            ->orderBy('title')
            ->take(200)
            ->get();

        $templates = KnowledgeTemplate::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $searchSuggestions = $this->search !== ''
            ? $this->buildSearchSuggestions($this->search)
            : [];

        $didYouMean = $articles->total() === 0 && $this->search !== ''
            ? $this->closestSuggestion($this->search, $tags->pluck('name')->all())
            : null;

        $analytics = [
            'total_articles' => KnowledgeArticle::count(),
            'published_articles' => KnowledgeArticle::where('status', 'published')->count(),
            'draft_articles' => KnowledgeArticle::where('status', 'draft')->count(),
            'review_articles' => KnowledgeArticle::where('status', 'review')->count(),
            'stale_articles' => KnowledgeArticle::where('updated_at', '<', now()->subYear())->count(),
        ];

        $searchLogQuery = KnowledgeSearchLog::query()->latest();
        $topSearches = (clone $searchLogQuery)->select('query')
            ->selectRaw('count(*) as total')
            ->groupBy('query')
            ->orderByDesc('total')
            ->take(5)
            ->get();
        $zeroResultSearches = (clone $searchLogQuery)->where('results_count', 0)
            ->select('query')
            ->selectRaw('count(*) as total')
            ->groupBy('query')
            ->orderByDesc('total')
            ->take(5)
            ->get();

        $recentTickets = SupportTicket::query()
            ->whereNotNull('resolved_at')
            ->orderByDesc('resolved_at')
            ->take(5)
            ->get();

        $contributors = KnowledgeArticle::query()
            ->select('created_by_user_id')
            ->selectRaw('count(*) as total')
            ->groupBy('created_by_user_id')
            ->with('createdBy')
            ->orderByDesc('total')
            ->take(5)
            ->get();

        $this->logSearchIfNeeded($articles->total());

        return view('livewire.knowledge-base.index', [
            'articles' => $articles,
            'categories' => $categories,
            'categoryOptions' => $categoryOptions,
            'tags' => $tags,
            'popularTags' => $popularTags,
            'authors' => $authors,
            'featuredArticles' => $featuredArticles,
            'trendingArticles' => $trendingArticles,
            'recentArticles' => $recentArticles,
            'staffPicks' => $staffPicks,
            'videoResources' => $videoResources,
            'articleOptions' => $articleOptions,
            'templates' => $templates,
            'searchSuggestions' => $searchSuggestions,
            'didYouMean' => $didYouMean,
            'analytics' => $analytics,
            'topSearches' => $topSearches,
            'zeroResultSearches' => $zeroResultSearches,
            'recentTickets' => $recentTickets,
            'contributors' => $contributors,
            'canManage' => $this->canManage,
            'statusOptions' => $this->statusOptions,
        ]);
    }

    public function getCanManageProperty(): bool
    {
        $user = auth()->user();

        if (! $user) {
            return false;
        }

        return $user->canManageKnowledgeBase();
    }

    public function highlight(string $text): string
    {
        if ($this->search === '') {
            return e($text);
        }

        $escaped = e($text);
        $terms = $this->extractSearchTerms($this->search);
        foreach ($terms as $term) {
            if ($term === '') {
                continue;
            }
            $escaped = preg_replace('/(' . preg_quote($term, '/') . ')/i', '<mark>$1</mark>', $escaped);
        }

        return $escaped;
    }

    private function uniqueSlug(string $title, ?int $ignoreId = null): string
    {
        $baseSlug = Str::slug($title);
        $slug = $baseSlug;
        $counter = 1;

        while (KnowledgeArticle::query()
            ->when($ignoreId, function ($builder) use ($ignoreId) {
                $builder->where('id', '!=', $ignoreId);
            })
            ->where('slug', $slug)
            ->exists()) {
            $counter += 1;
            $slug = $baseSlug . '-' . $counter;
        }

        return $slug;
    }

    private function estimateReadingTime(string $content): int
    {
        $plain = trim(strip_tags($content));
        $words = str_word_count($plain);
        return max(1, (int) ceil($words / 200));
    }

    private function syncResources(KnowledgeArticle $article, array $resources): void
    {
        $article->resources()->delete();
        $cleaned = [];

        foreach ($resources as $resource) {
            $label = trim((string) ($resource['label'] ?? ''));
            $url = trim((string) ($resource['url'] ?? ''));
            if ($label === '' && $url === '') {
                continue;
            }
            $cleaned[] = [
                'label' => $label !== '' ? $label : ($url !== '' ? $url : 'Resource'),
                'resource_type' => $resource['resource_type'] ?? 'file',
                'url' => $url,
                'file_type' => $resource['file_type'] ?? null,
                'is_downloadable' => (bool) ($resource['is_downloadable'] ?? true),
            ];
        }

        if (! empty($cleaned)) {
            $article->resources()->createMany($cleaned);
        }
    }

    private function syncRelations(KnowledgeArticle $article, string $type, array $relationIds): void
    {
        KnowledgeArticleRelation::query()
            ->where('knowledge_article_id', $article->id)
            ->where('relation_type', $type)
            ->delete();

        $order = 1;
        foreach (array_unique($relationIds) as $relatedId) {
            if ((int) $relatedId === $article->id) {
                continue;
            }
            KnowledgeArticleRelation::create([
                'knowledge_article_id' => $article->id,
                'related_article_id' => $relatedId,
                'relation_type' => $type,
                'sort_order' => $order,
            ]);
            $order += 1;
        }
    }

    private function relationIdsFor(KnowledgeArticle $article, string $type): array
    {
        return $article->relations()
            ->where('relation_type', $type)
            ->orderBy('sort_order')
            ->pluck('related_article_id')
            ->all();
    }

    private function buildSearchSuggestions(string $search): array
    {
        $like = '%' . $search . '%';
        $articleSuggestions = KnowledgeArticle::query()
            ->where('title', 'like', $like)
            ->limit(5)
            ->pluck('title')
            ->all();
        $tagSuggestions = KnowledgeTag::query()
            ->where('name', 'like', $like)
            ->limit(5)
            ->pluck('name')
            ->all();

        return array_values(array_unique(array_merge($articleSuggestions, $tagSuggestions)));
    }

    private function buildTagSuggestions(): array
    {
        $content = strtolower($this->form['content'] ?? '');
        $title = strtolower($this->form['title'] ?? '');
        $tags = KnowledgeTag::all();
        $suggestions = [];

        foreach ($tags as $tag) {
            $needle = strtolower($tag->name);
            if ($needle === '') {
                continue;
            }
            if (str_contains($content, $needle) || str_contains($title, $needle)) {
                $suggestions[] = $tag->id;
            }
        }

        return $suggestions;
    }

    private function closestSuggestion(string $search, array $options): ?string
    {
        $closest = null;
        $shortest = null;
        $search = strtolower($search);

        foreach ($options as $option) {
            $distance = levenshtein($search, strtolower($option));
            if ($shortest === null || $distance < $shortest) {
                $shortest = $distance;
                $closest = $option;
            }
        }

        if ($shortest !== null && $shortest <= 4) {
            return $closest;
        }

        return null;
    }

    private function applySearchTerms(Builder $query, string $search): void
    {
        $groups = $this->parseSearchGroups($search);
        $query->where(function ($builder) use ($groups) {
            foreach ($groups as $group) {
                $builder->orWhere(function ($sub) use ($group) {
                    foreach ($group as $term) {
                        $like = '%' . $term . '%';
                        $sub->where(function ($nested) use ($like) {
                            $nested->where('title', 'like', $like)
                                ->orWhere('summary', 'like', $like)
                                ->orWhere('content', 'like', $like);
                        });
                    }
                });
            }
        });
    }

    private function parseSearchGroups(string $search): array
    {
        $search = trim(preg_replace('/\s+/', ' ', $search));
        $orGroups = preg_split('/\s+OR\s+/i', $search);
        $groups = [];

        foreach ($orGroups as $group) {
            preg_match_all('/"([^"]+)"|\S+/', $group, $matches);
            $terms = [];
            foreach ($matches[0] as $index => $token) {
                $terms[] = $matches[1][$index] !== '' ? $matches[1][$index] : $token;
            }
            $terms = array_filter($terms, fn ($term) => $term !== 'AND');
            if (! empty($terms)) {
                $groups[] = $terms;
            }
        }

        return $groups;
    }

    private function extractSearchTerms(string $search): array
    {
        $groups = $this->parseSearchGroups($search);
        return array_values(array_unique(Arr::flatten($groups)));
    }

    private function logSearchIfNeeded(int $resultCount): void
    {
        $search = trim($this->search);
        if ($search === '') {
            return;
        }

        $filters = [
            'category_id' => $this->categoryFilter,
            'tags' => $this->tagFilters,
            'content_type' => $this->contentTypeFilter,
            'author' => $this->authorFilter,
            'language' => $this->languageFilter,
            'visibility' => $this->visibilityFilter,
            'status' => $this->statusFilter,
            'date_from' => $this->dateFrom,
            'date_to' => $this->dateTo,
        ];

        $hash = md5($search . json_encode($filters) . $resultCount);
        if ($hash === $this->lastLoggedHash) {
            return;
        }

        $log = KnowledgeSearchLog::create([
            'user_id' => auth()->id(),
            'query' => $search,
            'filters' => $filters,
            'results_count' => $resultCount,
        ]);

        $this->lastLoggedHash = $hash;
        $this->lastLoggedSearchId = $log->id;
    }

    private function descendantCategoryIds(int $categoryId): array
    {
        $ids = [$categoryId];
        $queue = [$categoryId];

        while (! empty($queue)) {
            $current = array_shift($queue);
            $children = KnowledgeCategory::where('parent_id', $current)->pluck('id')->all();
            foreach ($children as $child) {
                if (! in_array($child, $ids, true)) {
                    $ids[] = $child;
                    $queue[] = $child;
                }
            }
        }

        return $ids;
    }

    private function flattenCategories($categories, string $prefix = ''): array
    {
        $result = [];
        foreach ($categories as $category) {
            $result[] = [
                'id' => $category->id,
                'label' => $prefix . $category->name,
            ];
            if ($category->children->isNotEmpty()) {
                $result = array_merge(
                    $result,
                    $this->flattenCategories($category->children, $prefix . '— ')
                );
            }
        }

        return $result;
    }

    private function refreshTagUsageCounts(array $tagIds): void
    {
        if (empty($tagIds)) {
            return;
        }

        KnowledgeTag::whereIn('id', $tagIds)->get()->each(function ($tag) {
            $tag->update(['usage_count' => $tag->articles()->count()]);
        });
    }
}
