@props([
    'badge' => null,
    'title',
    'subtitle' => null,
    'centered' => true,
    'gradient' => false,
])

<section {{ $attributes->merge(['class' => 'public-hero' . ($gradient ? ' public-hero--gradient' : '')]) }}>
    <div class="public-hero__container {{ $centered ? 'text-center' : '' }}">
        @if($badge)
            <span class="public-hero__badge">
                {{ $badge }}
            </span>
        @endif
        <h1 class="public-hero__title">{{ $title }}</h1>
        @if($subtitle)
            <p class="public-hero__subtitle">{{ $subtitle }}</p>
        @endif
        @if($slot->isNotEmpty())
            <div class="public-hero__actions">
                {{ $slot }}
            </div>
        @endif
    </div>
</section>

<style>
    .public-hero {
        padding: 80px 24px 60px;
    }

    .public-hero--gradient {
        background: linear-gradient(180deg, #f0fdfa 0%, #ffffff 100%);
    }

    .public-hero__container {
        max-width: 800px;
        margin: 0 auto;
    }

    .public-hero__badge {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 8px 16px;
        background: linear-gradient(135deg, rgba(15, 118, 110, 0.1), rgba(20, 184, 166, 0.08));
        border: 1px solid rgba(15, 118, 110, 0.2);
        border-radius: 100px;
        font-size: 14px;
        font-weight: 600;
        color: #0f766e;
        margin-bottom: 24px;
    }

    .public-hero__title {
        font-size: clamp(32px, 5vw, 48px);
        font-weight: 800;
        color: #0f172a;
        margin: 0 0 16px;
        line-height: 1.2;
    }

    .public-hero__subtitle {
        font-size: 18px;
        color: #64748b;
        margin: 0 auto;
        max-width: 500px;
        line-height: 1.6;
    }

    .public-hero__actions {
        margin-top: 32px;
        display: flex;
        justify-content: center;
        gap: 16px;
        flex-wrap: wrap;
    }

    @media (max-width: 640px) {
        .public-hero {
            padding: 60px 20px 40px;
        }
    }
</style>
