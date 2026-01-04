<?php

namespace App\Livewire\KnowledgeBase;

use App\Models\KnowledgeArticle;
use App\Models\KnowledgeCategory;
use App\Models\KnowledgeTag;
use Livewire\Component;

class Home extends Component
{
    public string $search = '';

    public function mount()
    {
        // Permission check can be loose here as it's a public landing (with visibility scopes)
    }

    public function search()
    {
        if (trim($this->search) === '') {
            return;
        }
        return redirect()->route('knowledge-base.search', ['q' => $this->search]);
    }

    public function render()
    {
        $categories = KnowledgeCategory::whereNull('parent_id')
            ->where('is_active', true)
            ->withCount([
                'articles' => function ($query) {
                    $query->visibleTo(auth()->user());
                }
            ])
            ->orderBy('sort_order')
            ->get();

        $featuredArticles = KnowledgeArticle::visibleTo(auth()->user())
            ->where('is_featured', true)
            ->latest('featured_at')
            ->take(6)
            ->get();

        $popularArticles = KnowledgeArticle::visibleTo(auth()->user())
            ->orderByDesc('view_count')
            ->take(5)
            ->get();

        return view('livewire.knowledge-base.home', [
            'categories' => $categories,
            'featuredArticles' => $featuredArticles,
            'popularArticles' => $popularArticles,
        ])->title('Knowledge Base');
    }
}
