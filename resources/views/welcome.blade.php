<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Taisykla') }} - Equipment Maintenance Management</title>
    <meta name="description"
        content="Streamline your field service operations with intelligent scheduling, real-time tracking, and comprehensive equipment management.">

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800&display=swap" rel="stylesheet" />
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
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Inter', -apple-system, sans-serif;
            color: var(--dark);
            background: linear-gradient(135deg, #f8fafc 0%, #e0f2fe 50%, #f0fdfa 100%);
            min-height: 100vh;
            overflow-x: hidden;
        }

        /* Animated background */
        .bg-pattern {
            position: fixed;
            inset: 0;
            background-image:
                radial-gradient(circle at 20% 20%, rgba(14, 165, 233, 0.08) 0%, transparent 50%),
                radial-gradient(circle at 80% 80%, rgba(20, 184, 166, 0.08) 0%, transparent 50%),
                radial-gradient(circle at 40% 60%, rgba(245, 158, 11, 0.05) 0%, transparent 40%);
            pointer-events: none;
            z-index: -1;
        }

        .container {
            max-width: 1280px;
            margin: 0 auto;
            padding: 0 24px;
        }

        /* Navigation */
        nav {
            padding: 20px 0;
            position: relative;
            z-index: 100;
        }

        nav .inner {
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
            box-shadow: 0 4px 14px rgba(15, 118, 110, 0.3);
        }

        .nav-links {
            display: flex;
            align-items: center;
            gap: 32px;
        }

        .nav-links a {
            color: var(--muted);
            text-decoration: none;
            font-weight: 500;
            font-size: 15px;
            transition: color 0.2s;
        }

        .nav-links a:hover {
            color: var(--dark);
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
            padding: 12px 24px;
            border-radius: 12px;
            font-weight: 600;
            font-size: 15px;
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
            box-shadow: 0 4px 14px rgba(15, 118, 110, 0.35);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(15, 118, 110, 0.4);
        }

        .btn-secondary {
            background: white;
            color: var(--dark);
            border: 1px solid var(--border);
        }

        .btn-secondary:hover {
            background: var(--light);
            border-color: var(--primary);
        }

        .btn-lg {
            padding: 16px 32px;
            font-size: 16px;
            border-radius: 14px;
        }

        /* Hero Section */
        .hero {
            padding: 80px 0 100px;
            text-align: center;
        }

        .hero-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 16px;
            background: rgba(15, 118, 110, 0.1);
            border: 1px solid rgba(15, 118, 110, 0.2);
            border-radius: 999px;
            color: var(--primary);
            font-size: 13px;
            font-weight: 600;
            margin-bottom: 24px;
        }

        .hero-badge svg {
            width: 16px;
            height: 16px;
        }

        .hero h1 {
            font-size: clamp(40px, 6vw, 64px);
            font-weight: 800;
            line-height: 1.1;
            letter-spacing: -0.02em;
            margin-bottom: 24px;
            background: linear-gradient(135deg, var(--dark) 0%, #334155 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .hero h1 span {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
            -webkit-background-clip: text;
            background-clip: text;
        }

        .hero p {
            font-size: 20px;
            color: var(--muted);
            max-width: 640px;
            margin: 0 auto 40px;
            line-height: 1.7;
        }

        .hero-buttons {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 16px;
            flex-wrap: wrap;
        }

        /* Stats */
        .stats {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 48px;
            margin-top: 60px;
            flex-wrap: wrap;
        }

        .stat {
            text-align: center;
        }

        .stat-value {
            font-size: 32px;
            font-weight: 800;
            color: var(--primary);
        }

        .stat-label {
            font-size: 14px;
            color: var(--muted);
            margin-top: 4px;
        }

        /* Features Grid */
        .features {
            padding: 100px 0;
        }

        .section-header {
            text-align: center;
            margin-bottom: 60px;
        }

        .section-label {
            font-size: 13px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            color: var(--primary);
            margin-bottom: 16px;
        }

        .section-title {
            font-size: 40px;
            font-weight: 800;
            margin-bottom: 16px;
        }

        .section-desc {
            font-size: 18px;
            color: var(--muted);
            max-width: 560px;
            margin: 0 auto;
        }

        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
            gap: 24px;
        }

        .feature-card {
            background: white;
            border: 1px solid var(--border);
            border-radius: 20px;
            padding: 32px;
            transition: all 0.3s;
        }

        .feature-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 20px 40px rgba(15, 23, 42, 0.08);
            border-color: var(--primary);
        }

        .feature-icon {
            width: 56px;
            height: 56px;
            background: linear-gradient(135deg, rgba(15, 118, 110, 0.1) 0%, rgba(20, 184, 166, 0.15) 100%);
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 20px;
            color: var(--primary);
        }

        .feature-title {
            font-size: 20px;
            font-weight: 700;
            margin-bottom: 12px;
        }

        .feature-desc {
            color: var(--muted);
            line-height: 1.6;
        }

        /* CTA Section */
        .cta {
            padding: 80px 0 120px;
        }

        .cta-card {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            border-radius: 32px;
            padding: 64px;
            text-align: center;
            color: white;
            position: relative;
            overflow: hidden;
        }

        .cta-card::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -20%;
            width: 60%;
            height: 200%;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.1) 0%, transparent 60%);
            pointer-events: none;
        }

        .cta-title {
            font-size: 36px;
            font-weight: 800;
            margin-bottom: 16px;
        }

        .cta-desc {
            font-size: 18px;
            opacity: 0.9;
            max-width: 480px;
            margin: 0 auto 32px;
        }

        .cta .btn-secondary {
            background: white;
            color: var(--primary);
        }

        .cta .btn-secondary:hover {
            background: var(--light);
        }

        /* Footer */
        footer {
            padding: 40px 0;
            border-top: 1px solid var(--border);
            background: white;
        }

        .footer-inner {
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

        @media (max-width: 768px) {
            .nav-links {
                display: none;
            }

            .hero {
                padding: 60px 0 80px;
            }

            .stats {
                gap: 32px;
            }

            .features {
                padding: 60px 0;
            }

            .cta-card {
                padding: 40px 24px;
            }
        }
    </style>
</head>

<body>
    <div class="bg-pattern"></div>

    <!-- Navigation -->
    <nav>
        <div class="container inner">
            <a href="/" class="logo">
                <div class="logo-icon">T</div>
                <span>Taisykla</span>
            </a>

            <div class="nav-links">
                <a href="#features">Features</a>
                <a href="#pricing">Pricing</a>
                <a href="#about">About</a>
                <a href="#contact">Contact</a>
            </div>

            <div class="nav-buttons">
                <a href="{{ route('login') }}" class="btn btn-ghost">Sign In</a>
                <a href="{{ route('register') }}" class="btn btn-primary">Get Started</a>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero">
        <div class="container">
            <div class="hero-badge">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M13 10V3L4 14h7v7l9-11h-7z" />
                </svg>
                Trusted by 500+ service companies
            </div>

            <h1>
                Equipment maintenance<br>
                <span>made simple</span>
            </h1>

            <p>
                Streamline your field service operations with intelligent scheduling,
                real-time technician tracking, and comprehensive equipment lifecycle management.
            </p>

            <div class="hero-buttons">
                <a href="{{ route('register') }}" class="btn btn-primary btn-lg">
                    Start Free Trial
                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17 8l4 4m0 0l-4 4m4-4H3" />
                    </svg>
                </a>
                <a href="#demo" class="btn btn-secondary btn-lg">
                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Watch Demo
                </a>
            </div>

            <div class="stats">
                <div class="stat">
                    <div class="stat-value">98%</div>
                    <div class="stat-label">Customer Satisfaction</div>
                </div>
                <div class="stat">
                    <div class="stat-value">40%</div>
                    <div class="stat-label">Faster Response Times</div>
                </div>
                <div class="stat">
                    <div class="stat-value">25K+</div>
                    <div class="stat-label">Work Orders Completed</div>
                </div>
                <div class="stat">
                    <div class="stat-value">24/7</div>
                    <div class="stat-label">Monitoring & Support</div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="features" id="features">
        <div class="container">
            <div class="section-header">
                <div class="section-label">Features</div>
                <h2 class="section-title">Everything you need to manage field operations</h2>
                <p class="section-desc">Powerful tools designed for service companies of all sizes.</p>
            </div>

            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon">
                        <svg width="28" height="28" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                        </svg>
                    </div>
                    <h3 class="feature-title">Work Order Management</h3>
                    <p class="feature-desc">Create, assign, and track work orders from request to completion with
                        real-time status updates.</p>
                </div>

                <div class="feature-card">
                    <div class="feature-icon">
                        <svg width="28" height="28" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                    </div>
                    <h3 class="feature-title">Real-Time Tracking</h3>
                    <p class="feature-desc">Track technician locations and job progress in real-time. Customers receive
                        automatic ETA updates.</p>
                </div>

                <div class="feature-card">
                    <div class="feature-icon">
                        <svg width="28" height="28" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <h3 class="feature-title">Smart Scheduling</h3>
                    <p class="feature-desc">AI-powered scheduling optimizes routes and assigns the right technician
                        based on skills and availability.</p>
                </div>

                <div class="feature-card">
                    <div class="feature-icon">
                        <svg width="28" height="28" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z" />
                        </svg>
                    </div>
                    <h3 class="feature-title">Equipment Lifecycle</h3>
                    <p class="feature-desc">Track equipment from purchase to retirement. Manage warranties, maintenance
                        schedules, and service history.</p>
                </div>

                <div class="feature-card">
                    <div class="feature-icon">
                        <svg width="28" height="28" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                        </svg>
                    </div>
                    <h3 class="feature-title">Inventory Control</h3>
                    <p class="feature-desc">Manage parts inventory across locations. Automatic reorder alerts and usage
                        tracking.</p>
                </div>

                <div class="feature-card">
                    <div class="feature-icon">
                        <svg width="28" height="28" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                    </div>
                    <h3 class="feature-title">Analytics & Reports</h3>
                    <p class="feature-desc">Gain insights with comprehensive dashboards. Track KPIs, technician
                        performance, and customer satisfaction.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="cta">
        <div class="container">
            <div class="cta-card">
                <h2 class="cta-title">Ready to transform your operations?</h2>
                <p class="cta-desc">Join hundreds of service companies that trust Taisykla to manage their field
                    operations.</p>
                <div class="hero-buttons">
                    <a href="{{ route('register') }}" class="btn btn-secondary btn-lg">Start Your Free Trial</a>
                    <a href="{{ route('login') }}" class="btn btn-ghost btn-lg"
                        style="color: white; border: 1px solid rgba(255,255,255,0.3);">Sign In</a>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer>
        <div class="container footer-inner">
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
</body>

</html>