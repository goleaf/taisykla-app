<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&family=space-grotesk:400,500,600,700&family=dm-serif-display:400&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased">
        <div class="min-h-screen bg-gray-100">
            <livewire:layout.navigation />

            {{-- Global Loading Bar --}}
            <div wire:loading.delay.shortest class="fixed top-0 left-0 right-0 z-[100]">
                <div class="h-1 w-full bg-indigo-100 overflow-hidden">
                    <div class="h-full bg-indigo-600 animate-progress origin-left-right"></div>
                </div>
            </div>

            @if (isset($header))
                <header class="bg-white shadow">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endif

            <main>
                <livewire:toast />
                {{ $slot }}
            </main>
        </div>
    </body>
</html>
