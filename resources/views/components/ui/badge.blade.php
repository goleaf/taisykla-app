@props([
    'variant' => 'neutral',
])

@php
    $classes = match ($variant) {
        'primary' => 'badge-primary',
        'success' => 'badge-success',
        'warning' => 'badge-warning',
        'danger' => 'badge-danger',
        'info' => 'badge-info',
        default => 'badge-neutral',
    };
@endphp

<span {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</span>
