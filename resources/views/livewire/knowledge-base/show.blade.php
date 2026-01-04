@php
    $trail = [];
    $current = $article->category;
    while ($current) {
        array_unshift($trail, $current);
        $current = $current->parent;
    }
    $shareUrl = request()->url();
@endphp

<div
    class="py-8"
    x-data="{ progress: 0, update() { const doc = document.documentElement; const total = doc.scrollHeight - doc.clientHeight; this.progress = total > 0 ? (window.scrollY / total) * 100 : 0; } }"
    x-init="update(); window.addEventListener('scroll', () => requestAnimationFrame(() => update()))"
>
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">
        <div class="h-1 bg-gray-100 rounded-full overflow-hidden">
            <div class="h-1 bg-indigo-500" :style="`width: ${progress}%`"></div>
        </div>

        <div class="flex flex-wrap items-center justify-between gap-4">
            <div></div>
            <div class="flex flex-wrap items-center gap-2">
                <button type="button" class="px-3 py-1 text-xs border border-gray-300 rounded-md" x-on:click.prevent="window.print(); $wire.trackDownload()">Download PDF</button>
                <button type="button" class="px-3 py-1 text-xs border border-gray-300 rounded-md" x-on:click.prevent="window.print()">Print</button>
                <div x-data="{ copied: false }" class="flex items-center gap-2">
                    <a href="mailto:?subject={{ urlencode($article->title) }}&body={{ urlencode($shareUrl) }}" class="px-3 py-1 text-xs border border-gray-300 rounded-md" wire:click="trackShare">Share email</a>
                    <button type="button" class="px-3 py-1 text-xs border border-gray-300 rounded-md" x-on:click.prevent="navigator.clipboard.writeText('{{ $shareUrl }}'); copied = true; setTimeout(() => copied = false, 2000); $wire.trackShare()">Copy link</button>
                    <span x-show="copied" class="text-xs text-green-600">Copied</span>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
            <div class="lg:col-span-3 space-y-6">
                <div class="bg-white shadow-sm rounded-lg border border-gray-100 p-6">
                    <div class="flex flex-wrap items-start justify-between gap-4">
                        <div>
                            <h1 class="text-2xl font-semibold text-gray-900">{{ $article->title }}</h1>
                            @if ($article->summary)
                                <p class="mt-2 text-sm text-gray-600">{{ $article->summary }}</p>
                            @endif
                            <div class="mt-3 flex flex-wrap items-center gap-3 text-xs text-gray-500">
                                <span>Author: {{ $article->author_name ?? $article->createdBy?->name ?? 'System' }}</span>
                                <span>Updated: {{ $article->updated_at?->format('M d, Y') }}</span>
                                <span>{{ $article->reading_time_minutes ?? 3 }} min read</span>
                                <span>{{ strtoupper($article->language ?? 'EN') }}</span>
                                @if ($article->is_machine_translated)
                                    <span class="px-2 py-0.5 rounded-full bg-yellow-50 text-yellow-600">Machine translated</span>
                                @endif
                            </div>
                        </div>
                        @if ($translations->count() > 1)
                            <div>
                                <label class="text-xs text-gray-500">Language</label>
                                <select class="mt-1 rounded-md border-gray-300 text-sm" onchange="window.location=this.value">
                                    @foreach ($translations as $translation)
                                        <option value="{{ route('knowledge-base.show', $translation) }}" @selected($translation->id === $article->id)>
                                            {{ strtoupper($translation->language ?? 'EN') }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        @endif
                    </div>

                    <div class="mt-6 kb-content" id="article-content">
                        {!! $content !!}
                    </div>

                    @if ($article->resources->isNotEmpty())
                        <div class="mt-6 border-t border-gray-100 pt-4">
                            <h2 class="text-base font-semibold text-gray-900">Downloads & resources</h2>
                            <div class="mt-3 space-y-2 text-sm">
                                @foreach ($article->resources as $resource)
                                    <div class="flex items-center justify-between">
                                        <a href="{{ $resource->url }}" class="text-indigo-600" target="_blank" rel="noreferrer">{{ $resource->label }}</a>
                                        <span class="text-xs text-gray-400">{{ strtoupper($resource->resource_type) }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>

                <div class="bg-white shadow-sm rounded-lg border border-gray-100 p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Was this article helpful?</h2>
                    <div class="flex items-center gap-3">
                        <button type="button" class="px-3 py-1 border border-gray-300 rounded-md" wire:click="rateHelpful(true)" @disabled($helpfulSubmitted)>Yes</button>
                        <button type="button" class="px-3 py-1 border border-gray-300 rounded-md" wire:click="rateHelpful(false)" @disabled($helpfulSubmitted)>No</button>
                        <span class="text-xs text-gray-500">{{ $article->helpful_count }} helpful · {{ $article->unhelpful_count }} not helpful</span>
                    </div>

                    <div class="mt-4">
                        <p class="text-sm text-gray-600">Rate this article</p>
                        <div class="mt-2 flex items-center gap-1">
                            @for ($star = 1; $star <= 5; $star++)
                                <button type="button" class="text-xl {{ $article->rating_avg >= $star ? 'text-yellow-400' : 'text-gray-300' }}" wire:click="submitRating({{ $star }})">
                                    ★
                                </button>
                            @endfor
                            <span class="text-xs text-gray-500 ml-2">{{ number_format($article->rating_avg, 1) }}/5</span>
                        </div>
                    </div>

                    <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="text-xs text-gray-500">Report inaccuracy</label>
                            <textarea wire:model="reportNotes" class="mt-1 w-full rounded-md border-gray-300" rows="2"></textarea>
                            <button type="button" class="mt-2 px-3 py-1 text-xs border border-gray-300 rounded-md" wire:click="reportInaccuracy">Report</button>
                        </div>
                        <div>
                            <label class="text-xs text-gray-500">Request update</label>
                            <textarea wire:model="updateRequestNotes" class="mt-1 w-full rounded-md border-gray-300" rows="2"></textarea>
                            <button type="button" class="mt-2 px-3 py-1 text-xs border border-gray-300 rounded-md" wire:click="requestUpdate">Request</button>
                        </div>
                    </div>
                </div>

                <div class="bg-white shadow-sm rounded-lg border border-gray-100 p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Comments & discussion</h2>
                    @if ($article->allow_comments)
                        <form wire:submit.prevent="submitComment" class="mb-4">
                            <textarea wire:model="newComment" class="w-full rounded-md border-gray-300" rows="3" placeholder="Share feedback or tips"></textarea>
                            @error('newComment') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
                            <button class="mt-2 px-3 py-1 bg-indigo-600 text-white rounded-md">Post comment</button>
                        </form>
                    @else
                        <p class="text-sm text-gray-500">Comments are disabled for this article.</p>
                    @endif

                    <div class="space-y-4">
                        @foreach ($article->comments as $comment)
                            @if ($comment->is_approved)
                                <div class="border border-gray-100 rounded-lg p-3">
                                    <div class="flex items-center justify-between text-xs text-gray-500">
                                        <span>{{ $comment->user?->name ?? 'Anonymous' }}</span>
                                        <span>{{ $comment->created_at?->diffForHumans() }}</span>
                                    </div>
                                    <p class="mt-2 text-sm text-gray-700">{{ $comment->body }}</p>
                                    <div class="mt-2 flex items-center gap-3 text-xs text-gray-500">
                                        <button type="button" wire:click="voteComment({{ $comment->id }}, 1)">▲ {{ $comment->upvotes }}</button>
                                        <button type="button" wire:click="voteComment({{ $comment->id }}, -1)">▼ {{ $comment->downvotes }}</button>
                                        @if ($comment->is_helpful)
                                            <span class="text-green-600">Helpful</span>
                                        @elseif ($canManage)
                                            <button type="button" class="text-indigo-600" wire:click="markCommentHelpful({{ $comment->id }})">Mark helpful</button>
                                        @endif
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="space-y-6">
                @if (! empty($toc))
                    <div class="bg-white shadow-sm rounded-lg border border-gray-100 p-6">
                        <h2 class="text-sm font-semibold text-gray-900 mb-3">On this page</h2>
                        <div class="space-y-2 text-xs text-gray-600">
                            @foreach ($toc as $item)
                                <a class="block hover:text-indigo-600" href="#{{ $item['anchor'] }}" style="margin-left: {{ ($item['level'] - 1) * 12 }}px;">
                                    {{ $item['title'] }}
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif

                @if ($prerequisites->isNotEmpty())
                    <div class="bg-white shadow-sm rounded-lg border border-gray-100 p-6">
                        <h2 class="text-sm font-semibold text-gray-900 mb-3">Prerequisites</h2>
                        <div class="space-y-2 text-sm">
                            @foreach ($prerequisites as $relation)
                                <a class="text-indigo-600" href="{{ route('knowledge-base.show', $relation->relatedArticle) }}">{{ $relation->relatedArticle?->title }}</a>
                            @endforeach
                        </div>
                    </div>
                @endif

                @if ($series->isNotEmpty())
                    <div class="bg-white shadow-sm rounded-lg border border-gray-100 p-6">
                        <h2 class="text-sm font-semibold text-gray-900 mb-3">Series</h2>
                        <div class="space-y-2 text-sm">
                            @foreach ($series as $relation)
                                <a class="text-indigo-600" href="{{ route('knowledge-base.show', $relation->relatedArticle) }}">{{ $relation->relatedArticle?->title }}</a>
                            @endforeach
                        </div>
                    </div>
                @endif

                @if ($seeAlso->isNotEmpty())
                    <div class="bg-white shadow-sm rounded-lg border border-gray-100 p-6">
                        <h2 class="text-sm font-semibold text-gray-900 mb-3">See also</h2>
                        <div class="space-y-2 text-sm">
                            @foreach ($seeAlso as $relation)
                                <a class="text-indigo-600" href="{{ route('knowledge-base.show', $relation->relatedArticle) }}">{{ $relation->relatedArticle?->title }}</a>
                            @endforeach
                        </div>
                    </div>
                @endif

                @if ($relatedVideos->isNotEmpty())
                    <div class="bg-white shadow-sm rounded-lg border border-gray-100 p-6">
                        <h2 class="text-sm font-semibold text-gray-900 mb-3">Related videos</h2>
                        <div class="space-y-2 text-sm">
                            @foreach ($relatedVideos as $video)
                                <a class="text-indigo-600" href="{{ $video->url }}" target="_blank" rel="noreferrer">{{ $video->label }}</a>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="bg-white shadow-sm rounded-lg border border-gray-100 p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Customers who read this also read</h2>
                <div class="space-y-3 text-sm">
                    @foreach ($autoRelated as $relatedArticle)
                        <a class="flex items-center justify-between" href="{{ route('knowledge-base.show', $relatedArticle) }}">
                            <span>{{ $relatedArticle->title }}</span>
                            <span class="text-xs text-gray-400">{{ $relatedArticle->view_count }} views</span>
                        </a>
                    @endforeach
                </div>
            </div>
            <div class="bg-white shadow-sm rounded-lg border border-gray-100 p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Related discussions</h2>
                <p class="text-sm text-gray-500">Forum integration is ready. Link relevant discussions here.</p>
                <div class="mt-4 text-xs text-gray-400">No discussions linked yet.</div>
            </div>
        </div>
    </div>

    <style>
        @media print {
            nav, button, select, textarea, input {
                display: none !important;
            }
            .kb-content {
                font-size: 12pt;
            }
        }
        .kb-content h1,
        .kb-content h2,
        .kb-content h3 {
            margin-top: 1.5rem;
            margin-bottom: 0.5rem;
            color: #111827;
        }
        .kb-content p {
            margin-top: 0.75rem;
            margin-bottom: 0.75rem;
            color: #374151;
            line-height: 1.6;
        }
        .kb-content ul,
        .kb-content ol {
            margin-left: 1.25rem;
            margin-top: 0.75rem;
            margin-bottom: 0.75rem;
            color: #374151;
        }
        .kb-content pre {
            background: #111827;
            color: #f9fafb;
            padding: 12px;
            border-radius: 8px;
            overflow-x: auto;
        }
        .kb-content code {
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
        }
        .kb-content table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1rem;
        }
        .kb-content table th,
        .kb-content table td {
            border: 1px solid #e5e7eb;
            padding: 8px;
            font-size: 0.85rem;
            text-align: left;
        }
        .kb-content .kb-callout {
            border-left: 4px solid #cbd5f5;
            background: #eef2ff;
            padding: 12px;
            border-radius: 8px;
            margin: 12px 0;
        }
        .kb-content .kb-callout-tip {
            border-left-color: #a7f3d0;
            background: #ecfdf5;
        }
        .kb-content .kb-callout-warning {
            border-left-color: #fcd34d;
            background: #fffbeb;
        }
        .kb-content details {
            margin: 12px 0;
            padding: 10px;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
        }
    </style>
</div>
