@props([
    'variant' => 'primary',
    'size' => 'md',
    'href' => null,
    'type' => 'button',
])

@php
$baseClasses = match($variant) {
    'primary' => 'btn-primary',
    'secondary' => 'btn-secondary',
    'outline' => 'btn-outline',
    'ghost' => 'btn-ghost',
    'danger' => 'btn-danger',
    'success' => 'btn-success',
    default => 'btn-primary',
};

$sizeClasses = match($size) {
    'sm' => 'btn-sm',
    'lg' => 'btn-lg',
    default => '',
};

$classes = trim("$baseClasses $sizeClasses");
@endphp

@if($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>
        {{ $slot }}
    </a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => $classes]) }}>
        {{ $slot }}
    </button>
@endif
