<div>
    {{-- Hero Section --}}
    <div class="relative bg-gradient-to-br from-indigo-600 to-purple-700 text-white overflow-hidden">
        <div class="absolute inset-0 bg-black/10"></div>
        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-24 text-center">
            <h1 class="text-4xl font-bold tracking-tight mb-6">How can we help you?</h1>
            <p class="text-indigo-100 text-lg mb-8 max-w-2xl mx-auto">
                Search our knowledge base for guides, troubleshooting tips, and answers to common questions.
            </p>

            <div class="max-w-2xl mx-auto relative">
                <form wire:submit.prevent="search">
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <svg class="h-6 w-6 text-gray-400" fill="none" data-slot="icon" viewBox="0 0 24 24"
                                stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                            </svg>
                        </div>
                        <input wire:model="search" type="search"
                            class="block w-full rounded-2xl border-0 py-4 pl-12 pr-4 text-gray-900 shadow-xl ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-500 sm:text-lg"
                            placeholder="Search for articles (e.g. 'reset password')">
                        <button type="submit"
                            class="absolute right-2 top-2 p-2 bg-indigo-600 rounded-xl text-white hover:bg-indigo-500 transition-colors">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                            </svg>
                        </button>
                    </div>
                </form>
            </div>

            @if($popularArticles->isNotEmpty())
                <div class="mt-6 flex flex-wrap justify-center gap-2 text-sm text-indigo-100">
                    <span>Popular:</span>
                    @foreach($popularArticles->take(3) as $article)
                        <a href="{{ route('knowledge-base.show', $article) }}"
                            class="underline hover:text-white transition-colors">{{ $article->title }}</a>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 -mt-12 relative z-10 pb-16">

        {{-- Featured Cards --}}
        @if($featuredArticles->isNotEmpty())
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-12">
                @foreach($featuredArticles as $article)
                    <a href="{{ route('knowledge-base.show', $article) }}"
                        class="group bg-white rounded-xl shadow-lg border border-gray-100 p-6 transition-all hover:-translate-y-1 hover:shadow-xl">
                        <div class="flex items-start justify-between mb-4">
                            <span
                                class="inline-flex items-center rounded-md bg-indigo-50 px-2 py-1 text-xs font-medium text-indigo-700 ring-1 ring-inset ring-indigo-700/10">Featured</span>
                            @if($article->category)
                                <span class="text-xs text-gray-400">{{ $article->category->name }}</span>
                            @endif
                        </div>
                        <h3
                            class="text-lg font-semibold text-gray-900 group-hover:text-indigo-600 transition-colors mb-2 line-clamp-2">
                            {{ $article->title }}
                        </h3>
                        <p class="text-sm text-gray-500 line-clamp-3 mb-4">
                            {{ $article->summary }}
                        </p>
                        <div class="flex items-center text-xs text-gray-400 gap-3">
                            <span class="flex items-center gap-1">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                {{ $article->reading_time_minutes }} min
                            </span>
                            <span class="flex items-center gap-1">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                                {{ number_format($article->view_count) }}
                            </span>
                        </div>
                    </a>
                @endforeach
            </div>
        @endif

        {{-- Browse by Category --}}
        <h2 class="text-2xl font-bold text-gray-900 mb-6">Browse by Topic</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-16">
            @foreach($categories as $category)
                <a href="{{ route('knowledge-base.search', ['category' => $category->id]) }}"
                    class="group flex flex-col items-center text-center p-6 bg-white rounded-xl border border-gray-100 shadow-sm hover:shadow-md hover:border-indigo-100 transition-all">
                    <div
                        class="h-12 w-12 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
                        {{-- Icon Placeholder based on name or DB field --}}
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M2.25 12.75V12A2.25 2.25 0 014.5 9.75h15A2.25 2.25 0 0121.75 12v.75m-8.69-6.41l5.4-5.412A2.25 2.25 0 0016.81 0H5.25A2.25 2.25 0 003 2.25v2.25a2.25 2.25 0 002.25 2.25h12.75a2.25 2.25 0 001.65-.75M8.25 18v3m3.75-3v3m3.75-3v3M8.25 21h7.5" />
                        </svg>
                    </div>
                    <h3 class="font-semibold text-gray-900 group-hover:text-indigo-600">{{ $category->name }}</h3>
                    <p class="text-sm text-gray-500 mt-2">{{ $category->articles_count }} articles</p>
                </a>
            @endforeach
            <a href="{{ route('knowledge-base.search') }}"
                class="group flex flex-col items-center text-center p-6 bg-gray-50 rounded-xl border border-gray-100 border-dashed hover:border-gray-300 transition-all">
                <div class="h-12 w-12 rounded-lg bg-gray-100 text-gray-400 flex items-center justify-center mb-4">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                    </svg>
                </div>
                <h3 class="font-semibold text-gray-900">View All Articles</h3>
                <p class="text-sm text-gray-500 mt-2">Browse entire library</p>
            </a>
        </div>

        {{-- Contact / Support Banner --}}
        <div class="rounded-2xl bg-gray-900 overflow-hidden shadow-xl">
            <div class="flex flex-col md:flex-row items-center">
                <div class="p-8 md:p-12 md:w-2/3">
                    <h2 class="text-2xl font-bold text-white mb-4">Still can't find what you're looking for?</h2>
                    <p class="text-gray-400 mb-6">Our support team is always ready to help you with any questions or
                        issues you might have.</p>
                    <div class="flex flex-wrap gap-4">
                        <a href="#"
                            class="rounded-lg bg-indigo-600 px-5 py-3 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">Contact
                            Support</a>
                        <a href="#"
                            class="rounded-lg bg-white/10 px-5 py-3 text-sm font-semibold text-white hover:bg-white/20">Submit
                            a Ticket</a>
                    </div>
                </div>
                <div class="hidden md:block md:w-1/3 h-full relative">
                    <svg class="absolute inset-0 h-full w-full text-gray-800" fill="currentColor" viewBox="0 0 100 100"
                        preserveAspectRatio="none" aria-hidden="true">
                        <polygon points="0,0 100,0 100,100 0,100" fill="currentColor" />
                    </svg>
                    <!-- Optional Decorative Image/Graphic could go here -->
                </div>
            </div>
        </div>

    </div>
</div>