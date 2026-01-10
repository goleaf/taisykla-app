@props([
    'name',
    'size' => 'sm',
    'variant' => 'primary',
])
@php
    $initials = collect(explode(' ', $name))->map(fn($word) => strtoupper(substr($word, 0, 1)))->take(2)->join('');
    $sizeClass = match ($size) {
        'sm' => 'avatar-sm',
        'md' => 'avatar-md',
        'lg' => 'avatar-lg',
        'xl' => 'avatar-xl',
        default => 'avatar-md',
    };
@endphp

<div {{ $attributes->merge(['class' => "avatar {$sizeClass} avatar-{$variant}"]) }}>
    {{ $initials }}
</div>
