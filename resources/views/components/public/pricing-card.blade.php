@props([
    'name',
    'price',
    'period' => '/month',
    'description',
    'features' => [],
    'featured' => false,
    'featuredLabel' => 'Most Popular',
    'ctaText' => 'Get Started',
    'ctaUrl' => null,
    'variant' => 'default', // default, starter, professional, enterprise
])

@php
$iconColors = [
    'starter' => 'background: linear-gradient(135deg, #fef3c7, #fde68a); color: #b45309;',
    'professional' => 'background: linear-gradient(135deg, #ccfbf1, #99f6e4); color: #0f766e;',
    'enterprise' => 'background: linear-gradient(135deg, #e0e7ff, #c7d2fe); color: #4338ca;',
    'default' => 'background: linear-gradient(135deg, #ccfbf1, #99f6e4); color: #0f766e;',
];
@endphp

<div {{ $attributes->merge(['class' => 'public-pricing-card' . ($featured ? ' public-pricing-card--featured' : '')]) }}>
    @if($featured)
        <div class="public-pricing-card__badge">{{ $featuredLabel }}</div>
    @endif
    
    <div class="public-pricing-card__header">
        <div class="public-pricing-card__icon" style="{{ $iconColors[$variant] ?? $iconColors['default'] }}">
            {{ $icon ?? '' }}
        </div>
        <h3 class="public-pricing-card__name">{{ $name }}</h3>
        <p class="public-pricing-card__description">{{ $description }}</p>
    </div>
    
    <div class="public-pricing-card__price-section">
        <div class="public-pricing-card__price">
            @if(is_numeric($price))
                <span class="currency">€</span>
                <span class="amount">{{ $price }}</span>
                <span class="period">{{ $period }}</span>
            @else
                <span class="amount custom">{{ $price }}</span>
            @endif
        </div>
        @if($priceNote ?? false)
            <p class="public-pricing-card__price-note">{{ $priceNote }}</p>
        @endif
    </div>
    
    @if(count($features) > 0)
        <ul class="public-pricing-card__features">
            @foreach($features as $feature)
                <li>
                    <svg class="check" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    {{ $feature }}
                </li>
            @endforeach
        </ul>
    @endif
    
    @if($ctaUrl)
        <a href="{{ $ctaUrl }}" class="public-pricing-card__cta {{ $featured ? 'primary' : 'secondary' }}">
            {{ $ctaText }}
        </a>
    @endif
    
    @if($slot->isNotEmpty())
        {{ $slot }}
    @endif
</div>

<style>
    .public-pricing-card {
        background: white;
        border: 1px solid #e2e8f0;
        border-radius: 24px;
        padding: 32px;
        display: flex;
        flex-direction: column;
        position: relative;
        transition: all 0.3s;
    }

    .public-pricing-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 20px 40px rgba(15, 23, 42, 0.1);
    }

    .public-pricing-card--featured {
        border: 2px solid #0f766e;
        background: linear-gradient(180deg, rgba(15, 118, 110, 0.02) 0%, white 20%);
    }

    .public-pricing-card__badge {
        position: absolute;
        top: -12px;
        left: 50%;
        transform: translateX(-50%);
        padding: 6px 16px;
        background: linear-gradient(135deg, #0f766e 0%, #14b8a6 100%);
        color: white;
        font-size: 12px;
        font-weight: 700;
        border-radius: 100px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .public-pricing-card__header {
        margin-bottom: 24px;
    }

    .public-pricing-card__icon {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 16px;
    }

    .public-pricing-card__icon svg {
        width: 24px;
        height: 24px;
    }

    .public-pricing-card__name {
        font-size: 24px;
        font-weight: 700;
        color: #0f172a;
        margin: 0 0 8px;
    }

    .public-pricing-card__description {
        font-size: 14px;
        color: #64748b;
        margin: 0;
        line-height: 1.5;
    }

    .public-pricing-card__price-section {
        padding: 24px 0;
        border-top: 1px solid #f1f5f9;
        border-bottom: 1px solid #f1f5f9;
        margin-bottom: 24px;
    }

    .public-pricing-card__price {
        display: flex;
        align-items: baseline;
        gap: 4px;
    }

    .public-pricing-card__price .currency {
        font-size: 24px;
        font-weight: 600;
        color: #64748b;
    }

    .public-pricing-card__price .amount {
        font-size: 48px;
        font-weight: 800;
        color: #0f172a;
        line-height: 1;
    }

    .public-pricing-card__price .amount.custom {
        font-size: 36px;
    }

    .public-pricing-card__price .period {
        font-size: 16px;
        color: #64748b;
    }

    .public-pricing-card__price-note {
        font-size: 13px;
        color: #94a3b8;
        margin: 8px 0 0;
    }

    .public-pricing-card__features {
        list-style: none;
        padding: 0;
        margin: 0 0 32px;
        flex: 1;
    }

    .public-pricing-card__features li {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 10px 0;
        font-size: 14px;
        color: #334155;
    }

    .public-pricing-card__features .check {
        width: 20px;
        height: 20px;
        color: #0f766e;
        flex-shrink: 0;
    }

    .public-pricing-card__cta {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 14px 24px;
        font-size: 15px;
        font-weight: 600;
        border-radius: 12px;
        text-decoration: none;
        transition: all 0.2s;
        text-align: center;
    }

    .public-pricing-card__cta.primary {
        background: linear-gradient(135deg, #0f766e 0%, #0d5e58 100%);
        color: white;
        box-shadow: 0 4px 16px rgba(15, 118, 110, 0.3);
    }

    .public-pricing-card__cta.primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 24px rgba(15, 118, 110, 0.4);
    }

    .public-pricing-card__cta.secondary {
        background: #f1f5f9;
        color: #334155;
    }

    .public-pricing-card__cta.secondary:hover {
        background: #e2e8f0;
    }
</style>
