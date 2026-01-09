<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Taisykla') }}</title>

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
        }

        body {
            font-family: 'Inter', -apple-system, sans-serif;
            margin: 0;
            min-height: 100vh;
            background: linear-gradient(135deg, #f8fafc 0%, #e0f2fe 50%, #f0fdfa 100%);
        }

        .auth-container {
            min-height: 100vh;
            display: flex;
        }

        /* Left Panel - Branding */
        .auth-brand {
            display: none;
            width: 50%;
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            padding: 48px;
            position: relative;
            overflow: hidden;
        }

        .auth-brand::before {
            content: '';
            position: absolute;
            top: -30%;
            right: -20%;
            width: 80%;
            height: 160%;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.08) 0%, transparent 60%);
            pointer-events: none;
        }

        .auth-brand::after {
            content: '';
            position: absolute;
            bottom: -20%;
            left: -10%;
            width: 60%;
            height: 80%;
            background: radial-gradient(circle, rgba(20, 184, 166, 0.2) 0%, transparent 50%);
            pointer-events: none;
        }

        .brand-content {
            position: relative;
            z-index: 1;
            height: 100%;
            display: flex;
            flex-direction: column;
        }

        .brand-logo {
            display: flex;
            align-items: center;
            gap: 12px;
            color: white;
            font-weight: 800;
            font-size: 24px;
            text-decoration: none;
        }

        .brand-logo-icon {
            width: 44px;
            height: 44px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
        }

        .brand-main {
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
            max-width: 400px;
        }

        .brand-main h1 {
            font-size: 36px;
            font-weight: 800;
            color: white;
            line-height: 1.2;
            margin: 0 0 16px;
        }

        .brand-main p {
            color: rgba(255, 255, 255, 0.8);
            font-size: 17px;
            line-height: 1.7;
            margin: 0;
        }

        .brand-features {
            margin-top: 40px;
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        .brand-feature {
            display: flex;
            align-items: center;
            gap: 12px;
            color: rgba(255, 255, 255, 0.9);
            font-size: 15px;
        }

        .brand-feature svg {
            width: 20px;
            height: 20px;
            flex-shrink: 0;
        }

        /* Right Panel - Form */
        .auth-form-panel {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 40px 24px;
        }

        .auth-form-container {
            width: 100%;
            max-width: 420px;
        }

        .auth-header {
            text-align: center;
            margin-bottom: 32px;
        }

        .auth-header .mobile-logo {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            margin-bottom: 24px;
            text-decoration: none;
        }

        .auth-header .mobile-logo-icon {
            width: 48px;
            height: 48px;
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 700;
            font-size: 20px;
            box-shadow: 0 4px 14px rgba(15, 118, 110, 0.3);
        }

        .auth-header .mobile-logo span {
            font-size: 24px;
            font-weight: 800;
            color: var(--primary);
        }

        .auth-header h2 {
            font-size: 28px;
            font-weight: 800;
            color: var(--dark);
            margin: 0 0 8px;
        }

        .auth-header p {
            color: var(--muted);
            font-size: 15px;
            margin: 0;
        }

        .auth-card {
            background: white;
            border-radius: 20px;
            padding: 32px;
            border: 1px solid var(--border);
            box-shadow: 0 4px 24px rgba(15, 23, 42, 0.06);
        }

        /* Form Styles */
        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            display: block;
            font-weight: 600;
            font-size: 14px;
            color: var(--dark);
            margin-bottom: 8px;
        }

        .form-input {
            width: 100%;
            padding: 14px 16px;
            border: 1px solid var(--border);
            border-radius: 12px;
            font-size: 15px;
            transition: all 0.2s;
            background: white;
        }

        .form-input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(15, 118, 110, 0.1);
        }

        .form-input::placeholder {
            color: #94a3b8;
        }

        .form-error {
            color: #dc2626;
            font-size: 13px;
            margin-top: 6px;
        }

        .form-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 24px;
        }

        .checkbox-label {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
            color: var(--dark);
            cursor: pointer;
        }

        .checkbox-label input {
            width: 18px;
            height: 18px;
            border-radius: 4px;
            accent-color: var(--primary);
        }

        .link {
            color: var(--primary);
            font-size: 14px;
            font-weight: 500;
            text-decoration: none;
            transition: color 0.2s;
        }

        .link:hover {
            color: var(--primary-dark);
            text-decoration: underline;
        }

        .btn-primary {
            width: 100%;
            padding: 14px 24px;
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            color: white;
            font-weight: 600;
            font-size: 15px;
            border: none;
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .btn-primary:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 20px rgba(15, 118, 110, 0.35);
        }

        .btn-primary:disabled {
            opacity: 0.7;
            cursor: not-allowed;
            transform: none;
        }

        .auth-footer {
            text-align: center;
            margin-top: 24px;
            font-size: 14px;
            color: var(--muted);
        }

        .auth-footer a {
            color: var(--primary);
            font-weight: 600;
            text-decoration: none;
        }

        .auth-footer a:hover {
            text-decoration: underline;
        }

        /* Demo Users Section */
        .demo-section {
            margin-top: 24px;
            padding-top: 24px;
            border-top: 1px solid var(--border);
        }

        .demo-header {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 12px;
        }

        .demo-header svg {
            width: 18px;
            height: 18px;
            color: var(--primary);
        }

        .demo-header h3 {
            font-size: 14px;
            font-weight: 600;
            color: var(--dark);
            margin: 0;
        }

        .demo-note {
            font-size: 12px;
            color: var(--muted);
            margin-bottom: 12px;
        }

        .demo-note code {
            background: #f1f5f9;
            padding: 2px 6px;
            border-radius: 4px;
            font-family: monospace;
            color: var(--dark);
        }

        .demo-grid {
            display: flex;
            flex-direction: column;
            gap: 8px;
            max-height: 280px;
            overflow-y: auto;
        }

        .demo-user {
            padding: 12px 14px;
            background: #f8fafc;
            border: 1px solid var(--border);
            border-radius: 10px;
            font-size: 13px;
        }

        .demo-user-role {
            font-weight: 600;
            color: var(--primary);
            margin-bottom: 6px;
        }

        .demo-user-info {
            display: flex;
            flex-direction: column;
            gap: 2px;
            color: var(--muted);
        }

        .demo-user-info span {
            color: var(--dark);
            font-weight: 500;
        }

        .demo-user-info code {
            font-family: monospace;
            font-size: 12px;
        }

        /* Password Requirements */
        .password-requirements {
            margin-top: 8px;
            font-size: 12px;
            color: var(--muted);
        }

        .password-requirements ul {
            margin: 4px 0 0;
            padding-left: 16px;
        }

        /* Status Messages */
        .status-message {
            padding: 12px 16px;
            border-radius: 10px;
            margin-bottom: 16px;
            font-size: 14px;
        }

        .status-message.success {
            background: #dcfce7;
            color: #166534;
            border: 1px solid #86efac;
        }

        .status-message.error {
            background: #fee2e2;
            color: #991b1b;
            border: 1px solid #fca5a5;
        }

        @media (min-width: 1024px) {
            .auth-brand {
                display: flex;
                flex-direction: column;
            }

            .auth-header .mobile-logo {
                display: none;
            }
        }
    </style>
