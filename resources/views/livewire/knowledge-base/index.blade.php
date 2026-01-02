<div class="py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900">Knowledge Base</h1>
                <p class="text-sm text-gray-500">Troubleshooting guides and best practices.</p>
            </div>
            <button class="px-4 py-2 bg-indigo-600 text-white rounded-md" wire:click="$toggle('showCreate')">New Article</button>
        </div>

        @if (session('status'))
            <div class="mb-4 rounded-md bg-green-50 p-3 text-sm text-green-700">
                {{ session('status') }}
            </div>
        @endif

        @if ($showCreate)
            <div class="bg-white shadow-sm rounded-lg p-6 border border-gray-100 mb-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Create Article</h2>
                <form wire:submit.prevent="createArticle" class="grid grid-cols-1 gap-4">
                    <div>
                        <label class="text-xs text-gray-500">Title</label>
                        <input wire:model="new.title" class="mt-1 w-full rounded-md border-gray-300" />
                        @error('new.title') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="text-xs text-gray-500">Category</label>
                        <input wire:model="new.category" class="mt-1 w-full rounded-md border-gray-300" />
                    </div>
                    <div>
                        <label class="text-xs text-gray-500">Content</label>
                        <textarea wire:model="new.content" class="mt-1 w-full rounded-md border-gray-300" rows="4"></textarea>
                        @error('new.content') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <label class="inline-flex items-center gap-2 text-sm">
                        <input type="checkbox" wire:model="new.is_published" class="rounded border-gray-300" />
                        Publish immediately
                    </label>
                    <button class="px-4 py-2 bg-indigo-600 text-white rounded-md">Save</button>
                </form>
            </div>
        @endif

        <div class="bg-white shadow-sm rounded-lg p-4 border border-gray-100 mb-6">
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
            </div>
        </div>

        <div class="bg-white shadow-sm rounded-lg border border-gray-100">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Title</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Category</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Updated</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @foreach ($articles as $article)
                        <tr>
                            <td class="px-4 py-3 text-sm text-gray-900">{{ $article->title }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700">{{ $article->category ?? '—' }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700">{{ $article->is_published ? 'Published' : 'Draft' }}</td>
                            <td class="px-4 py-3 text-sm text-gray-500">{{ $article->updated_at?->format('M d, Y') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="p-4">{{ $articles->links() }}</div>
        </div>
    </div>
</div>
