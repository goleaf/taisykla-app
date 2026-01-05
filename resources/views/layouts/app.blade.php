<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- PWA Meta Tags -->
    <meta name="theme-color" content="#4f46e5">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="Taisykla">
    <link rel="manifest" href="/manifest.json">
    <link rel="apple-touch-icon" href="/icons/icon-192x192.png">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link
        href="https://fonts.bunny.net/css?family=figtree:400,500,600&family=space-grotesk:400,500,600,700&family=dm-serif-display:400&display=swap"
        rel="stylesheet" />

    <!-- Scripts -->
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

        <!-- Breadcrumbs (for pages without header) -->
        @if (!isset($header))
            <div class="max-w-7xl mx-auto pt-6 px-4 sm:px-6 lg:px-8">
                {{ Breadcrumbs::render() }}
            </div>
        @endif

        <!-- Page Heading -->
        @if (isset($header))
            <header class="bg-white shadow">
                <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                    {{ Breadcrumbs::render() }}
                    {{ $header }}
                </div>
            </header>
        @endif

        <!-- Page Content -->
        <main>
            <livewire:toast />
            {{ $slot }}
        </main>
    </div>
</body>

{{-- Offline Indicator --}}
<div id="offline-indicator" class="fixed bottom-4 left-4 right-4 md:left-auto md:right-4 md:w-auto z-50 hidden">
    <div class="flex items-center gap-2 px-4 py-3 bg-amber-500 text-white rounded-lg shadow-lg text-sm font-medium">
        <svg class="w-5 h-5 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M18.364 5.636a9 9 0 010 12.728m0 0l-2.829-2.829m2.829 2.829L21 21M15.536 8.464a5 5 0 010 7.072m0 0l-2.829-2.829m-4.243 2.829a4.978 4.978 0 01-1.414-2.83m-1.414 5.658a9 9 0 01-2.167-9.238m7.824 2.167a1 1 0 111.414 1.414m-1.414-1.414L3 3m8.293 8.293l1.414 1.414" />
        </svg>
        You're offline. Some features may be unavailable.
    </div>
</div>

{{-- Service Worker Registration --}}
<script>
    // Register Service Worker
    if ('serviceWorker' in navigator) {
        window.addEventListener('load', () => {
            navigator.serviceWorker.register('/sw.js')
                .then(reg => console.log('[App] Service Worker registered'))
                .catch(err => console.log('[App] Service Worker registration failed:', err));
        });
    }

    // Online/Offline indicator
    const offlineIndicator = document.getElementById('offline-indicator');

    function updateOnlineStatus() {
        if (!navigator.onLine) {
            offlineIndicator.classList.remove('hidden');
        } else {
            offlineIndicator.classList.add('hidden');
        }
    }

    window.addEventListener('online', updateOnlineStatus);
    window.addEventListener('offline', updateOnlineStatus);
    updateOnlineStatus();
</script>
</body>

</html>