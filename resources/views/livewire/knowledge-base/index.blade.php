@php
    use App\Support\RoleCatalog;
    use Illuminate\Support\Str;
@endphp

<div class="py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900">Knowledge Base</h1>
                <p class="text-sm text-gray-500">Self-service guides, troubleshooting, and product documentation.</p>
            </div>
            <div class="flex items-center gap-2">
                @if ($canManage)
                    <button class="px-4 py-2 bg-indigo-600 text-white rounded-md" wire:click="startCreate">New Article</button>
                @else
                    <button class="px-4 py-2 border border-gray-300 rounded-md" wire:click="$toggle('showSubmissionForm')">Submit Article</button>
                @endif
            </div>
        </div>

        @if (session('status'))
            <div class="rounded-md bg-green-50 p-3 text-sm text-green-700">
                {{ session('status') }}
            </div>
        @endif

        <div class="bg-white shadow-sm rounded-lg border border-gray-100 p-6 space-y-4">
            <div class="grid grid-cols-1 lg:grid-cols-6 gap-4">
                <div class="lg:col-span-3">
                    <label class="text-xs text-gray-500">Search</label>
                    <input wire:model.debounce.300ms="search" class="mt-1 w-full rounded-md border-gray-300" placeholder="Search articles, error codes, or topics" />
                    @if (! empty($searchSuggestions))
                        <div class="mt-2 flex flex-wrap gap-2 text-xs">
                            @foreach ($searchSuggestions as $suggestion)
                                <button type="button" class="px-2 py-1 bg-gray-100 rounded-full text-gray-600" wire:click="$set('search', @js($suggestion))">
                                    {{ $suggestion }}
                                </button>
                            @endforeach
                        </div>
                    @endif
                    <p class="mt-2 text-xs text-gray-400">Tips: Use quotes for exact matches and AND/OR to combine terms.</p>
                    @if ($didYouMean)
                        <p class="mt-1 text-xs text-indigo-600">Did you mean "{{ $didYouMean }}"?</p>
                    @endif
                </div>
                <div>
                    <label class="text-xs text-gray-500">Category</label>
                    <select wire:model="categoryFilter" class="mt-1 w-full rounded-md border-gray-300">
                        <option value="">All categories</option>
                        @foreach ($categoryOptions as $option)
                            <option value="{{ $option['id'] }}">{{ $option['label'] }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="text-xs text-gray-500">Content type</label>
                    <select wire:model="contentTypeFilter" class="mt-1 w-full rounded-md border-gray-300">
                        <option value="">All types</option>
                        @foreach ($contentTypeOptions as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="text-xs text-gray-500">Tags</label>
                    <select wire:model="tagFilters" multiple size="4" class="mt-1 w-full rounded-md border-gray-300">
                        @foreach ($tags as $tag)
                            <option value="{{ $tag->id }}">{{ $tag->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="text-xs text-gray-500">Author</label>
                    <select wire:model="authorFilter" class="mt-1 w-full rounded-md border-gray-300">
                        <option value="">All authors</option>
                        @foreach ($authors as $author)
                            <option value="{{ $author->id }}">{{ $author->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="text-xs text-gray-500">Language</label>
                    <select wire:model="languageFilter" class="mt-1 w-full rounded-md border-gray-300">
                        <option value="">All languages</option>
                        @foreach ($languageOptions as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="text-xs text-gray-500">Sort</label>
                    <select wire:model="sort" class="mt-1 w-full rounded-md border-gray-300">
                        @foreach ($sortOptions as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                @if ($canManage)
                    <div>
                        <label class="text-xs text-gray-500">Visibility</label>
                        <select wire:model="visibilityFilter" class="mt-1 w-full rounded-md border-gray-300">
                            <option value="">All visibility</option>
                            @foreach ($visibilityOptions as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="text-xs text-gray-500">Status</label>
                        <select wire:model="statusFilter" class="mt-1 w-full rounded-md border-gray-300">
                            @foreach ($statusOptions as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif
                <div>
                    <label class="text-xs text-gray-500">Updated from</label>
                    <input type="date" wire:model="dateFrom" class="mt-1 w-full rounded-md border-gray-300" />
                </div>
                <div>
                    <label class="text-xs text-gray-500">Updated to</label>
                    <input type="date" wire:model="dateTo" class="mt-1 w-full rounded-md border-gray-300" />
                </div>
            </div>
            <div class="flex flex-wrap items-center justify-between gap-2 text-xs text-gray-500">
                <button type="button" class="text-indigo-600" wire:click="clearFilters">Clear filters</button>
                <span>{{ $articles->total() }} results</span>
            </div>
        </div>

        @if ($featuredArticles->isNotEmpty())
            <div class="bg-white shadow-sm rounded-lg border border-gray-100 p-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-semibold text-gray-900">Featured articles</h2>
                    <span class="text-xs text-gray-500">Curated by the team</span>
                </div>
                <div class="flex gap-4 overflow-x-auto pb-2">
                    @foreach ($featuredArticles as $article)
                        <a href="{{ route('knowledge-base.show', $article) }}" class="min-w-[240px] rounded-lg border border-gray-200 p-4 hover:border-indigo-200">
                            <p class="text-xs text-gray-500">{{ $article->category?->name ?? $article->category ?? 'General' }}</p>
                            <p class="text-sm font-semibold text-gray-900 mt-1">{{ $article->title }}</p>
                            <p class="text-xs text-gray-500 mt-2">{{ $article->reading_time_minutes ?? 3 }} min read</p>
                        </a>
                    @endforeach
                </div>
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2 space-y-6">
                <div class="bg-white shadow-sm rounded-lg border border-gray-100">
                    <div class="p-4 border-b border-gray-100 flex flex-wrap items-center justify-between gap-3">
                        <div>
                            <h2 class="text-lg font-semibold text-gray-900">Browse articles</h2>
                            <p class="text-xs text-gray-500">{{ $articles->total() }} articles found</p>
                        </div>
                        @if ($canManage)
                            <div class="flex flex-wrap items-center gap-2">
                                <select wire:model="bulkAction" class="rounded-md border-gray-300 text-xs">
                                    <option value="">Bulk action</option>
                                    <option value="publish">Publish</option>
                                    <option value="archive">Archive</option>
                                    <option value="feature">Feature</option>
                                    <option value="promote">Promote</option>
                                    <option value="categorize">Assign category</option>
                                </select>
                                @if ($bulkAction === 'categorize')
                                    <select wire:model="bulkCategoryId" class="rounded-md border-gray-300 text-xs">
                                        <option value="">Select category</option>
                                        @foreach ($categoryOptions as $option)
                                            <option value="{{ $option['id'] }}">{{ $option['label'] }}</option>
                                        @endforeach
                                    </select>
                                @endif
                                <button class="px-3 py-1 bg-indigo-600 text-white rounded-md text-xs" wire:click="applyBulkAction">Apply</button>
                            </div>
                        @endif
                    </div>
                    <div class="divide-y divide-gray-200">
                        @forelse ($articles as $article)
                            <div class="p-5" wire:key="article-{{ $article->id }}">
                                <div class="flex flex-wrap items-start justify-between gap-4">
                                    <div class="flex items-start gap-3">
                                        @if ($canManage)
                                            <input type="checkbox" wire:model="selectedArticles" value="{{ $article->id }}" class="mt-1 rounded border-gray-300" />
                                        @endif
                                        <div class="space-y-2">
                                            <div class="flex flex-wrap items-center gap-2">
                                                <a href="{{ route('knowledge-base.show', $article) }}{{ $lastLoggedSearchId ? '?search_log_id=' . $lastLoggedSearchId : '' }}" class="text-base font-semibold text-gray-900 hover:text-indigo-600">
                                                    {!! $this->highlight($article->title) !!}
                                                </a>
                                                <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs {{ $article->status === 'published' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700' }}">
                                                    {{ ucfirst($article->status ?? 'draft') }}
                                                </span>
                                                @if ($article->is_featured)
                                                    <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs bg-indigo-50 text-indigo-600">Featured</span>
                                                @endif
                                            </div>
                                            <div class="flex flex-wrap items-center gap-3 text-xs text-gray-500">
                                                <span>Category: {{ $article->category?->name ?? $article->category ?? 'Uncategorized' }}</span>
                                                <span>Updated: {{ $article->updated_at?->format('M d, Y') ?? 'Unknown' }}</span>
                                                <span>Author: {{ $article->author_name ?? $article->createdBy?->name ?? 'System' }}</span>
                                                <span>{{ $article->reading_time_minutes ?? 3 }} min read</span>
                                                <span>{{ strtoupper($article->language ?? 'EN') }}</span>
                                            </div>
                                            <p class="text-sm text-gray-600">
                                                {!! $this->highlight(Str::limit($article->summary ?: strip_tags($article->content), 160)) !!}
                                            </p>
                                            <div class="flex flex-wrap gap-2 text-xs">
                                                @foreach ($article->tags as $tag)
                                                    <span class="px-2 py-0.5 rounded-full bg-gray-100 text-gray-600">#{{ $tag->name }}</span>
                                                @endforeach
                                            </div>
                                            <div class="flex flex-wrap gap-4 text-xs text-gray-500">
                                                <span>{{ $article->view_count }} views</span>
                                                <span>{{ number_format($article->rating_avg, 1) }} rating</span>
                                                <span>{{ $article->comment_count }} comments</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <a class="px-3 py-1 text-xs border border-gray-300 rounded-md" href="{{ route('knowledge-base.show', $article) }}{{ $lastLoggedSearchId ? '?search_log_id=' . $lastLoggedSearchId : '' }}">View</a>
                                        @if ($canManage)
                                            <button class="px-3 py-1 text-xs border border-gray-300 rounded-md" wire:click="editArticle({{ $article->id }})">Edit</button>
                                            @if ($article->status === 'review')
                                                <button class="px-3 py-1 text-xs border border-gray-300 rounded-md" wire:click="approveArticle({{ $article->id }})">Approve</button>
                                                <button class="px-3 py-1 text-xs border border-gray-300 rounded-md" wire:click="rejectArticle({{ $article->id }})">Request changes</button>
                                            @endif
                                            <button class="px-3 py-1 text-xs border border-gray-300 rounded-md" wire:click="archiveArticle({{ $article->id }})">Archive</button>
                                            <button class="px-3 py-1 text-xs border border-gray-300 rounded-md" wire:click="featureArticle({{ $article->id }})">Feature</button>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="p-6 text-sm text-gray-500">No articles match the current filters.</div>
                        @endforelse
                    </div>
                    <div class="p-4">{{ $articles->links() }}</div>
                </div>
            </div>

            <div class="space-y-6">
                <div class="bg-white shadow-sm rounded-lg border border-gray-100 p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Browse by category</h2>
                    <div class="space-y-2 text-sm">
                        @foreach ($categories as $category)
                            @include('livewire.knowledge-base.partials.category-tree', ['category' => $category, 'level' => 0])
                        @endforeach
                    </div>
                </div>

                <div class="bg-white shadow-sm rounded-lg border border-gray-100 p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Popular tags</h2>
                    <div class="flex flex-wrap gap-2 text-xs">
                        @foreach ($popularTags as $tag)
                            <button type="button" class="px-2 py-1 rounded-full border border-gray-200 text-gray-600" wire:click="$set('tagFilters', [{{ $tag->id }}])">
                                #{{ $tag->name }}
                            </button>
                        @endforeach
                    </div>
                </div>

                <div class="bg-white shadow-sm rounded-lg border border-gray-100 p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Staff picks</h2>
                    <div class="space-y-3 text-sm">
                        @foreach ($staffPicks as $pick)
                            <a class="flex items-center justify-between" href="{{ route('knowledge-base.show', $pick) }}">
                                <span>{{ $pick->title }}</span>
                                <span class="text-xs text-gray-400">{{ $pick->updated_at?->diffForHumans() }}</span>
                            </a>
                        @endforeach
                        @if ($staffPicks->isEmpty())
                            <p class="text-xs text-gray-500">No staff picks yet.</p>
                        @endif
                    </div>
                </div>

                <div class="bg-white shadow-sm rounded-lg border border-gray-100 p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Recently updated</h2>
                    <div class="space-y-3 text-sm">
                        @foreach ($recentArticles as $recent)
                            <a class="flex items-center justify-between" href="{{ route('knowledge-base.show', $recent) }}">
                                <span>{{ $recent->title }}</span>
                                <span class="text-xs text-gray-400">{{ $recent->updated_at?->format('M d') }}</span>
                            </a>
                        @endforeach
                    </div>
                </div>

                <div class="bg-white shadow-sm rounded-lg border border-gray-100 p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Trending articles</h2>
                    <div class="space-y-3 text-sm">
                        @foreach ($trendingArticles as $trending)
                            <a class="flex items-center justify-between" href="{{ route('knowledge-base.show', $trending) }}">
                                <span>{{ $trending->title }}</span>
                                <span class="text-xs text-gray-400">{{ $trending->view_count }} views</span>
                            </a>
                        @endforeach
                    </div>
                </div>

                @if ($canManage)
                    <div class="bg-white shadow-sm rounded-lg border border-gray-100 p-6">
                        <h2 class="text-lg font-semibold text-gray-900 mb-4">Content health</h2>
                        <div class="grid grid-cols-2 gap-4 text-sm">
                            <div>
                                <p class="text-xs text-gray-500 uppercase">Total</p>
                                <p class="text-lg font-semibold text-gray-900">{{ $analytics['total_articles'] }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 uppercase">Published</p>
                                <p class="text-lg font-semibold text-gray-900">{{ $analytics['published_articles'] }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 uppercase">Drafts</p>
                                <p class="text-lg font-semibold text-gray-900">{{ $analytics['draft_articles'] }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 uppercase">Needs review</p>
                                <p class="text-lg font-semibold text-gray-900">{{ $analytics['review_articles'] }}</p>
                            </div>
                        </div>
                        <div class="mt-4 text-xs text-gray-500">{{ $analytics['stale_articles'] }} articles are older than 12 months.</div>
                    </div>

                    <div class="bg-white shadow-sm rounded-lg border border-gray-100 p-6">
                        <h2 class="text-lg font-semibold text-gray-900 mb-4">Search analytics</h2>
                        <div class="space-y-3 text-sm">
                            <div>
                                <p class="text-xs text-gray-500 uppercase">Top searches</p>
                                @foreach ($topSearches as $search)
                                    <div class="flex items-center justify-between">
                                        <span>{{ $search->query }}</span>
                                        <span class="text-xs text-gray-400">{{ $search->total }}</span>
                                    </div>
                                @endforeach
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 uppercase">No results</p>
                                @foreach ($zeroResultSearches as $search)
                                    <div class="flex items-center justify-between">
                                        <span>{{ $search->query }}</span>
                                        <span class="text-xs text-gray-400">{{ $search->total }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="bg-white shadow-sm rounded-lg border border-gray-100 p-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-semibold text-gray-900">Video library</h2>
                    <span class="text-xs text-gray-500">Tutorials and walkthroughs</span>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    @forelse ($videoResources as $video)
                        <div class="border border-gray-200 rounded-lg p-3">
                            <p class="text-sm font-semibold text-gray-900">{{ $video->label }}</p>
                            <p class="text-xs text-gray-500">{{ $video->article?->title ?? 'General' }}</p>
                            <a class="mt-2 inline-flex text-xs text-indigo-600" href="{{ $video->url }}" target="_blank" rel="noreferrer">Watch</a>
                        </div>
                    @empty
                        <p class="text-sm text-gray-500">No videos uploaded yet.</p>
                    @endforelse
                </div>
            </div>

            <div class="bg-white shadow-sm rounded-lg border border-gray-100 p-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-semibold text-gray-900">Community contributions</h2>
                    <span class="text-xs text-gray-500">Top authors</span>
                </div>
                <div class="space-y-3 text-sm">
                    @foreach ($contributors as $contributor)
                        <div class="flex items-center justify-between">
                            <span>{{ $contributor->createdBy?->name ?? 'Unknown' }}</span>
                            <span class="text-xs text-gray-400">{{ $contributor->total }} articles</span>
                        </div>
                    @endforeach
                </div>
                @if (! $canManage)
                    <div class="mt-4 text-xs text-gray-500">Submit a guide to earn contributor badges.</div>
                @endif
            </div>
        </div>

        @if ($showSubmissionForm && ! $canManage)
            <div class="bg-white shadow-sm rounded-lg border border-gray-100 p-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-semibold text-gray-900">Submit an article</h2>
                    <button class="text-sm text-gray-500" type="button" wire:click="$toggle('showSubmissionForm')">Close</button>
                </div>
                <form wire:submit.prevent="submitCommunityArticle" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="md:col-span-2">
                        <label class="text-xs text-gray-500">Title</label>
                        <input wire:model="submission.title" class="mt-1 w-full rounded-md border-gray-300" />
                        @error('submission.title') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="text-xs text-gray-500">Category</label>
                        <select wire:model="submission.category_id" class="mt-1 w-full rounded-md border-gray-300">
                            <option value="">Select category</option>
                            @foreach ($categoryOptions as $option)
                                <option value="{{ $option['id'] }}">{{ $option['label'] }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="text-xs text-gray-500">Tags (comma separated)</label>
                        <input wire:model="submission.tags" class="mt-1 w-full rounded-md border-gray-300" />
                    </div>
                    <div class="md:col-span-2">
                        <label class="text-xs text-gray-500">Summary</label>
                        <textarea wire:model="submission.summary" class="mt-1 w-full rounded-md border-gray-300" rows="2"></textarea>
                    </div>
                    <div class="md:col-span-2">
                        <label class="text-xs text-gray-500">Article content</label>
                        <textarea wire:model="submission.content" class="mt-1 w-full rounded-md border-gray-300" rows="4"></textarea>
                    </div>
                    <div class="md:col-span-2">
                        <button class="px-4 py-2 bg-indigo-600 text-white rounded-md">Submit for review</button>
                    </div>
                </form>
            </div>
        @endif

        @if ($canManage)
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div class="lg:col-span-2 space-y-6">
                    <div class="bg-white shadow-sm rounded-lg border border-gray-100 p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h2 class="text-lg font-semibold text-gray-900">Content studio</h2>
                            @if ($showForm)
                                <button class="text-sm text-gray-500" type="button" wire:click="cancelEdit">Close</button>
                            @endif
                        </div>

                        @if ($showForm)
                            <form wire:submit.prevent="saveArticle" class="space-y-4">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="text-xs text-gray-500">Title</label>
                                        <input wire:model="form.title" class="mt-1 w-full rounded-md border-gray-300" />
                                        @error('form.title') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
                                    </div>
                                    <div>
                                        <label class="text-xs text-gray-500">Content type</label>
                                        <select wire:model="form.content_type" class="mt-1 w-full rounded-md border-gray-300">
                                            @foreach ($contentTypeOptions as $value => $label)
                                                <option value="{{ $value }}">{{ $label }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <label class="text-xs text-gray-500">Category</label>
                                        <select wire:model="form.category_id" class="mt-1 w-full rounded-md border-gray-300">
                                            <option value="">Uncategorized</option>
                                            @foreach ($categoryOptions as $option)
                                                <option value="{{ $option['id'] }}">{{ $option['label'] }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <label class="text-xs text-gray-500">Visibility</label>
                                        <select wire:model="form.visibility" class="mt-1 w-full rounded-md border-gray-300">
                                            @foreach ($visibilityOptions as $value => $label)
                                                <option value="{{ $value }}">{{ $label }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <label class="text-xs text-gray-500">Status</label>
                                        <select wire:model="form.status" class="mt-1 w-full rounded-md border-gray-300">
                                            @foreach ($statusOptions as $value => $label)
                                                @if ($value !== 'all')
                                                    <option value="{{ $value }}">{{ $label }}</option>
                                                @endif
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <label class="text-xs text-gray-500">Language</label>
                                        <select wire:model="form.language" class="mt-1 w-full rounded-md border-gray-300">
                                            @foreach ($languageOptions as $value => $label)
                                                <option value="{{ $value }}">{{ $label }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <label class="text-xs text-gray-500">Translation status</label>
                                        <select wire:model="form.translation_status" class="mt-1 w-full rounded-md border-gray-300">
                                            @foreach ($translationStatusOptions as $value => $label)
                                                <option value="{{ $value }}">{{ $label }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <label class="text-xs text-gray-500">Translation of</label>
                                        <select wire:model="form.translation_of_id" class="mt-1 w-full rounded-md border-gray-300">
                                            <option value="">Original article</option>
                                            @foreach ($articleOptions as $articleOption)
                                                <option value="{{ $articleOption->id }}">{{ $articleOption->title }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <label class="text-xs text-gray-500">Author name</label>
                                        <input wire:model="form.author_name" class="mt-1 w-full rounded-md border-gray-300" />
                                    </div>
                                    <div>
                                        <label class="text-xs text-gray-500">Author title</label>
                                        <input wire:model="form.author_title" class="mt-1 w-full rounded-md border-gray-300" />
                                    </div>
                                    <div class="md:col-span-2">
                                        <label class="text-xs text-gray-500">Summary</label>
                                        <textarea wire:model="form.summary" class="mt-1 w-full rounded-md border-gray-300" rows="2"></textarea>
                                    </div>
                                </div>

                                <div>
                                    <label class="text-xs text-gray-500">Tags</label>
                                    <select wire:model="form.tags" multiple size="4" class="mt-1 w-full rounded-md border-gray-300">
                                        @foreach ($tags as $tag)
                                            <option value="{{ $tag->id }}">{{ $tag->name }}</option>
                                        @endforeach
                                    </select>
                                    @if (! empty($suggestedTags))
                                        <div class="mt-2 flex flex-wrap gap-2 text-xs text-gray-500">
                                            <span>Suggested:</span>
                                            @foreach ($tags->whereIn('id', $suggestedTags) as $tag)
                                                <button type="button" class="px-2 py-1 rounded-full bg-gray-100" wire:click="addSuggestedTag({{ $tag->id }})">
                                                    #{{ $tag->name }}
                                                </button>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>

                                <div wire:ignore class="border border-gray-200 rounded-lg p-4" x-data="{ content: @entangle('form.content').defer, insertHtml(html) { document.execCommand('insertHTML', false, html); this.sync(); }, sync() { this.content = this.$refs.editor.innerHTML; }, exec(cmd, value = null) { document.execCommand(cmd, false, value); this.sync(); }, insertHeading(level) { this.exec('formatBlock', 'H' + level); }, insertCallout(type) { const labels = { note: 'Note', tip: 'Tip', warning: 'Warning' }; this.insertHtml('<div class=\"kb-callout kb-callout-' + type + '\"><strong>' + labels[type] + ':</strong> Add your message.</div>'); }, insertCollapsible() { this.insertHtml('<details><summary>Section title</summary><p>Add details here.</p></details>'); }, insertTable() { this.insertHtml('<table class=\"kb-table\"><thead><tr><th>Column</th><th>Column</th></tr></thead><tbody><tr><td>Value</td><td>Value</td></tr></tbody></table>'); }, insertCode() { this.insertHtml('<pre><code class=\"language-plain\">// Code snippet</code></pre>'); }, insertImage() { const url = prompt('Image URL'); if (! url) return; const caption = prompt('Caption'); this.insertHtml('<figure><img src=\"' + url + '\" alt=\"\" /><figcaption>' + (caption || '') + '</figcaption></figure>'); }, insertVideo() { const url = prompt('Video URL'); if (! url) return; this.insertHtml('<div class=\"kb-embed\"><iframe src=\"' + url + '\" allowfullscreen></iframe></div>'); }, insertLink() { const url = prompt('Link URL'); if (! url) return; const text = prompt('Link text') || url; this.insertHtml('<a href=\"' + url + '\" target=\"_blank\" rel=\"noreferrer\">' + text + '</a>'); } }" x-init="$refs.editor.innerHTML = content" x-effect="if ($refs.editor && $refs.editor.innerHTML !== content) { $refs.editor.innerHTML = content || '' }">
                                    <div class="flex flex-wrap gap-2 mb-3 text-xs">
                                        <button type="button" class="px-2 py-1 border rounded" x-on:click="exec('bold')">Bold</button>
                                        <button type="button" class="px-2 py-1 border rounded" x-on:click="exec('italic')">Italic</button>
                                        <button type="button" class="px-2 py-1 border rounded" x-on:click="insertHeading(2)">H2</button>
                                        <button type="button" class="px-2 py-1 border rounded" x-on:click="insertHeading(3)">H3</button>
                                        <button type="button" class="px-2 py-1 border rounded" x-on:click="exec('insertUnorderedList')">List</button>
                                        <button type="button" class="px-2 py-1 border rounded" x-on:click="exec('insertOrderedList')">Numbered</button>
                                        <button type="button" class="px-2 py-1 border rounded" x-on:click="insertImage()">Image</button>
                                        <button type="button" class="px-2 py-1 border rounded" x-on:click="insertVideo()">Video</button>
                                        <button type="button" class="px-2 py-1 border rounded" x-on:click="insertCode()">Code</button>
                                        <button type="button" class="px-2 py-1 border rounded" x-on:click="insertTable()">Table</button>
                                        <button type="button" class="px-2 py-1 border rounded" x-on:click="insertCallout('note')">Note</button>
                                        <button type="button" class="px-2 py-1 border rounded" x-on:click="insertCallout('tip')">Tip</button>
                                        <button type="button" class="px-2 py-1 border rounded" x-on:click="insertCallout('warning')">Warning</button>
                                        <button type="button" class="px-2 py-1 border rounded" x-on:click="insertCollapsible()">Collapsible</button>
                                        <button type="button" class="px-2 py-1 border rounded" x-on:click="insertLink()">Link</button>
                                    </div>
                                    <div
                                        class="min-h-[200px] rounded-md border border-gray-300 p-3 text-sm text-gray-700"
                                        contenteditable
                                        x-ref="editor"
                                        x-on:input.debounce.300ms="sync()"
                                    ></div>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="text-xs text-gray-500">SEO title</label>
                                        <input wire:model="form.seo_title" class="mt-1 w-full rounded-md border-gray-300" />
                                    </div>
                                    <div>
                                        <label class="text-xs text-gray-500">SEO description</label>
                                        <input wire:model="form.seo_description" class="mt-1 w-full rounded-md border-gray-300" />
                                    </div>
                                    <div>
                                        <label class="text-xs text-gray-500">Review due</label>
                                        <input type="date" wire:model="form.review_due_at" class="mt-1 w-full rounded-md border-gray-300" />
                                    </div>
                                    <div>
                                        <label class="text-xs text-gray-500">Expires at</label>
                                        <input type="date" wire:model="form.expires_at" class="mt-1 w-full rounded-md border-gray-300" />
                                    </div>
                                    <div>
                                        <label class="text-xs text-gray-500">Schedule publish</label>
                                        <input type="datetime-local" wire:model="form.scheduled_for" class="mt-1 w-full rounded-md border-gray-300" />
                                    </div>
                                </div>

                                <div>
                                    <label class="text-xs text-gray-500">Resources</label>
                                    <div class="space-y-3 mt-2">
                                        @foreach ($form['resources'] as $index => $resource)
                                            <div class="grid grid-cols-1 md:grid-cols-5 gap-2" wire:key="resource-{{ $index }}">
                                                <input wire:model="form.resources.{{ $index }}.label" class="rounded-md border-gray-300" placeholder="Label" />
                                                <input wire:model="form.resources.{{ $index }}.url" class="rounded-md border-gray-300" placeholder="URL" />
                                                <select wire:model="form.resources.{{ $index }}.resource_type" class="rounded-md border-gray-300">
                                                    <option value="file">File</option>
                                                    <option value="pdf">PDF</option>
                                                    <option value="template">Template</option>
                                                    <option value="quick_reference">Quick reference</option>
                                                    <option value="video">Video</option>
                                                </select>
                                                <input wire:model="form.resources.{{ $index }}.file_type" class="rounded-md border-gray-300" placeholder="Type" />
                                                <div class="flex items-center gap-2">
                                                    <label class="inline-flex items-center gap-1 text-xs">
                                                        <input type="checkbox" wire:model="form.resources.{{ $index }}.is_downloadable" class="rounded border-gray-300" />
                                                        Downloadable
                                                    </label>
                                                    <button type="button" class="text-xs text-red-500" wire:click="removeResourceRow({{ $index }})">Remove</button>
                                                </div>
                                            </div>
                                        @endforeach
                                        <button type="button" class="px-3 py-1 text-xs border border-gray-300 rounded-md" wire:click="addResourceRow">Add resource</button>
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="text-xs text-gray-500">Related articles</label>
                                        <select wire:model="form.related" multiple size="4" class="mt-1 w-full rounded-md border-gray-300">
                                            @foreach ($articleOptions as $articleOption)
                                                <option value="{{ $articleOption->id }}">{{ $articleOption->title }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <label class="text-xs text-gray-500">See also</label>
                                        <select wire:model="form.see_also" multiple size="4" class="mt-1 w-full rounded-md border-gray-300">
                                            @foreach ($articleOptions as $articleOption)
                                                <option value="{{ $articleOption->id }}">{{ $articleOption->title }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <label class="text-xs text-gray-500">Prerequisites</label>
                                        <select wire:model="form.prerequisites" multiple size="4" class="mt-1 w-full rounded-md border-gray-300">
                                            @foreach ($articleOptions as $articleOption)
                                                <option value="{{ $articleOption->id }}">{{ $articleOption->title }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <label class="text-xs text-gray-500">Series order</label>
                                        <select wire:model="form.series" multiple size="4" class="mt-1 w-full rounded-md border-gray-300">
                                            @foreach ($articleOptions as $articleOption)
                                                <option value="{{ $articleOption->id }}">{{ $articleOption->title }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                    <label class="inline-flex items-center gap-2 text-sm">
                                        <input type="checkbox" wire:model="form.is_featured" class="rounded border-gray-300" />
                                        Featured
                                    </label>
                                    <label class="inline-flex items-center gap-2 text-sm">
                                        <input type="checkbox" wire:model="form.is_promoted" class="rounded border-gray-300" />
                                        Promoted
                                    </label>
                                    <label class="inline-flex items-center gap-2 text-sm">
                                        <input type="checkbox" wire:model="form.allow_comments" class="rounded border-gray-300" />
                                        Allow comments
                                    </label>
                                </div>

                                <div>
                                    <label class="text-xs text-gray-500">Role-based visibility (optional)</label>
                                    <select wire:model="form.visibility_roles" multiple size="5" class="mt-1 w-full rounded-md border-gray-300">
                                        @foreach (RoleCatalog::all() as $role)
                                            <option value="{{ $role }}">{{ RoleCatalog::label($role) }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="flex items-center gap-3">
                                    <button class="px-4 py-2 bg-indigo-600 text-white rounded-md">
                                        {{ $editingId ? 'Update Article' : 'Save Article' }}
                                    </button>
                                    @if ($editingId)
                                        <button type="button" class="px-4 py-2 border border-gray-300 rounded-md" wire:click="cancelEdit">Cancel</button>
                                    @endif
                                </div>
                            </form>
                        @else
                            <p class="text-sm text-gray-500">Select "New Article" to draft a new knowledge base entry.</p>
                        @endif
                    </div>

                    <div class="bg-white shadow-sm rounded-lg border border-gray-100 p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h2 class="text-lg font-semibold text-gray-900">Category management</h2>
                            <button class="text-sm text-gray-500" type="button" wire:click="$toggle('showCategoryForm')">{{ $showCategoryForm ? 'Close' : 'Add' }}</button>
                        </div>
                        @if ($showCategoryForm)
                            <form wire:submit.prevent="saveCategory" class="grid grid-cols-1 md:grid-cols-2 gap-3 mb-4">
                                <div>
                                    <label class="text-xs text-gray-500">Name</label>
                                    <input wire:model="categoryForm.name" class="mt-1 w-full rounded-md border-gray-300" />
                                </div>
                                <div>
                                    <label class="text-xs text-gray-500">Icon</label>
                                    <input wire:model="categoryForm.icon" class="mt-1 w-full rounded-md border-gray-300" placeholder="printer" />
                                </div>
                                <div>
                                    <label class="text-xs text-gray-500">Parent</label>
                                    <select wire:model="categoryForm.parent_id" class="mt-1 w-full rounded-md border-gray-300">
                                        <option value="">None</option>
                                        @foreach ($categoryOptions as $option)
                                            <option value="{{ $option['id'] }}">{{ $option['label'] }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="text-xs text-gray-500">Sort order</label>
                                    <input type="number" wire:model="categoryForm.sort_order" class="mt-1 w-full rounded-md border-gray-300" />
                                </div>
                                <div class="md:col-span-2">
                                    <label class="text-xs text-gray-500">Description</label>
                                    <textarea wire:model="categoryForm.description" class="mt-1 w-full rounded-md border-gray-300" rows="2"></textarea>
                                </div>
                                <div class="md:col-span-2">
                                    <label class="inline-flex items-center gap-2 text-sm">
                                        <input type="checkbox" wire:model="categoryForm.is_active" class="rounded border-gray-300" />
                                        Active
                                    </label>
                                </div>
                                <div class="md:col-span-2 flex items-center gap-2">
                                    <button class="px-3 py-2 bg-indigo-600 text-white rounded-md">Save</button>
                                    @if ($editingCategoryId)
                                        <button type="button" class="px-3 py-2 border border-gray-300 rounded-md" wire:click="resetCategoryForm">Cancel</button>
                                    @endif
                                </div>
                            </form>
                        @endif
                        <div class="space-y-2 text-sm">
                            @foreach ($categories as $category)
                                <div class="flex items-center justify-between">
                                    <span>{{ $category->name }}</span>
                                    <div class="flex items-center gap-2">
                                        <button class="text-xs text-gray-500" wire:click="editCategory({{ $category->id }})">Edit</button>
                                        <button class="text-xs text-red-500" wire:click="deleteCategory({{ $category->id }})">Delete</button>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="space-y-6">
                    <div class="bg-white shadow-sm rounded-lg border border-gray-100 p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h2 class="text-lg font-semibold text-gray-900">Tag management</h2>
                            <button class="text-sm text-gray-500" type="button" wire:click="$toggle('showTagForm')">{{ $showTagForm ? 'Close' : 'Add' }}</button>
                        </div>
                        @if ($showTagForm)
                            <form wire:submit.prevent="saveTag" class="grid grid-cols-1 gap-3 mb-4">
                                <div>
                                    <label class="text-xs text-gray-500">Name</label>
                                    <input wire:model="tagForm.name" class="mt-1 w-full rounded-md border-gray-300" />
                                </div>
                                <div>
                                    <label class="text-xs text-gray-500">Slug</label>
                                    <input wire:model="tagForm.slug" class="mt-1 w-full rounded-md border-gray-300" />
                                </div>
                                <button class="px-3 py-2 bg-indigo-600 text-white rounded-md">Save tag</button>
                            </form>
                        @endif
                        <div class="space-y-2 text-sm">
                            @foreach ($tags as $tag)
                                <div class="flex items-center justify-between">
                                    <span>#{{ $tag->name }}</span>
                                    <div class="flex items-center gap-2">
                                        <button class="text-xs text-gray-500" wire:click="editTag({{ $tag->id }})">Rename</button>
                                        <button class="text-xs text-red-500" wire:click="deleteTag({{ $tag->id }})">Delete</button>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <div class="mt-4 text-xs text-gray-500">
                            Merge tags: select a target below and click "Merge" next to a tag.
                        </div>
                        <select wire:model="mergeTargetTagId" class="mt-2 w-full rounded-md border-gray-300 text-sm">
                            <option value="">Select target tag</option>
                            @foreach ($tags as $tag)
                                <option value="{{ $tag->id }}">{{ $tag->name }}</option>
                            @endforeach
                        </select>
                        <div class="mt-2 space-y-2 text-sm">
                            @foreach ($tags as $tag)
                                <div class="flex items-center justify-between">
                                    <span>{{ $tag->name }}</span>
                                    <button class="text-xs text-indigo-600" wire:click="mergeTags({{ $tag->id }})">Merge</button>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="bg-white shadow-sm rounded-lg border border-gray-100 p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h2 class="text-lg font-semibold text-gray-900">Content templates</h2>
                            <button class="text-sm text-gray-500" type="button" wire:click="$toggle('showTemplateForm')">{{ $showTemplateForm ? 'Close' : 'Add' }}</button>
                        </div>
                        @if ($showTemplateForm)
                            <form wire:submit.prevent="saveTemplate" class="grid grid-cols-1 gap-3 mb-4">
                                <input wire:model="templateForm.name" class="rounded-md border-gray-300" placeholder="Template name" />
                                <select wire:model="templateForm.content_type" class="rounded-md border-gray-300">
                                    @foreach ($contentTypeOptions as $value => $label)
                                        <option value="{{ $value }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                                <input wire:model="templateForm.sections" class="rounded-md border-gray-300" placeholder="Sections (comma separated)" />
                                <textarea wire:model="templateForm.body" class="rounded-md border-gray-300" rows="3" placeholder="Template body"></textarea>
                                <button class="px-3 py-2 bg-indigo-600 text-white rounded-md">Save template</button>
                            </form>
                        @endif
                        <div class="space-y-2 text-sm">
                            @foreach ($templates as $template)
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="font-medium text-gray-900">{{ $template->name }}</p>
                                        <p class="text-xs text-gray-500">{{ $contentTypeOptions[$template->content_type] ?? $template->content_type }}</p>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <button class="text-xs text-gray-500" wire:click="editTemplate({{ $template->id }})">Edit</button>
                                        <button class="text-xs text-indigo-600" wire:click="applyTemplate({{ $template->id }})">Apply</button>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="bg-white shadow-sm rounded-lg border border-gray-100 p-6">
                        <h2 class="text-lg font-semibold text-gray-900 mb-4">Ticket-to-article pipeline</h2>
                        <p class="text-xs text-gray-500">Create articles from resolved tickets.</p>
                        <div class="mt-3 space-y-2 text-sm">
                            @foreach ($recentTickets as $ticket)
                                <div class="flex items-center justify-between">
                                    <span>#{{ $ticket->id }} {{ $ticket->subject }}</span>
                                    <button class="text-xs text-indigo-600" wire:click="startCreate">Draft</button>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>

    <style>
        .kb-callout {
            border-left: 4px solid #cbd5f5;
            background: #eef2ff;
            padding: 12px;
            border-radius: 8px;
            margin: 8px 0;
        }
        .kb-callout-tip {
            border-left-color: #a7f3d0;
            background: #ecfdf5;
        }
        .kb-callout-warning {
            border-left-color: #fcd34d;
            background: #fffbeb;
        }
        .kb-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
        }
        .kb-table th,
        .kb-table td {
            border: 1px solid #e5e7eb;
            padding: 6px;
            text-align: left;
            font-size: 0.85rem;
        }
        .kb-embed iframe {
            width: 100%;
            height: 220px;
            border: 0;
        }
    </style>
</div>
