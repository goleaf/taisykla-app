@props([
    'title',
    'subtitle' => null,
    'primaryText' => 'Get Started',
    'primaryUrl' => null,
    'secondaryText' => null,
    'secondaryUrl' => null,
    'dark' => true,
])

<section {{ $attributes->merge(['class' => 'public-cta' . ($dark ? ' public-cta--dark' : '')]) }}>
    <div class="public-cta__container">
        <div class="public-cta__content">
            <h2 class="public-cta__title">{{ $title }}</h2>
            @if($subtitle)
                <p class="public-cta__subtitle">{{ $subtitle }}</p>
            @endif
            
            <div class="public-cta__buttons">
                @if($primaryUrl)
                    <a href="{{ $primaryUrl }}" class="public-cta__button primary">
                        {{ $primaryText }}
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="20" height="20">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                        </svg>
                    </a>
                @endif
                @if($secondaryUrl && $secondaryText)
                    <a href="{{ $secondaryUrl }}" class="public-cta__button secondary">
                        {{ $secondaryText }}
                    </a>
                @endif
            </div>
        </div>
    </div>
</section>

<style>
    .public-cta {
        padding: 0 24px 80px;
    }

    .public-cta__container {
        max-width: 1000px;
        margin: 0 auto;
    }

    .public-cta__content {
        border-radius: 24px;
        padding: 60px 40px;
        text-align: center;
        position: relative;
        overflow: hidden;
    }

    .public-cta--dark .public-cta__content {
        background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
    }

    .public-cta--dark .public-cta__content::before {
        content: '';
        position: absolute;
        top: -100px;
        right: -100px;
        width: 300px;
        height: 300px;
        background: radial-gradient(circle, rgba(20, 184, 166, 0.3), transparent);
        border-radius: 50%;
    }

    .public-cta--dark .public-cta__content::after {
        content: '';
        position: absolute;
        bottom: -100px;
        left: -100px;
        width: 300px;
        height: 300px;
        background: radial-gradient(circle, rgba(245, 158, 11, 0.2), transparent);
        border-radius: 50%;
    }

    .public-cta__title {
        font-size: 32px;
        font-weight: 800;
        margin: 0 0 12px;
        position: relative;
        z-index: 1;
    }

    .public-cta--dark .public-cta__title {
        color: white;
    }

    .public-cta__subtitle {
        font-size: 18px;
        margin: 0 0 32px;
        position: relative;
        z-index: 1;
    }

    .public-cta--dark .public-cta__subtitle {
        color: rgba(255, 255, 255, 0.7);
    }

    .public-cta__buttons {
        display: flex;
        justify-content: center;
        gap: 16px;
        flex-wrap: wrap;
        position: relative;
        z-index: 1;
    }

    .public-cta__button {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        padding: 16px 32px;
        font-size: 16px;
        font-weight: 600;
        border-radius: 12px;
        text-decoration: none;
        transition: all 0.2s;
    }

    .public-cta__button.primary {
        background: linear-gradient(135deg, #0f766e 0%, #0d5e58 100%);
        color: white;
        box-shadow: 0 4px 16px rgba(15, 118, 110, 0.3);
    }

    .public-cta__button.primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 24px rgba(15, 118, 110, 0.4);
    }

    .public-cta--dark .public-cta__button.secondary {
        background: transparent;
        color: white;
        border: 2px solid white;
    }

    .public-cta--dark .public-cta__button.secondary:hover {
        background: rgba(255, 255, 255, 0.1);
    }

    @media (max-width: 640px) {
        .public-cta__content {
            padding: 40px 24px;
        }

        .public-cta__title {
            font-size: 24px;
        }

        .public-cta__buttons {
            flex-direction: column;
        }
    }
</style>
