@props([
    'title',
    'subtitle' => null,
    'actions' => null,
])

<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">{{ $title }}</h1>
            @if($subtitle)
                <p class="page-subtitle">{{ $subtitle }}</p>
            @endif
        </div>
        @if($actions)
            <div class="page-actions">
                {{ $actions }}
            </div>
        @endif
    </div>
</div>
