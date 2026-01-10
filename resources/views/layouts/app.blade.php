<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Taisykla') }}</title>

    <!-- PWA Meta Tags -->
    <meta name="theme-color" content="#0f766e">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="Taisykla">
    <link rel="manifest" href="/manifest.json">
    <link rel="apple-touch-icon" href="/icons/icon-192x192.png">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        :root {
            --primary: #0f766e;
            --primary-dark: #0d5e58;
            --primary-light: #14b8a6;
            --accent: #f59e0b;
            --dark: #0f172a;
            --muted: #64748b;
            --light: #f8fafc;
            --border: #e2e8f0;
            --sidebar-width: 260px;
        }

        * {
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, sans-serif;
            margin: 0;
            background: #f1f5f9;
        }

        /* App Shell */
        .app-shell {
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar */
        .sidebar {
            width: var(--sidebar-width);
            background: white;
            border-right: 1px solid var(--border);
            display: flex;
            flex-direction: column;
            position: fixed;
            top: 0;
            left: 0;
            bottom: 0;
            z-index: 40;
            transform: translateX(-100%);
            transition: transform 0.3s ease;
        }

        .sidebar.open {
            transform: translateX(0);
        }

        @media (min-width: 1024px) {
            .sidebar {
                transform: translateX(0);
            }
        }

        .sidebar-header {
            padding: 20px;
            border-bottom: 1px solid var(--border);
        }

        .sidebar-logo {
            display: flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
        }

        .sidebar-logo-icon {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 700;
            font-size: 16px;
        }

        .sidebar-logo span {
            font-size: 20px;
            font-weight: 800;
            color: var(--primary);
        }

        .sidebar-nav {
            flex: 1;
            padding: 16px 12px;
            overflow-y: auto;
        }

        .nav-section {
            margin-bottom: 24px;
        }

        .nav-section-title {
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: var(--muted);
            padding: 0 12px;
            margin-bottom: 8px;
        }

        .nav-link {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 10px 12px;
            border-radius: 10px;
            color: #475569;
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.15s;
            margin-bottom: 2px;
        }

        .nav-link:hover {
            background: #f1f5f9;
            color: var(--dark);
        }

        .nav-link.active {
            background: linear-gradient(135deg, rgba(15, 118, 110, 0.1) 0%, rgba(20, 184, 166, 0.08) 100%);
            color: var(--primary);
        }

        .nav-link svg {
            width: 20px;
            height: 20px;
            flex-shrink: 0;
        }

        .nav-badge {
            margin-left: auto;
            padding: 2px 8px;
            background: var(--primary);
            color: white;
            border-radius: 10px;
            font-size: 11px;
            font-weight: 600;
        }

        .sidebar-footer {
            padding: 16px;
            border-top: 1px solid var(--border);
        }

        .user-menu {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 10px 12px;
            border-radius: 10px;
            cursor: pointer;
            transition: background 0.15s;
        }

        .user-menu:hover {
            background: #f1f5f9;
        }

        .user-avatar {
            width: 36px;
            height: 36px;
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            font-size: 14px;
        }

        .user-info {
            flex: 1;
            min-width: 0;
        }

        .user-name {
            font-size: 14px;
            font-weight: 600;
            color: var(--dark);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .user-role {
            font-size: 12px;
            color: var(--muted);
        }

        /* Main Content */
        .main-content {
            flex: 1;
            margin-left: 0;
            transition: margin-left 0.3s ease;
        }

        @media (min-width: 1024px) {
            .main-content {
                margin-left: var(--sidebar-width);
            }
        }

        /* Top Bar */
        .top-bar {
            background: white;
            border-bottom: 1px solid var(--border);
            padding: 0 24px;
            height: 64px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 30;
        }

        .top-bar-left {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .menu-toggle {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            border-radius: 10px;
            border: none;
            background: transparent;
            cursor: pointer;
            color: var(--muted);
            transition: all 0.15s;
        }

        .menu-toggle:hover {
            background: #f1f5f9;
            color: var(--dark);
        }

        @media (min-width: 1024px) {
            .menu-toggle {
                display: none;
            }
        }

        .breadcrumb {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
            color: var(--muted);
        }

        .breadcrumb a {
            color: var(--muted);
            text-decoration: none;
            transition: color 0.15s;
        }

        .breadcrumb a:hover {
            color: var(--primary);
        }

        .breadcrumb-current {
            color: var(--dark);
            font-weight: 500;
        }

        .top-bar-right {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .search-box {
            position: relative;
            width: 280px;
        }

        .search-box input {
            width: 100%;
            padding: 10px 16px 10px 40px;
            border: 1px solid var(--border);
            border-radius: 10px;
            font-size: 14px;
            background: #f8fafc;
            transition: all 0.15s;
        }

        .search-box input:focus {
            outline: none;
            border-color: var(--primary);
            background: white;
            box-shadow: 0 0 0 3px rgba(15, 118, 110, 0.1);
        }

        .search-box svg {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            width: 18px;
            height: 18px;
            color: var(--muted);
        }

        .icon-btn {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            border: 1px solid var(--border);
            background: white;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            color: var(--muted);
            transition: all 0.15s;
            position: relative;
        }

        .icon-btn:hover {
            border-color: var(--primary);
            color: var(--primary);
        }

        .icon-btn svg {
            width: 20px;
            height: 20px;
        }

        .icon-btn .badge {
            position: absolute;
            top: -4px;
            right: -4px;
            width: 18px;
            height: 18px;
            background: #ef4444;
            color: white;
            border-radius: 50%;
            font-size: 10px;
            font-weight: 600;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* Page Content */
        .page-content {
            padding: 24px;
            max-width: 1600px;
            margin: 0 auto;
        }

        .page-header {
            margin-bottom: 24px;
        }

        .page-title {
            font-size: 28px;
            font-weight: 700;
            color: var(--dark);
            margin: 0;
        }

        .page-subtitle {
            color: var(--muted);
            margin-top: 4px;
        }

        /* Cards */
        .card {
            background: white;
            border: 1px solid var(--border);
            border-radius: 16px;
            padding: 24px;
        }

        .card-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 16px;
        }

        .card-title {
            font-size: 16px;
            font-weight: 600;
            color: var(--dark);
        }

        /* Loading Bar */
        .loading-bar {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: var(--primary-light);
            z-index: 100;
        }

        .loading-bar::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            height: 100%;
            width: 30%;
            background: var(--primary);
            animation: loading 1s ease-in-out infinite;
        }

        @keyframes loading {
            0% {
                left: -30%;
            }

            100% {
                left: 100%;
            }
        }

        /* Sidebar Overlay */
        .sidebar-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.5);
            z-index: 35;
        }

        .sidebar-overlay.show {
            display: block;
        }

        @media (min-width: 1024px) {
            .sidebar-overlay {
                display: none !important;
            }
        }

        /* Offline Indicator */
        .offline-bar {
            background: #fef3c7;
            border-bottom: 1px solid #fcd34d;
            padding: 8px 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            font-size: 14px;
            color: #92400e;
        }

        .offline-bar svg {
            width: 18px;
            height: 18px;
        }
    </style>
</head>

<body>
    <div class="app-shell" x-data="{ sidebarOpen: false }">
        <!-- Sidebar Overlay -->
        <div class="sidebar-overlay" :class="{ 'show': sidebarOpen }" @click="sidebarOpen = false"></div>

        <!-- Sidebar -->
        <aside class="sidebar" :class="{ 'open': sidebarOpen }">
            <div class="sidebar-header">
                <a href="{{ route('dashboard') }}" class="sidebar-logo" wire:navigate>
                    <div class="sidebar-logo-icon">T</div>
                    <span>Taisykla</span>
                </a>
            </div>

            <livewire:layout.sidebar-nav />

            <div class="sidebar-footer">
                <livewire:layout.user-menu />
            </div>
        </aside>

        <!-- Main Content -->
        <div class="main-content">
            <!-- Loading Bar -->
            <div wire:loading.delay.shortest class="loading-bar"></div>

            <!-- Top Bar -->
            <header class="top-bar">
                <div class="top-bar-left">
                    <button class="menu-toggle" @click="sidebarOpen = !sidebarOpen">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>
                    <div class="breadcrumb">
                        {{ Breadcrumbs::render() }}
                    </div>
                </div>
                <div class="top-bar-right">
                    <livewire:global-search />
                </div>
            </header>

            <!-- Page Content -->
            <main class="page-content">
                <livewire:toast />
                {{ $slot }}
            </main>
        </div>
    </div>

    <!-- Offline Indicator -->
    <div id="offline-indicator" class="offline-bar" style="display: none;">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M18.364 5.636a9 9 0 010 12.728m0 0l-2.829-2.829m2.829 2.829L21 21M15.536 8.464a5 5 0 010 7.072m0 0l-2.829-2.829m-4.243 2.829a4.978 4.978 0 01-1.414-2.83m-1.414 5.658a9 9 0 01-2.167-9.238m7.824 2.167a1 1 0 111.414 1.414m-1.414-1.414L3 3m8.293 8.293l1.414 1.414" />
        </svg>
        You're offline. Some features may be unavailable.
    </div>

    <script>
        // Service Worker Registration
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
            offlineIndicator.style.display = navigator.onLine ? 'none' : 'flex';
        }

        window.addEventListener('online', updateOnlineStatus);
        window.addEventListener('offline', updateOnlineStatus);
        updateOnlineStatus();
    </script>
</body>

</html>