<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title') - {{ config('app.name', 'Taisykla') }}</title>
    <meta name="description" content="@yield('description', 'Equipment Maintenance Management Platform')">

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        :root {
            --primary: #0f766e;
            --primary-dark: #0d5e58;
            --primary-light: #14b8a6;
            --dark: #0f172a;
            --muted: #64748b;
            --light: #f8fafc;
            --border: #e2e8f0;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Inter', -apple-system, sans-serif;
            color: var(--dark);
            background: #f8fafc;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* Navigation */
        nav {
            padding: 20px 0;
            background: white;
            border-bottom: 1px solid var(--border);
        }

        nav .inner {
            max-width: 1280px;
            margin: 0 auto;
            padding: 0 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 12px;
            font-weight: 800;
            font-size: 24px;
            color: var(--primary);
            text-decoration: none;
        }

        .logo-icon {
            width: 44px;
            height: 44px;
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 700;
            font-size: 18px;
        }

        .nav-buttons {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 10px 20px;
            border-radius: 10px;
            font-weight: 600;
            font-size: 14px;
            text-decoration: none;
            transition: all 0.2s;
            border: none;
            cursor: pointer;
        }

        .btn-ghost {
            background: transparent;
            color: var(--dark);
        }

        .btn-ghost:hover {
            background: rgba(15, 118, 110, 0.08);
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(15, 118, 110, 0.3);
        }

        /* Main Content */
        main {
            flex: 1;
            padding: 48px 24px;
        }

        .container {
            max-width: 1000px;
            margin: 0 auto;
        }

        /* Prose Container for Legal Pages */
        .prose-container {
            background: white;
            border: 1px solid var(--border);
            border-radius: 20px;
            padding: 48px;
            max-width: 800px;
            margin: 0 auto;
        }

        .prose-container h1 {
            font-size: 36px;
            font-weight: 800;
            margin-bottom: 8px;
            color: var(--dark);
        }

        .prose-container .last-updated {
            color: var(--muted);
            font-size: 14px;
            margin-bottom: 40px;
            padding-bottom: 24px;
            border-bottom: 1px solid var(--border);
        }

        .prose-container section {
            margin-bottom: 32px;
        }

        .prose-container h2 {
            font-size: 22px;
            font-weight: 700;
            margin-bottom: 16px;
            color: var(--dark);
        }

        .prose-container h3 {
            font-size: 17px;
            font-weight: 600;
            margin: 20px 0 12px;
            color: var(--dark);
        }

        .prose-container p {
            color: #475569;
            line-height: 1.8;
            margin-bottom: 16px;
        }

        .prose-container ul,
        .prose-container ol {
            color: #475569;
            line-height: 1.8;
            margin-bottom: 16px;
            padding-left: 24px;
        }

        .prose-container li {
            margin-bottom: 8px;
        }

        .prose-container a {
            color: var(--primary);
            text-decoration: none;
        }

        .prose-container a:hover {
            text-decoration: underline;
        }

        /* Footer */
        footer {
            padding: 32px 0;
            border-top: 1px solid var(--border);
            background: white;
        }

        .footer-inner {
            max-width: 1280px;
            margin: 0 auto;
            padding: 0 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 20px;
        }

        .footer-copy {
            color: var(--muted);
            font-size: 14px;
        }

        .footer-links {
            display: flex;
            gap: 24px;
        }

        .footer-links a {
            color: var(--muted);
            text-decoration: none;
            font-size: 14px;
            transition: color 0.2s;
        }

        .footer-links a:hover {
            color: var(--primary);
        }

        @media (max-width: 640px) {
            .prose-container {
                padding: 24px;
            }

            .prose-container h1 {
                font-size: 28px;
            }
        }
    </style>
    @stack('styles')
</head>

<body>
    <!-- Navigation -->
    <nav>
        <div class="inner">
            <a href="/" class="logo">
                <div class="logo-icon">T</div>
                <span>Taisykla</span>
            </a>

            <div class="nav-buttons">
                <a href="{{ route('login') }}" class="btn btn-ghost">Sign In</a>
                <a href="{{ route('register') }}" class="btn btn-primary">Get Started</a>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main>
        <div class="container">
            @yield('content')
        </div>
    </main>

    <!-- Footer -->
    <footer>
        <div class="footer-inner">
            <div class="footer-copy">
                © {{ date('Y') }} {{ config('app.name', 'Taisykla') }}. All rights reserved.
            </div>
            <div class="footer-links">
                <a href="{{ route('privacy') }}">Privacy Policy</a>
                <a href="{{ route('terms') }}">Terms of Service</a>
                <a href="{{ route('support') }}">Support</a>
            </div>
        </div>
    </footer>

    @stack('scripts')
</body>

</html>