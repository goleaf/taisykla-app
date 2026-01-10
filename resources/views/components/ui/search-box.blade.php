@props([
    'placeholder' => 'Search...',
    'model' => null,
])

<div class="search-box">
    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
    </svg>
    <input 
        type="search" 
        placeholder="{{ $placeholder }}"
        @if($model) wire:model.live.debounce.300ms="{{ $model }}" @endif
        {{ $attributes }}
    >
</div>
