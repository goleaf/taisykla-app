<div class="py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900">Knowledge Base</h1>
                <p class="text-sm text-gray-500">Troubleshooting guides and best practices.</p>
            </div>
            @if ($canManage)
                <button class="px-4 py-2 bg-indigo-600 text-white rounded-md" wire:click="startCreate">New Article</button>
            @endif
        </div>

        @if (session('status'))
            <div class="rounded-md bg-green-50 p-3 text-sm text-green-700">
                {{ session('status') }}
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="{{ $canManage ? 'lg:col-span-2' : 'lg:col-span-3' }} space-y-6">
                <div class="bg-white shadow-sm rounded-lg p-4 border border-gray-100">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="md:col-span-2">
                            <label class="text-xs text-gray-500">Search</label>
                            <input wire:model.debounce.300ms="search" class="mt-1 w-full rounded-md border-gray-300" placeholder="Search articles" />
                        </div>
                        <div>
                            <label class="text-xs text-gray-500">Category</label>
                            <select wire:model="categoryFilter" class="mt-1 w-full rounded-md border-gray-300">
                                <option value="">All categories</option>
                                @foreach ($categories as $category)
                                    <option value="{{ $category }}">{{ $category }}</option>
                                @endforeach
                            </select>
                        </div>
                        @if ($canManage)
                            <div>
                                <label class="text-xs text-gray-500">Status</label>
                                <select wire:model="statusFilter" class="mt-1 w-full rounded-md border-gray-300">
                                    @foreach ($statusOptions as $value => $label)
                                        <option value="{{ $value }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                        @endif
                    </div>
                    <div class="mt-3 flex flex-wrap items-center justify-between gap-2 text-xs text-gray-500">
                        <button type="button" class="text-indigo-600" wire:click="clearFilters">Clear filters</button>
                        <span>{{ $articles->total() }} articles</span>
                    </div>
                </div>

                <div class="bg-white shadow-sm rounded-lg border border-gray-100">
                    <div class="divide-y divide-gray-200">
                        @forelse ($articles as $article)
                            <div class="p-5" wire:key="article-{{ $article->id }}">
                                <div class="flex flex-wrap items-start justify-between gap-4">
                                    <div class="space-y-2">
                                        <div class="flex flex-wrap items-center gap-2">
                                            <h3 class="text-base font-semibold text-gray-900">{{ $article->title }}</h3>
                                            <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs {{ $article->is_published ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700' }}">
                                                {{ $article->is_published ? 'Published' : 'Draft' }}
                                            </span>
                                        </div>
                                        <div class="flex flex-wrap items-center gap-3 text-xs text-gray-500">
                                            <span>Category: {{ $article->category ?? 'Uncategorized' }}</span>
                                            <span>Updated: {{ $article->updated_at?->format('M d, Y') ?? 'Unknown' }}</span>
                                            <span>Author: {{ $article->updatedBy?->name ?? $article->createdBy?->name ?? 'System' }}</span>
                                        </div>
                                        <p class="text-sm text-gray-600">
                                            {{ \Illuminate\Support\Str::limit($article->content, 180) }}
                                        </p>
                                    </div>
                                    @if ($canManage)
                                        <div class="flex items-center gap-2">
                                            <button class="px-3 py-1 text-xs border border-gray-300 rounded-md" wire:click="editArticle({{ $article->id }})">Edit</button>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <div class="p-6 text-sm text-gray-500">No articles match the current filters.</div>
                        @endforelse
                    </div>
                    <div class="p-4">{{ $articles->links() }}</div>
                </div>
            </div>

            @if ($canManage)
                <div class="space-y-6">
                    <div class="bg-white shadow-sm rounded-lg border border-gray-100 p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h2 class="text-lg font-semibold text-gray-900">
                                {{ $editingId ? 'Edit Article' : 'Create Article' }}
                            </h2>
                            @if ($showForm)
                                <button class="text-sm text-gray-500" type="button" wire:click="cancelEdit">Close</button>
                            @endif
                        </div>

                        @if ($showForm)
                            <form wire:submit.prevent="saveArticle" class="space-y-3">
                                <div>
                                    <label class="text-xs text-gray-500">Title</label>
                                    <input wire:model="form.title" class="mt-1 w-full rounded-md border-gray-300" />
                                    @error('form.title') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label class="text-xs text-gray-500">Category</label>
                                    <input wire:model="form.category" class="mt-1 w-full rounded-md border-gray-300" />
                                    @error('form.category') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label class="text-xs text-gray-500">Content</label>
                                    <textarea wire:model="form.content" class="mt-1 w-full rounded-md border-gray-300" rows="5"></textarea>
                                    @error('form.content') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
                                </div>
                                <label class="inline-flex items-center gap-2 text-sm">
                                    <input type="checkbox" wire:model="form.is_published" class="rounded border-gray-300" />
                                    Publish immediately
                                </label>
                                <div class="flex items-center gap-2">
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
                </div>
            @endif
        </div>
    </div>
</div>
