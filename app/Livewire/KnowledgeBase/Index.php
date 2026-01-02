<?php

namespace App\Livewire\KnowledgeBase;

use App\Models\KnowledgeArticle;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public bool $showCreate = false;
    public string $search = '';
    public string $categoryFilter = '';
    public array $new = [];

    protected $paginationTheme = 'tailwind';

    public function mount(): void
    {
        $this->resetNew();
    }

    public function resetNew(): void
    {
        $this->new = [
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

    public function createArticle(): void
    {
        $this->validate([
            'new.title' => ['required', 'string', 'max:255'],
            'new.category' => ['nullable', 'string', 'max:255'],
            'new.content' => ['required', 'string'],
            'new.is_published' => ['boolean'],
        ]);

        $title = $this->new['title'];
        $slug = Str::slug($title);

        KnowledgeArticle::create([
            'title' => $title,
            'slug' => $slug,
            'category' => $this->new['category'],
            'content' => $this->new['content'],
            'is_published' => $this->new['is_published'],
            'published_at' => $this->new['is_published'] ? now() : null,
            'created_by_user_id' => auth()->id(),
        ]);

        session()->flash('status', 'Article created.');
        $this->resetNew();
        $this->showCreate = false;
    }

    public function render()
    {
        $query = KnowledgeArticle::query();

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

        $articles = $query->latest()->paginate(10);
        $categories = KnowledgeArticle::whereNotNull('category')
            ->select('category')
            ->distinct()
            ->orderBy('category')
            ->pluck('category');

        return view('livewire.knowledge-base.index', [
            'articles' => $articles,
            'categories' => $categories,
        ]);
    }
}
