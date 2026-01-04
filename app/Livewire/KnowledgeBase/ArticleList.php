<?php

namespace App\Livewire\KnowledgeBase;

use App\Models\KnowledgeArticle;
use App\Models\KnowledgeCategory;
use Livewire\Component;
use Livewire\WithPagination;

class ArticleList extends Component
{
    use WithPagination;

    public $q = '';
    public $category = null;
    public $sort = 'relevance';

    protected $queryString = [
        'q' => ['except' => ''],
        'category' => ['except' => null],
        'sort' => ['except' => 'relevance'],
    ];

    public function updatedQ()
    {
        $this->resetPage();
    }
    public function updatedCategory()
    {
        $this->resetPage();
    }

    public function render()
    {
        $query = KnowledgeArticle::visibleTo(auth()->user());

        if ($this->q) {
            $query->where(function ($sub) {
                $sub->where('title', 'like', '%' . $this->q . '%')
                    ->orWhere('summary', 'like', '%' . $this->q . '%')
                    ->orWhere('content', 'like', '%' . $this->q . '%');
            });
        }

        if ($this->category) {
            $query->where('category_id', $this->category);
        }

        if ($this->sort === 'recent') {
            $query->latest('published_at');
        } elseif ($this->sort === 'popular') {
            $query->orderByDesc('view_count');
        } else {
            // Default to relevance (if search) or recent
            if ($this->q) {
                // simple likeness matching isn't true relevance, but latest serves well enough for simple DB search fallback
                $query->latest();
            } else {
                $query->latest();
            }
        }

        $articles = $query->paginate(12);

        $categories = KnowledgeCategory::whereNull('parent_id')
            ->where('is_active', true)
            ->withCount(['articles' => function ($q) {
                $q->visibleTo(auth()->user()); }])
            ->get();

        return view('livewire.knowledge-base.article-list', [
            'articles' => $articles,
            'categories' => $categories,
        ])->title('Search Knowledge Base');
    }
}
