@props([
    'title',
    'subtitle' => null,
])

<div class="public-section-header">
    <h2 class="public-section-header__title">{{ $title }}</h2>
    @if($subtitle)
        <p class="public-section-header__subtitle">{{ $subtitle }}</p>
    @endif
</div>

<style>
    .public-section-header {
        text-align: center;
        margin-bottom: 48px;
    }

    .public-section-header__title {
        font-size: 32px;
        font-weight: 800;
        color: #0f172a;
        margin: 0 0 12px;
    }

    .public-section-header__subtitle {
        font-size: 16px;
        color: #64748b;
        margin: 0;
        max-width: 500px;
        margin-left: auto;
        margin-right: auto;
    }
</style>
