@props([
    'value',
    'label',
    'suffix' => null,
])

<div {{ $attributes->merge(['class' => 'public-stat']) }}>
    <div class="public-stat__value">
        {{ $value }}
        @if($suffix)
            <span class="public-stat__suffix">{{ $suffix }}</span>
        @endif
    </div>
    <div class="public-stat__label">{{ $label }}</div>
</div>

<style>
    .public-stat {
        text-align: center;
        padding: 24px;
    }

    .public-stat__value {
        font-size: 48px;
        font-weight: 800;
        color: #0f766e;
        line-height: 1;
        margin-bottom: 8px;
    }

    .public-stat__suffix {
        font-size: 24px;
        font-weight: 600;
        color: #14b8a6;
    }

    .public-stat__label {
        font-size: 14px;
        color: #64748b;
        font-weight: 500;
    }
</style>