</head>

<body>
    <div class="auth-container">
        <!-- Left Brand Panel -->
        <div class="auth-brand">
            <div class="brand-content">
                <a href="/" class="brand-logo">
                    <div class="brand-logo-icon">T</div>
                    <span>Taisykla</span>
                </a>

                <div class="brand-main">
                    <h1>Manage your field operations with confidence</h1>
                    <p>Join thousands of service companies using Taisykla to streamline their equipment maintenance,
                        scheduling, and technician management.</p>

                    <div class="brand-features">
                        <div class="brand-feature">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M5 13l4 4L19 7" />
                            </svg>
                            <span>Real-time technician tracking</span>
                        </div>
                        <div class="brand-feature">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M5 13l4 4L19 7" />
                            </svg>
                            <span>Intelligent scheduling & dispatch</span>
                        </div>
                        <div class="brand-feature">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M5 13l4 4L19 7" />
                            </svg>
                            <span>Complete equipment lifecycle management</span>
                        </div>
                        <div class="brand-feature">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M5 13l4 4L19 7" />
                            </svg>
                            <span>Mobile-first for field technicians</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Form Panel -->
        <div class="auth-form-panel">
            <div class="auth-form-container">
                {{ $slot }}
            </div>
        </div>
    </div>
</body>

</html>