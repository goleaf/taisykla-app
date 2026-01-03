<div class="flex items-center justify-between" style="margin-left: {{ $level * 16 }}px;">
    <button type="button" class="text-sm text-gray-700 hover:text-indigo-600" wire:click="$set('categoryFilter', {{ $category->id }})">
        {{ $category->name }}
    </button>
    @if ($category->icon)
        <span class="text-xs text-gray-400">{{ $category->icon }}</span>
    @endif
</div>
@if ($category->children->isNotEmpty())
    <div class="mt-1 space-y-1">
        @foreach ($category->children as $child)
            @include('livewire.knowledge-base.partials.category-tree', ['category' => $child, 'level' => $level + 1])
        @endforeach
    </div>
@endif
