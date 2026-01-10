@props([
    'variant' => 'primary', // primary, secondary, outline, ghost
    'size' => 'md', // sm, md, lg
    'href' => null,
])
@php
    $baseClasses = 'public-button';
    $variantClasses = [
        'primary' => 'public-button--primary',
        'secondary' => 'public-button--secondary',
        'outline' => 'public-button--outline',
        'ghost' => 'public-button--ghost',
    ];
    $sizeClasses = [
        'sm' => 'public-button--sm',
        'md' => '',
        'lg' => 'public-button--lg',
    ];
    $classes = $baseClasses . ' ' . ($variantClasses[$variant] ?? '') . ' ' . ($sizeClasses[$size] ?? '');
@endphp
@if($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>
        {{ $slot }}
    </a>
@else
    <button {{ $attributes->merge(['class' => $classes, 'type' => 'button']) }}>
        {{ $slot }}
    </button>
@endif

<style>
    .public-button {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        padding: 12px 24px;
        font-size: 15px;
        font-weight: 600;
        border-radius: 12px;
        text-decoration: none;
        transition: all 0.2s;
        border: none;
        cursor: pointer;
        font-family: inherit;
    }

    .public-button--sm {
        padding: 8px 16px;
        font-size: 13px;
        border-radius: 10px;
    }

    .public-button--lg {
        padding: 16px 32px;
        font-size: 16px;
    }

    .public-button--primary {
        background: linear-gradient(135deg, #0f766e 0%, #0d5e58 100%);
        color: white;
        box-shadow: 0 4px 16px rgba(15, 118, 110, 0.25);
    }

    .public-button--primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 24px rgba(15, 118, 110, 0.35);
    }

    .public-button--secondary {
        background: #f1f5f9;
        color: #334155;
    }

    .public-button--secondary:hover {
        background: #e2e8f0;
    }

    .public-button--outline {
        background: transparent;
        color: #0f766e;
        border: 2px solid #0f766e;
    }

    .public-button--outline:hover {
        background: rgba(15, 118, 110, 0.05);
    }

    .public-button--ghost {
        background: transparent;
        color: #0f172a;
    }

    .public-button--ghost:hover {
        background: rgba(15, 118, 110, 0.08);
    }
</style>
