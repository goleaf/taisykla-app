<?php

namespace App\Livewire\KnowledgeBase;

use App\Models\KnowledgeArticle;
use App\Support\PermissionCatalog;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public bool $showForm = false;
    public string $search = '';
    public string $categoryFilter = '';
    public string $statusFilter = 'all';
    public array $form = [];
    public ?int $editingId = null;

    protected $paginationTheme = 'tailwind';

    public array $statusOptions = [
        'all' => 'All',
        'published' => 'Published',
        'draft' => 'Draft',
    ];

    public function mount(): void
    {
        abort_unless(auth()->user()?->can(PermissionCatalog::KNOWLEDGE_BASE_VIEW), 403);

        $this->resetForm();

        if (! $this->canManage) {
            $this->statusFilter = 'published';
        }
    }

    public function resetForm(): void
    {
        $this->form = [
            'title' => '',
            'category' => '',
            'content' => '',
            'is_published' => false,
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

    public function clearFilters(): void
    {
        $this->search = '';
        $this->categoryFilter = '';
        $this->statusFilter = $this->canManage ? 'all' : 'published';
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

        $article = KnowledgeArticle::findOrFail($articleId);
        $this->editingId = $article->id;
        $this->form = [
            'title' => $article->title,
            'category' => $article->category ?? '',
            'content' => $article->content,
            'is_published' => $article->is_published,
        ];
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
            'form.category' => ['nullable', 'string', 'max:255'],
            'form.content' => ['required', 'string'],
            'form.is_published' => ['boolean'],
        ];
    }

    public function saveArticle(): void
    {
        if (! $this->canManage) {
            return;
        }

        $this->validate();

        $userId = auth()->id();
        $title = trim($this->form['title']);
        $category = trim($this->form['category']);
        $isPublished = (bool) $this->form['is_published'];

        if ($this->editingId) {
            $article = KnowledgeArticle::findOrFail($this->editingId);
            $slug = $article->title === $title
                ? $article->slug
                : $this->uniqueSlug($title, $article->id);

            $article->update([
                'title' => $title,
                'slug' => $slug,
                'category' => $category !== '' ? $category : null,
                'content' => $this->form['content'],
                'is_published' => $isPublished,
                'published_at' => $isPublished ? ($article->published_at ?? now()) : null,
                'updated_by_user_id' => $userId,
            ]);

            session()->flash('status', 'Article updated.');
        } else {
            KnowledgeArticle::create([
                'title' => $title,
                'slug' => $this->uniqueSlug($title),
                'category' => $category !== '' ? $category : null,
                'content' => $this->form['content'],
                'is_published' => $isPublished,
                'published_at' => $isPublished ? now() : null,
                'created_by_user_id' => $userId,
                'updated_by_user_id' => $userId,
            ]);

            session()->flash('status', 'Article created.');
        }

        $this->resetForm();
        $this->editingId = null;
        $this->showForm = false;
        $this->resetPage();
    }

    public function render()
    {
        $query = KnowledgeArticle::query()->with(['createdBy', 'updatedBy']);

        if ($this->search !== '') {
            $search = '%' . $this->search . '%';
            $query->where(function ($builder) use ($search) {
                $builder->where('title', 'like', $search)
                    ->orWhere('content', 'like', $search);
            });
        }

        if ($this->categoryFilter !== '') {
            $query->where('category', $this->categoryFilter);
        }

        if (! $this->canManage) {
            $query->where('is_published', true);
        } elseif ($this->statusFilter === 'published') {
            $query->where('is_published', true);
        } elseif ($this->statusFilter === 'draft') {
            $query->where('is_published', false);
        }

        $articles = $query->orderByDesc('updated_at')->paginate(10);
        $categories = KnowledgeArticle::query()
            ->whereNotNull('category')
            ->when(! $this->canManage, function ($builder) {
                $builder->where('is_published', true);
            })
            ->select('category')
            ->distinct()
            ->orderBy('category')
            ->pluck('category');

        return view('livewire.knowledge-base.index', [
            'articles' => $articles,
            'categories' => $categories,
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
}
