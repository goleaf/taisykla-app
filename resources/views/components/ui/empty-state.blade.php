@props([
    'title' => 'No items found',
    'description' => null,
    'icon' => null,
])

<div class="empty-state">
    @if($icon)
        <div class="empty-state-icon">
            {{ $icon }}
        </div>
    @else
        <div class="empty-state-icon">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
            </svg>
        </div>
    @endif
    <h3 class="empty-state-title">{{ $title }}</h3>
    @if($description)
        <p class="empty-state-text">{{ $description }}</p>
    @endif
    @if($slot->isNotEmpty())
        <div class="mt-4">
            {{ $slot }}
        </div>
    @endif
</div>
