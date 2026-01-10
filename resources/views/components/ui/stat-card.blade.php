@props([
    'value',
    'label',
    'icon' => null,
    'change' => null,
    'changeDirection' => null,
    'variant' => 'primary',
])

<div class="stat-card">
    @if($icon)
        <div class="stat-card-icon {{ $variant }}">
            {{ $icon }}
        </div>
    @endif
    <div class="stat-value">{{ $value }}</div>
    <div class="stat-label">{{ $label }}</div>
    @if($change)
        <div class="stat-change {{ $changeDirection === 'up' ? 'up' : ($changeDirection === 'down' ? 'down' : '') }}">
            @if($changeDirection === 'up')
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"/></svg>
            @elseif($changeDirection === 'down')
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"/></svg>
            @endif
            {{ $change }}
        </div>
    @endif
</div>
