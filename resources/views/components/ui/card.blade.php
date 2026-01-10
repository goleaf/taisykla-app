@props([
    'padding' => true,
])

<div {{ $attributes->merge(['class' => $padding ? 'card-padded' : 'card']) }}>
    {{ $slot }}
</div>
