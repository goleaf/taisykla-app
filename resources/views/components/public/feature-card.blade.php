@props([
    'icon' => null,
    'title',
    'description',
])

<div {{ $attributes->merge(['class' => 'public-feature-card']) }}>
    @if($icon)
        <div class="public-feature-card__icon">
            {{ $icon }}
        </div>
    @endif
    <h3 class="public-feature-card__title">{{ $title }}</h3>
    <p class="public-feature-card__description">{{ $description }}</p>
    @if($slot->isNotEmpty())
        <div class="public-feature-card__content">
            {{ $slot }}
        </div>
    @endif
</div>

<style>
    .public-feature-card {
        background: white;
        border: 1px solid #e2e8f0;
        border-radius: 20px;
        padding: 32px;
        transition: all 0.3s;
    }

    .public-feature-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 20px 40px rgba(15, 23, 42, 0.08);
    }

    .public-feature-card__icon {
        width: 56px;
        height: 56px;
        border-radius: 16px;
        background: linear-gradient(135deg, #f0fdfa 0%, #ccfbf1 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 20px;
        color: #0f766e;
    }

    .public-feature-card__icon svg {
        width: 28px;
        height: 28px;
    }

    .public-feature-card__title {
        font-size: 20px;
        font-weight: 700;
        color: #0f172a;
        margin: 0 0 12px;
    }

    .public-feature-card__description {
        font-size: 15px;
        color: #64748b;
        line-height: 1.6;
        margin: 0;
    }

    .public-feature-card__content {
        margin-top: 20px;
    }
</style>
