<div class="bg-gray-50 min-h-screen py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        {{-- Search Header --}}
        <div class="mb-8 flex flex-col md:flex-row md:items-center justify-between gap-4">
             <div class="relative flex-1 max-w-2xl">
                <input 
                    wire:model.debounce.300ms="q"
                    type="search" 
                    class="block w-full rounded-lg border-0 py-3 pl-11 pr-4 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6" 
                    placeholder="Search articles..."
                >
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
            </div>
            <div class="flex items-center gap-2">
                 <select wire:model.live="sort" class="rounded-lg border-0 py-2.5 pl-3 pr-8 text-gray-900 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600 sm:text-sm sm:leading-6">
                    <option value="relevance">Relevance</option>
                    <option value="recent">Most Recent</option>
                    <option value="popular">Most Popular</option>
                </select>
            </div>
        </div>

        <div class="flex flex-col lg:flex-row gap-8">
            {{-- Sidebar Filters --}}
            <div class="lg:w-64 flex-shrink-0 space-y-6">
                 <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-5">
                    <h3 class="font-semibold text-gray-900 mb-4">Categories</h3>
                    <div class="space-y-2">
                        <label class="flex items-center gap-2 text-sm text-gray-600 cursor-pointer">
                            <input type="radio" wire:model.live="category" value="" class="text-indigo-600 focus:ring-indigo-600 border-gray-300">
                            All Categories
                        </label>
                        @foreach($categories as $cat)
                             <label class="flex items-center gap-2 text-sm text-gray-600 cursor-pointer">
                                <input type="radio" wire:model.live="category" value="{{ $cat->id }}" class="text-indigo-600 focus:ring-indigo-600 border-gray-300">
                                {{ $cat->name }}
                                <span class="text-xs text-gray-400 ml-auto">{{ $cat->articles_count }}</span>
                            </label>
                        @endforeach
                    </div>
                 </div>
                 
                 <div class="bg-indigo-50 rounded-lg p-5 border border-indigo-100">
                    <h4 class="font-semibold text-indigo-900 mb-2 text-sm">Need help?</h4>
                    <p class="text-xs text-indigo-700 mb-3">If you can't find the answer you're looking for, our support team is here to help.</p>
                    <a href="#" class="block w-full text-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white hover:bg-indigo-500">Contact Support</a>
                 </div>
            </div>

            {{-- Results List --}}
            <div class="flex-1">
                 @if($articles->isEmpty())
                    <div class="text-center py-12 bg-white rounded-lg border border-gray-100 shadow-sm">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                             <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <h3 class="mt-2 text-sm font-semibold text-gray-900">No articles found</h3>
                        <p class="mt-1 text-sm text-gray-500">Try adjusting your search or filters.</p>
                    </div>
                 @else
                    <div class="space-y-4">
                        @foreach($articles as $article)
                            <a href="{{ route('knowledge-base.show', $article) }}" class="block bg-white rounded-lg border border-gray-100 shadow-sm p-6 transition hover:shadow-md hover:border-indigo-200">
                                <div class="flex items-start justify-between">
                                    <div>
                                        <div class="flex items-center gap-2 mb-1">
                                            @if($article->category)
                                                <span class="text-xs font-medium text-indigo-600 bg-indigo-50 px-2 py-0.5 rounded">{{ $article->category->name }}</span>
                                            @endif
                                            <span class="text-xs text-gray-400">{{ $article->published_at?->format('M d, Y') }}</span>
                                        </div>
                                        <h3 class="text-lg font-semibold text-gray-900 mb-2">{{ $article->title }}</h3>
                                        <p class="text-sm text-gray-500 line-clamp-2">{{ $article->summary ?? Str::limit(strip_tags($article->content), 150) }}</p>
                                    </div>
                                </div>
                                <div class="mt-4 flex items-center gap-4 text-xs text-gray-400">
                                    <span class="flex items-center gap-1">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        {{ $article->reading_time_minutes ?? 3 }} min read
                                    </span>
                                </div>
                            </a>
                        @endforeach
                    </div>
                    <div class="mt-6">
                        {{ $articles->links() }}
                    </div>
                 @endif
            </div>
        </div>
    </div>
</div>
