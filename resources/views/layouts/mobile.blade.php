<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport"
        content="width=device-width, initial-scale=1, viewport-fit=cover, maximum-scale=1, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Taisykla') }} - Field Technician</title>

    <!-- PWA Meta Tags -->
    <meta name="theme-color" content="#1e1b4b">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="Field Tech">
    <link rel="manifest" href="/manifest.json">
    <link rel="apple-touch-icon" href="/icons/icon-192x192.png">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        :root {
            --safe-area-inset-top: env(safe-area-inset-top, 0px);
            --safe-area-inset-right: env(safe-area-inset-right, 0px);
            --safe-area-inset-bottom: env(safe-area-inset-bottom, 0px);
            --safe-area-inset-left: env(safe-area-inset-left, 0px);
        }

        body {
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
            overscroll-behavior-y: none;
        }

        /* High contrast for outdoor visibility */
        .high-contrast {
            filter: contrast(1.1);
        }

        /* Large touch targets */
        .touch-target {
            min-height: 44px;
            min-width: 44px;
        }

        /* Safe area padding */
        .safe-area-top {
            padding-top: max(var(--safe-area-inset-top), 12px);
        }

        .safe-area-bottom {
            padding-bottom: max(calc(var(--safe-area-inset-bottom) + 70px), 90px);
        }

        /* Bottom nav safe area */
        .bottom-nav-safe {
            padding-bottom: var(--safe-area-inset-bottom);
        }

        /* Smooth scroll */
        .scroll-smooth {
            scroll-behavior: smooth;
            -webkit-overflow-scrolling: touch;
        }

        /* Pull to refresh animation */
        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        .animate-spin-slow {
            animation: spin 1.5s linear infinite;
        }

        /* Swipe action styles */
        .swipe-container {
            overflow: hidden;
            position: relative;
        }

        .swipe-actions {
            position: absolute;
            top: 0;
            right: 0;
            bottom: 0;
            display: flex;
            transform: translateX(100%);
            transition: transform 0.2s ease-out;
        }

        .swipe-container.swiped .swipe-actions {
            transform: translateX(0);
        }

        /* Timer display */
        .timer-display {
            font-variant-numeric: tabular-nums;
            letter-spacing: 0.05em;
        }

        /* Glassmorphism effects */
        .glass {
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
        }

        .glass-dark {
            background: rgba(30, 27, 75, 0.9);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
        }
    </style>
</head>

<body class="bg-slate-100 text-slate-900 antialiased">
    <div class="min-h-screen flex flex-col">
        {{ $slot }}
    </div>

    <!-- Offline Indicator -->
    <div id="offline-indicator" class="fixed top-4 inset-x-4 z-[100] hidden safe-area-top">
        <div
            class="flex items-center gap-3 px-4 py-3 bg-amber-500 text-white rounded-xl shadow-lg text-sm font-semibold">
            <svg class="w-5 h-5 animate-pulse flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M18.364 5.636a9 9 0 010 12.728m0 0l-2.829-2.829m2.829 2.829L21 21M15.536 8.464a5 5 0 010 7.072m0 0l-2.829-2.829m-4.243 2.829a4.978 4.978 0 01-1.414-2.83m-1.414 5.658a9 9 0 01-2.167-9.238m7.824 2.167a1 1 0 111.414 1.414m-1.414-1.414L3 3m8.293 8.293l1.414 1.414" />
            </svg>
            <span>You're offline - Changes will sync when connected</span>
        </div>
    </div>

    <!-- Syncing Indicator -->
    <div id="sync-indicator" class="fixed top-4 inset-x-4 z-[100] hidden safe-area-top">
        <div
            class="flex items-center gap-3 px-4 py-3 bg-indigo-600 text-white rounded-xl shadow-lg text-sm font-semibold">
            <svg class="w-5 h-5 animate-spin flex-shrink-0" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor"
                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                </path>
            </svg>
            <span>Syncing your changes...</span>
        </div>
    </div>

    <script>
        // Network status handling
        const offlineIndicator = document.getElementById('offline-indicator');
        const syncIndicator = document.getElementById('sync-indicator');

        function updateOnlineStatus() {
            if (!navigator.onLine) {
                offlineIndicator.classList.remove('hidden');
                syncIndicator.classList.add('hidden');
            } else {
                offlineIndicator.classList.add('hidden');
            }

            // Dispatch Livewire event
            if (typeof Livewire !== 'undefined') {
                Livewire.dispatch('network-status-changed', { online: navigator.onLine });
            }
        }

        window.addEventListener('online', updateOnlineStatus);
        window.addEventListener('offline', updateOnlineStatus);
        updateOnlineStatus();

        // Register Service Worker
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/sw.js')
                    .then(reg => console.log('[Mobile] Service Worker registered'))
                    .catch(err => console.log('[Mobile] Service Worker registration failed:', err));
            });
        }

        // Handle phone calls
        window.addEventListener('open-phone', (e) => {
            window.location.href = 'tel:' + e.detail.phone;
        });

        // Handle SMS
        window.addEventListener('open-sms', (e) => {
            window.location.href = 'sms:' + e.detail.phone;
        });

        // Handle navigation
        window.addEventListener('open-navigation', (e) => {
            const { lat, lng, address } = e.detail;
            if (lat && lng) {
                // Try Apple Maps first on iOS, fallback to Google Maps
                if (/iPhone|iPad|iPod/i.test(navigator.userAgent)) {
                    window.location.href = `maps://maps.apple.com/?daddr=${lat},${lng}&dirflg=d`;
                } else {
                    window.location.href = `https://www.google.com/maps/dir/?api=1&destination=${lat},${lng}`;
                }
            } else if (address) {
                window.location.href = `https://www.google.com/maps/dir/?api=1&destination=${encodeURIComponent(address)}`;
            }
        });

        // Handle clipboard
        window.addEventListener('copy-to-clipboard', async (e) => {
            try {
                await navigator.clipboard.writeText(e.detail.text);
                // Show brief toast
                showToast('Address copied!');
            } catch (err) {
                console.error('Failed to copy:', err);
            }
        });

        // Toast notification
        function showToast(message, duration = 2000) {
            const toast = document.createElement('div');
            toast.className = 'fixed bottom-24 inset-x-4 z-[100] flex justify-center pointer-events-none';
            toast.innerHTML = `
                <div class="px-4 py-2 bg-slate-900 text-white rounded-full text-sm font-medium shadow-lg">
                    ${message}
                </div>
            `;
            document.body.appendChild(toast);
            setTimeout(() => toast.remove(), duration);
        }

        // Geolocation tracking
        if ('geolocation' in navigator) {
            navigator.geolocation.watchPosition(
                (position) => {
                    if (typeof Livewire !== 'undefined') {
                        Livewire.dispatch('location-updated', {
                            lat: position.coords.latitude,
                            lng: position.coords.longitude
                        });
                    }
                },
                (error) => console.log('Geolocation error:', error),
                { enableHighAccuracy: true, timeout: 10000, maximumAge: 30000 }
            );
        }
    </script>
</body>

</html>