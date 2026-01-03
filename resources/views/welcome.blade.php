<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ config('app.name', 'Taisykla Field Ops') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=space-grotesk:400,500,600,700|ibm-plex-sans:400,500,600&display=swap" rel="stylesheet" />
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <style>
            :root {
                color-scheme: only light;
                --ink: #0c1b1a;
                --muted: #4f5d5b;
                --paper: #f7f3ea;
                --panel: #ffffff;
                --border: #e6dfd3;
                --teal: #0f766e;
                --teal-dark: #0b4f4a;
                --amber: #f59e0b;
                --rose: #ef4444;
                --sky: #0ea5e9;
                --mint: #dcf8f1;
            }

            * {
                box-sizing: border-box;
            }

            body {
                margin: 0;
                font-family: "IBM Plex Sans", "Segoe UI", sans-serif;
                color: var(--ink);
                background:
                    radial-gradient(900px 540px at 8% -10%, #ffe6c4 0%, transparent 60%),
                    radial-gradient(900px 540px at 90% -5%, #c8f4ec 0%, transparent 55%),
                    linear-gradient(180deg, #f7f3ea 0%, #f1efe7 100%);
            }

            body::before {
                content: "";
                position: fixed;
                inset: 0;
                background:
                    linear-gradient(120deg, rgba(12, 27, 26, 0.05) 1px, transparent 1px),
                    linear-gradient(0deg, rgba(12, 27, 26, 0.04) 1px, transparent 1px);
                background-size: 32px 32px;
                pointer-events: none;
                opacity: 0.3;
            }

            h1, h2, h3, .display {
                font-family: "Space Grotesk", "Segoe UI", sans-serif;
            }

            .page-shell {
                position: relative;
                max-width: 1200px;
                margin: 0 auto;
                padding: 48px 24px 88px;
                z-index: 1;
            }

            .hero {
                display: grid;
                gap: 24px;
                align-items: center;
            }

            .hero p {
                color: var(--muted);
                margin: 0;
            }

            .badge {
                display: inline-flex;
                align-items: center;
                gap: 8px;
                padding: 6px 12px;
                border-radius: 999px;
                font-size: 12px;
                text-transform: uppercase;
                letter-spacing: 0.12em;
                background: #0b4f4a;
                color: #ffffff;
            }

            .hero-actions {
                display: flex;
                flex-wrap: wrap;
                gap: 12px;
                margin-top: 18px;
            }

            .screen-grid {
                margin-top: 36px;
                display: grid;
                gap: 28px;
            }

            .screen {
                background: var(--panel);
                border-radius: 28px;
                border: 1px solid var(--border);
                box-shadow: 0 24px 60px rgba(12, 27, 26, 0.16);
                min-height: 720px;
                display: flex;
                flex-direction: column;
                position: relative;
                overflow: hidden;
                animation: lift 0.7s ease both;
            }

            .screen::after {
                content: "";
                position: absolute;
                inset: 0;
                border: 1px solid rgba(12, 27, 26, 0.05);
                border-radius: 28px;
                pointer-events: none;
            }

            .screen-header {
                padding: 18px 20px 0;
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 12px;
            }

            .screen-title {
                font-size: 12px;
                text-transform: uppercase;
                letter-spacing: 0.18em;
                color: var(--muted);
                font-weight: 600;
            }

            .screen-body {
                padding: 16px 20px 20px;
                display: flex;
                flex-direction: column;
                gap: 16px;
            }

            .screen-heading {
                font-size: 20px;
                margin: 0;
            }

            .muted {
                color: var(--muted);
                font-size: 13px;
            }

            .status-pill {
                padding: 6px 10px;
                border-radius: 999px;
                font-size: 12px;
                font-weight: 600;
                background: #0b4f4a;
                color: #ffffff;
            }

            .status-pill.offline {
                background: #3b1d1d;
                color: #ffd7d7;
            }

            .count-badge {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                min-width: 64px;
                padding: 6px 12px;
                border-radius: 14px;
                background: #fef3c7;
                color: #854d0e;
                font-weight: 600;
                font-size: 12px;
            }

            .pull-row {
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 12px;
                padding: 10px 12px;
                border-radius: 14px;
                background: #f9f7f2;
                border: 1px dashed #e9e2d6;
                font-size: 12px;
            }

            .segmented {
                display: grid;
                grid-template-columns: repeat(2, minmax(0, 1fr));
                gap: 6px;
                padding: 6px;
                border-radius: 14px;
                background: #f3efe6;
            }

            .segmented button {
                border: none;
                background: transparent;
                padding: 10px 12px;
                border-radius: 10px;
                font-weight: 600;
                color: var(--muted);
                cursor: pointer;
            }

            .segmented .active {
                background: #ffffff;
                color: var(--ink);
                box-shadow: 0 6px 16px rgba(12, 27, 26, 0.08);
            }

            .job-card {
                padding: 14px;
                border-radius: 18px;
                border: 1px solid #ece6da;
                background: #ffffff;
                border-left: 6px solid var(--amber);
                display: flex;
                flex-direction: column;
                gap: 12px;
            }

            .job-card.priority-high {
                border-left-color: var(--rose);
            }

            .job-card.priority-low {
                border-left-color: #10b981;
            }

            .job-main {
                display: flex;
                justify-content: space-between;
                gap: 16px;
            }

            .job-time {
                font-weight: 700;
                font-size: 18px;
            }

            .job-title {
                font-weight: 600;
                margin-top: 4px;
            }

            .job-desc {
                font-size: 13px;
                color: var(--muted);
            }

            .job-meta {
                font-size: 12px;
                color: var(--muted);
                margin-top: 6px;
            }

            .job-swipe {
                font-size: 12px;
                text-transform: uppercase;
                letter-spacing: 0.2em;
                color: #9a8f80;
                writing-mode: vertical-rl;
            }

            .chip-row {
                display: flex;
                flex-wrap: wrap;
                gap: 6px;
                margin-top: 8px;
            }

            .chip {
                display: inline-flex;
                align-items: center;
                gap: 6px;
                padding: 6px 10px;
                border-radius: 999px;
                font-size: 11px;
                background: #f2f6f5;
                color: #1f2937;
                font-weight: 600;
            }

            .chip.high {
                background: #fee2e2;
                color: #991b1b;
            }

            .chip.medium {
                background: #fef3c7;
                color: #92400e;
            }

            .chip.low {
                background: #dcfce7;
                color: #166534;
            }

            .swipe-actions {
                display: grid;
                grid-template-columns: repeat(3, minmax(0, 1fr));
                gap: 8px;
            }

            .section {
                padding: 14px;
                border-radius: 18px;
                background: #f9f6f0;
                border: 1px solid #ece6da;
                display: flex;
                flex-direction: column;
                gap: 12px;
            }

            .section-title {
                font-size: 11px;
                letter-spacing: 0.16em;
                text-transform: uppercase;
                color: var(--muted);
                font-weight: 700;
            }

            .row {
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 12px;
            }

            .btn {
                border: none;
                border-radius: 14px;
                padding: 10px 14px;
                font-weight: 600;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                gap: 8px;
                cursor: pointer;
                min-height: 44px;
            }

            .btn-primary {
                background: var(--teal);
                color: #ffffff;
            }

            .btn-secondary {
                background: #ffffff;
                border: 1px solid var(--border);
                color: var(--ink);
            }

            .btn-danger {
                background: var(--rose);
                color: #ffffff;
            }

            .btn-ghost {
                background: #f3efe6;
                color: #3f4a48;
            }

            .icon-btn {
                width: 44px;
                height: 44px;
                border-radius: 14px;
                border: 1px solid var(--border);
                background: #ffffff;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                cursor: pointer;
            }

            .icon-btn.primary {
                background: var(--teal);
                border-color: var(--teal);
                color: #ffffff;
            }

            .select {
                width: 100%;
                padding: 10px 12px;
                border-radius: 14px;
                border: 1px solid var(--border);
                background: #ffffff;
                font-weight: 600;
                min-height: 44px;
            }

            .photo-strip {
                display: grid;
                grid-template-columns: repeat(3, minmax(0, 1fr));
                gap: 8px;
            }

            .photo {
                border-radius: 12px;
                border: 1px dashed #d8d0c4;
                background: #ffffff;
                padding: 10px;
                text-align: center;
                font-size: 11px;
                color: var(--muted);
                min-height: 72px;
            }

            .camera-view {
                border-radius: 18px;
                border: 1px solid #d7d1c6;
                background: linear-gradient(135deg, #1f2937, #0f172a);
                color: #d1d5db;
                padding: 16px;
                min-height: 160px;
                display: flex;
                align-items: center;
                justify-content: center;
                text-align: center;
                font-size: 13px;
            }

            .progress {
                height: 8px;
                border-radius: 999px;
                background: #ebe6db;
                overflow: hidden;
            }

            .progress span {
                display: block;
                height: 100%;
                width: 68%;
                background: linear-gradient(90deg, #0f766e, #14b8a6);
            }

            .note-entry {
                border-radius: 14px;
                padding: 12px;
                border: 1px solid #e6dfd3;
                background: #ffffff;
            }

            .note-meta {
                font-size: 11px;
                text-transform: uppercase;
                letter-spacing: 0.12em;
                color: #7a6f63;
                font-weight: 600;
            }

            .input {
                width: 100%;
                border-radius: 14px;
                border: 1px solid var(--border);
                padding: 12px;
                font-size: 14px;
            }

            .parts-row {
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 12px;
                padding: 12px;
                border-radius: 14px;
                border: 1px solid #e7e1d6;
                background: #ffffff;
            }

            .qty-control {
                display: inline-flex;
                align-items: center;
                gap: 8px;
                background: #f3efe6;
                border-radius: 999px;
                padding: 4px 8px;
                font-weight: 600;
            }

            .timer {
                font-size: 32px;
                font-weight: 700;
                letter-spacing: 0.08em;
            }

            .summary-row {
                display: flex;
                align-items: center;
                justify-content: space-between;
                font-size: 13px;
                color: var(--muted);
            }

            .signature-pad {
                height: 140px;
                border-radius: 18px;
                border: 2px dashed #d6cdbf;
                background: #fffaf0;
                display: flex;
                align-items: center;
                justify-content: center;
                color: #9a8f80;
                font-size: 13px;
            }

            .rating {
                display: grid;
                grid-template-columns: repeat(5, minmax(0, 1fr));
                gap: 8px;
            }

            .rating button {
                border-radius: 12px;
                border: 1px solid var(--border);
                padding: 10px 0;
                font-weight: 700;
                background: #ffffff;
                cursor: pointer;
                min-height: 44px;
            }

            .bottom-nav {
                margin-top: auto;
                padding: 12px 16px 16px;
                border-top: 1px solid #efe8dc;
                background: rgba(255, 255, 255, 0.92);
                display: grid;
                grid-template-columns: repeat(4, minmax(0, 1fr));
                gap: 8px;
            }

            .nav-item {
                display: flex;
                flex-direction: column;
                align-items: center;
                gap: 4px;
                font-size: 11px;
                color: var(--muted);
                font-weight: 600;
                min-height: 44px;
                justify-content: center;
            }

            .nav-item.active {
                color: var(--ink);
            }

            .nav-icon {
                width: 28px;
                height: 28px;
                border-radius: 10px;
                background: #f1ede4;
                display: flex;
                align-items: center;
                justify-content: center;
                color: var(--ink);
            }

            .quick-actions {
                position: absolute;
                right: 16px;
                bottom: 88px;
                z-index: 2;
                display: flex;
                flex-direction: column-reverse;
                align-items: flex-end;
            }

            .quick-actions summary {
                list-style: none;
            }

            .quick-actions summary::-webkit-details-marker {
                display: none;
            }

            .fab {
                width: 54px;
                height: 54px;
                border-radius: 18px;
                background: var(--teal);
                color: #ffffff;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                font-weight: 700;
                box-shadow: 0 14px 30px rgba(15, 118, 110, 0.4);
            }

            .quick-menu {
                margin-bottom: 12px;
                width: 210px;
                background: #ffffff;
                border-radius: 18px;
                border: 1px solid #e5ded3;
                padding: 10px;
                display: grid;
                gap: 8px;
            }

            .quick-menu button {
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 8px;
                border: 1px solid #ede7db;
                background: #f9f7f2;
                border-radius: 12px;
                padding: 10px 12px;
                font-size: 12px;
                font-weight: 600;
                min-height: 44px;
                cursor: pointer;
            }

            .screen-grid section:nth-child(1) {
                animation-delay: 0.1s;
            }
            .screen-grid section:nth-child(2) {
                animation-delay: 0.2s;
            }
            .screen-grid section:nth-child(3) {
                animation-delay: 0.3s;
            }
            .screen-grid section:nth-child(4) {
                animation-delay: 0.4s;
            }
            .screen-grid section:nth-child(5) {
                animation-delay: 0.5s;
            }
            .screen-grid section:nth-child(6) {
                animation-delay: 0.6s;
            }
            .screen-grid section:nth-child(7) {
                animation-delay: 0.7s;
            }
            .screen-grid section:nth-child(8) {
                animation-delay: 0.8s;
            }

            @keyframes lift {
                from {
                    opacity: 0;
                    transform: translateY(18px);
                }
                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }

            @media (min-width: 900px) {
                .hero {
                    grid-template-columns: 1.2fr 1fr;
                }

                .screen-grid {
                    grid-template-columns: repeat(2, minmax(0, 1fr));
                }
            }

            @media (min-width: 1200px) {
                .screen-grid {
                    grid-template-columns: repeat(3, minmax(0, 1fr));
                }
            }
        </style>
    </head>
    <body class="antialiased">
        <div class="page-shell">
            <header class="hero">
                <div>
                    <span class="badge">Field Technician Mobile</span>
                    <h1 class="display" style="font-size: 40px; margin: 16px 0 12px;">Work order control built for one-hand use.</h1>
                    <p>High contrast, offline-first workflows, and large touch targets keep technicians moving without wasting data or time.</p>
                    <div class="hero-actions">
                        <a href="{{ route('login') }}" class="btn btn-primary">Launch Technician View</a>
                        <a href="{{ route('register') }}" class="btn btn-secondary">Invite New Tech</a>
                    </div>
                </div>
                <div class="section" style="background: #ffffff;">
                    <div class="section-title">Quick Actions Menu</div>
                    <div class="muted">Available from anywhere via the floating button.</div>
                    <div class="quick-menu">
                        <button type="button">Emergency support<span>Call</span></button>
                        <button type="button">Report a problem<span>Log</span></button>
                        <button type="button">Request parts delivery<span>ETA</span></button>
                        <button type="button">Check inventory<span>Stock</span></button>
                        <button type="button">View today schedule<span>Route</span></button>
                    </div>
                </div>
            </header>

            <main class="screen-grid">
                <section class="screen">
                    <div class="screen-header">
                        <span class="screen-title">Job List</span>
                        <span class="status-pill offline">Offline - 3 queued</span>
                    </div>
                    <div class="screen-body">
                        <div class="row">
                            <div>
                                <h2 class="screen-heading">Today's Route</h2>
                                <div class="muted">Wed, Zone East, data saver on</div>
                            </div>
                            <span class="count-badge">8 jobs</span>
                        </div>
                        <div class="pull-row">
                            <span>Pull to refresh</span>
                            <span class="muted">Auto-sync on reconnect</span>
                        </div>
                        <div class="segmented">
                            <button class="active">Time order</button>
                            <button>Route order</button>
                        </div>
                        <div class="job-card priority-high">
                            <div class="job-main">
                                <div>
                                    <div class="job-time">08:30</div>
                                    <div class="job-title">Arsenault Dental</div>
                                    <div class="job-desc">AC down, waiting room 30C</div>
                                    <div class="job-meta">12 Ridge St · 2.3 mi</div>
                                    <div class="chip-row">
                                        <span class="chip high">High</span>
                                        <span class="chip">Waiting customer</span>
                                    </div>
                                </div>
                                <div class="job-swipe">Swipe</div>
                            </div>
                            <div class="swipe-actions">
                                <button class="btn btn-secondary">Call</button>
                                <button class="btn btn-secondary">Navigate</button>
                                <button class="btn btn-primary">Start</button>
                            </div>
                        </div>
                        <div class="job-card">
                            <div class="job-main">
                                <div>
                                    <div class="job-time">10:15</div>
                                    <div class="job-title">Pinegrove Market</div>
                                    <div class="job-desc">POS terminals reboot loop</div>
                                    <div class="job-meta">88 Queen Ave · 4.9 mi</div>
                                    <div class="chip-row">
                                        <span class="chip medium">Medium</span>
                                        <span class="chip">En route</span>
                                    </div>
                                </div>
                                <div class="job-swipe">Swipe</div>
                            </div>
                            <div class="swipe-actions">
                                <button class="btn btn-secondary">Call</button>
                                <button class="btn btn-secondary">Navigate</button>
                                <button class="btn btn-primary">Start</button>
                            </div>
                        </div>
                        <div class="job-card priority-low">
                            <div class="job-main">
                                <div>
                                    <div class="job-time">13:40</div>
                                    <div class="job-title">Harbor Studios</div>
                                    <div class="job-desc">Printer calibration and test print</div>
                                    <div class="job-meta">21 Dock St · 6.1 mi</div>
                                    <div class="chip-row">
                                        <span class="chip low">Low</span>
                                        <span class="chip">Scheduled</span>
                                    </div>
                                </div>
                                <div class="job-swipe">Swipe</div>
                            </div>
                            <div class="swipe-actions">
                                <button class="btn btn-secondary">Call</button>
                                <button class="btn btn-secondary">Navigate</button>
                                <button class="btn btn-primary">Start</button>
                            </div>
                        </div>
                        <div class="section">
                            <div class="section-title">Offline mode</div>
                            <div class="muted">Local job data stored on device. Sync queue auto-replays.</div>
                            <div class="summary-row">
                                <span>3 updates queued</span>
                                <span>Conflicts: latest timestamp wins</span>
                            </div>
                        </div>
                    </div>
                    <details class="quick-actions">
                        <summary class="fab">QA</summary>
                        <div class="quick-menu">
                            <button type="button">Emergency support<span>Call</span></button>
                            <button type="button">Report a problem<span>Log</span></button>
                            <button type="button">Request parts delivery<span>ETA</span></button>
                            <button type="button">Check inventory<span>Stock</span></button>
                            <button type="button">View today schedule<span>Route</span></button>
                        </div>
                    </details>
                    <nav class="bottom-nav">
                        <div class="nav-item active">
                            <div class="nav-icon">J</div>
                            Jobs
                        </div>
                        <div class="nav-item">
                            <div class="nav-icon">M</div>
                            Map
                        </div>
                        <div class="nav-item">
                            <div class="nav-icon">N</div>
                            Notes
                        </div>
                        <div class="nav-item">
                            <div class="nav-icon">P</div>
                            Profile
                        </div>
                    </nav>
                </section>

                <section class="screen">
                    <div class="screen-header">
                        <span class="screen-title">Job Detail</span>
                        <span class="status-pill">Live status</span>
                    </div>
                    <div class="screen-body">
                        <div class="section" style="background: #ffffff;">
                            <div class="row">
                                <div>
                                    <h2 class="screen-heading">Nova Labs</h2>
                                    <button class="btn btn-ghost" type="button">742 Market Ave, Kaunas</button>
                                    <div class="muted">Tap for maps and directions</div>
                                </div>
                                <div style="display: grid; gap: 10px;">
                                    <button class="icon-btn primary" type="button" aria-label="Call customer">
                                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M22 16.9v3a2 2 0 0 1-2.2 2 19.8 19.8 0 0 1-8.6-3.1 19.5 19.5 0 0 1-6-6 19.8 19.8 0 0 1-3.1-8.6A2 2 0 0 1 4.1 2h3a2 2 0 0 1 2 1.7c.1 1 .3 2 .6 3a2 2 0 0 1-.4 2.1L8.2 10a16 16 0 0 0 6 6l1.2-1.1a2 2 0 0 1 2.1-.4c1 .3 2 .5 3 .6a2 2 0 0 1 1.7 2z"/>
                                        </svg>
                                    </button>
                                    <button class="icon-btn" type="button" aria-label="Message customer">
                                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M21 15a4 4 0 0 1-4 4H7l-4 3V7a4 4 0 0 1 4-4h10a4 4 0 0 1 4 4z"/>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                            <div>
                                <div class="section-title">Status</div>
                                <select class="select">
                                    <option>En route</option>
                                    <option>Arrived</option>
                                    <option>Working</option>
                                    <option>Awaiting approval</option>
                                </select>
                            </div>
                        </div>
                        <div class="section">
                            <div class="section-title">Problem Information</div>
                            <div>Server room overheating, alarm triggered twice in 24 hours.</div>
                            <div class="photo-strip">
                                <div class="photo">Before 01</div>
                                <div class="photo">Before 02</div>
                                <div class="photo">During</div>
                            </div>
                            <div class="muted">Pinch to zoom any photo</div>
                            <details>
                                <summary style="cursor: pointer; font-weight: 600;">Equipment details</summary>
                                <div class="muted" style="margin-top: 8px;">HVAC Model ZX-440, last serviced 3 months ago, filter warning active.</div>
                            </details>
                        </div>
                        <div class="section">
                            <div class="section-title">Navigation</div>
                            <div class="row">
                                <span class="muted">ETA 12 min · 4.2 mi</span>
                                <button class="btn btn-ghost">Copy address</button>
                            </div>
                            <button class="btn btn-primary">Navigate Here</button>
                        </div>
                        <div class="section">
                            <div class="section-title">Check-in and Status</div>
                            <div class="row">
                                <span class="muted">Timer</span>
                                <span class="timer">00:12:45</span>
                            </div>
                            <div class="row">
                                <button class="btn btn-primary" style="flex: 1;">Arrive at site</button>
                                <button class="btn btn-secondary" style="flex: 1;">Start work</button>
                            </div>
                            <button class="btn btn-danger">Request help</button>
                        </div>
                    </div>
                    <details class="quick-actions">
                        <summary class="fab">QA</summary>
                        <div class="quick-menu">
                            <button type="button">Emergency support<span>Call</span></button>
                            <button type="button">Report a problem<span>Log</span></button>
                            <button type="button">Request parts delivery<span>ETA</span></button>
                            <button type="button">Check inventory<span>Stock</span></button>
                            <button type="button">View today schedule<span>Route</span></button>
                        </div>
                    </details>
                    <nav class="bottom-nav">
                        <div class="nav-item active">
                            <div class="nav-icon">D</div>
                            Detail
                        </div>
                        <div class="nav-item">
                            <div class="nav-icon">P</div>
                            Photos
                        </div>
                        <div class="nav-item">
                            <div class="nav-icon">N</div>
                            Notes
                        </div>
                        <div class="nav-item">
                            <div class="nav-icon">T</div>
                            Timer
                        </div>
                    </nav>
                </section>

                <section class="screen">
                    <div class="screen-header">
                        <span class="screen-title">Photo Capture</span>
                        <span class="status-pill">Upload 68%</span>
                    </div>
                    <div class="screen-body">
                        <div class="camera-view">Camera ready. Tap to capture in-app.</div>
                        <div class="row">
                            <div class="chip-row">
                                <span class="chip high">Before</span>
                                <span class="chip">During</span>
                                <span class="chip">After</span>
                            </div>
                            <button class="btn btn-secondary">Delete</button>
                        </div>
                        <div class="section">
                            <div class="section-title">Annotation tools</div>
                            <div class="row">
                                <button class="btn btn-secondary">Draw</button>
                                <button class="btn btn-secondary">Arrow</button>
                                <button class="btn btn-secondary">Blur</button>
                                <button class="btn btn-secondary">Text</button>
                            </div>
                        </div>
                        <div class="section">
                            <div class="section-title">Captured Photos</div>
                            <div class="photo-strip">
                                <div class="photo">Before 01</div>
                                <div class="photo">During 01</div>
                                <div class="photo">After 01</div>
                            </div>
                            <div class="progress"><span></span></div>
                            <div class="muted">Uploading 5 of 8, queued if offline</div>
                        </div>
                    </div>
                    <details class="quick-actions">
                        <summary class="fab">QA</summary>
                        <div class="quick-menu">
                            <button type="button">Emergency support<span>Call</span></button>
                            <button type="button">Report a problem<span>Log</span></button>
                            <button type="button">Request parts delivery<span>ETA</span></button>
                            <button type="button">Check inventory<span>Stock</span></button>
                            <button type="button">View today schedule<span>Route</span></button>
                        </div>
                    </details>
                    <nav class="bottom-nav">
                        <div class="nav-item">
                            <div class="nav-icon">J</div>
                            Jobs
                        </div>
                        <div class="nav-item active">
                            <div class="nav-icon">P</div>
                            Photos
                        </div>
                        <div class="nav-item">
                            <div class="nav-icon">N</div>
                            Notes
                        </div>
                        <div class="nav-item">
                            <div class="nav-icon">S</div>
                            Sync
                        </div>
                    </nav>
                </section>

                <section class="screen">
                    <div class="screen-header">
                        <span class="screen-title">Work Notes</span>
                        <span class="status-pill">Voice ready</span>
                    </div>
                    <div class="screen-body">
                        <div class="section">
                            <div class="section-title">Voice to text</div>
                            <div class="row">
                                <textarea class="input" rows="3" placeholder="Dictate notes or type here."></textarea>
                                <button class="icon-btn primary" type="button" aria-label="Start voice input">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M12 1a3 3 0 0 0-3 3v8a3 3 0 1 0 6 0V4a3 3 0 0 0-3-3z"/>
                                        <path d="M19 10v2a7 7 0 0 1-14 0v-2"/>
                                        <path d="M12 19v4"/>
                                    </svg>
                                </button>
                            </div>
                            <div class="chip-row">
                                <span class="chip">Checked power supply</span>
                                <span class="chip">Replaced filter</span>
                                <span class="chip">Ran diagnostics</span>
                            </div>
                        </div>
                        <div class="section">
                            <div class="section-title">Categories</div>
                            <div class="segmented">
                                <button class="active">Diagnosis</button>
                                <button>Repair</button>
                            </div>
                            <div class="segmented">
                                <button>Testing</button>
                                <button>Travel</button>
                            </div>
                        </div>
                        <div class="section">
                            <div class="section-title">Time-stamped entries</div>
                            <div class="note-entry">
                                <div class="note-meta">10:26 AM · Diagnosis</div>
                                <div>Thermostat showing error E07, fan not spinning.</div>
                            </div>
                            <div class="note-entry">
                                <div class="note-meta">11:02 AM · Repair</div>
                                <div>Replaced fan relay and reseated control board.</div>
                            </div>
                        </div>
                        <div class="section">
                            <div class="section-title">Attachments</div>
                            <div class="row">
                                <button class="btn btn-secondary">Add photo</button>
                                <button class="btn btn-secondary">Attach PDF</button>
                            </div>
                        </div>
                    </div>
                    <details class="quick-actions">
                        <summary class="fab">QA</summary>
                        <div class="quick-menu">
                            <button type="button">Emergency support<span>Call</span></button>
                            <button type="button">Report a problem<span>Log</span></button>
                            <button type="button">Request parts delivery<span>ETA</span></button>
                            <button type="button">Check inventory<span>Stock</span></button>
                            <button type="button">View today schedule<span>Route</span></button>
                        </div>
                    </details>
                    <nav class="bottom-nav">
                        <div class="nav-item">
                            <div class="nav-icon">J</div>
                            Jobs
                        </div>
                        <div class="nav-item active">
                            <div class="nav-icon">N</div>
                            Notes
                        </div>
                        <div class="nav-item">
                            <div class="nav-icon">P</div>
                            Photos
                        </div>
                        <div class="nav-item">
                            <div class="nav-icon">T</div>
                            Timer
                        </div>
                    </nav>
                </section>

                <section class="screen">
                    <div class="screen-header">
                        <span class="screen-title">Parts Usage</span>
                        <span class="status-pill">Inventory live</span>
                    </div>
                    <div class="screen-body">
                        <div class="section">
                            <div class="section-title">Barcode Scanner</div>
                            <button class="btn btn-primary">Scan part barcode</button>
                            <div class="muted">Offline scan queues lookup for sync.</div>
                        </div>
                        <div class="section">
                            <div class="section-title">Favorites</div>
                            <div class="chip-row">
                                <span class="chip">Filter 24x24</span>
                                <span class="chip">Relay Kit</span>
                                <span class="chip">Thermo Sensor</span>
                            </div>
                            <input class="input" type="search" placeholder="Search parts catalog" />
                        </div>
                        <div class="parts-row">
                            <div>
                                <div style="font-weight: 600;">Fan Relay R-88</div>
                                <div class="muted">In stock: 12</div>
                            </div>
                            <div class="qty-control">
                                <button class="icon-btn" type="button">-</button>
                                <span>2</span>
                                <button class="icon-btn primary" type="button">+</button>
                            </div>
                        </div>
                        <div class="parts-row">
                            <div>
                                <div style="font-weight: 600;">Filter Pack 24x24</div>
                                <div class="muted">Low stock: 3</div>
                            </div>
                            <div class="qty-control">
                                <button class="icon-btn" type="button">-</button>
                                <span>1</span>
                                <button class="icon-btn primary" type="button">+</button>
                            </div>
                        </div>
                        <div class="section">
                            <div class="row">
                                <span class="section-title">Running total</span>
                                <span style="font-weight: 700;">$148.00</span>
                            </div>
                            <button class="btn btn-secondary">Add to work order</button>
                        </div>
                    </div>
                    <details class="quick-actions">
                        <summary class="fab">QA</summary>
                        <div class="quick-menu">
                            <button type="button">Emergency support<span>Call</span></button>
                            <button type="button">Report a problem<span>Log</span></button>
                            <button type="button">Request parts delivery<span>ETA</span></button>
                            <button type="button">Check inventory<span>Stock</span></button>
                            <button type="button">View today schedule<span>Route</span></button>
                        </div>
                    </details>
                    <nav class="bottom-nav">
                        <div class="nav-item">
                            <div class="nav-icon">J</div>
                            Jobs
                        </div>
                        <div class="nav-item active">
                            <div class="nav-icon">P</div>
                            Parts
                        </div>
                        <div class="nav-item">
                            <div class="nav-icon">C</div>
                            Cart
                        </div>
                        <div class="nav-item">
                            <div class="nav-icon">I</div>
                            Inventory
                        </div>
                    </nav>
                </section>

                <section class="screen">
                    <div class="screen-header">
                        <span class="screen-title">Time Tracking</span>
                        <span class="status-pill">Live timer</span>
                    </div>
                    <div class="screen-body">
                        <div class="section">
                            <div class="section-title">Activity</div>
                            <div class="segmented">
                                <button class="active">Diagnosis</button>
                                <button>Repair</button>
                            </div>
                            <div class="segmented">
                                <button>Travel</button>
                                <button>Break</button>
                            </div>
                        </div>
                        <div class="section" style="align-items: center; text-align: center;">
                            <div class="timer">01:24:18</div>
                            <div class="muted">Breaks auto-logged</div>
                            <div class="row" style="width: 100%; margin-top: 12px;">
                                <button class="btn btn-primary" style="flex: 1;">Start</button>
                                <button class="btn btn-secondary" style="flex: 1;">Pause</button>
                                <button class="btn btn-danger" style="flex: 1;">Stop</button>
                            </div>
                        </div>
                        <div class="section">
                            <div class="section-title">Daily summary</div>
                            <div class="summary-row">
                                <span>Diagnosis</span>
                                <span>1h 42m</span>
                            </div>
                            <div class="summary-row">
                                <span>Repair</span>
                                <span>2h 18m</span>
                            </div>
                            <div class="summary-row">
                                <span>Travel</span>
                                <span>52m</span>
                            </div>
                        </div>
                        <div class="section">
                            <div class="section-title">Manual entry</div>
                            <button class="btn btn-secondary">Add manual time</button>
                        </div>
                    </div>
                    <details class="quick-actions">
                        <summary class="fab">QA</summary>
                        <div class="quick-menu">
                            <button type="button">Emergency support<span>Call</span></button>
                            <button type="button">Report a problem<span>Log</span></button>
                            <button type="button">Request parts delivery<span>ETA</span></button>
                            <button type="button">Check inventory<span>Stock</span></button>
                            <button type="button">View today schedule<span>Route</span></button>
                        </div>
                    </details>
                    <nav class="bottom-nav">
                        <div class="nav-item">
                            <div class="nav-icon">J</div>
                            Jobs
                        </div>
                        <div class="nav-item">
                            <div class="nav-icon">N</div>
                            Notes
                        </div>
                        <div class="nav-item active">
                            <div class="nav-icon">T</div>
                            Timer
                        </div>
                        <div class="nav-item">
                            <div class="nav-icon">S</div>
                            Summary
                        </div>
                    </nav>
                </section>

                <section class="screen">
                    <div class="screen-header">
                        <span class="screen-title">Customer Sign-off</span>
                        <span class="status-pill">Ready</span>
                    </div>
                    <div class="screen-body">
                        <div class="section">
                            <div class="section-title">Work summary</div>
                            <div class="summary-row">
                                <span>Replaced fan relay</span>
                                <span>2 parts</span>
                            </div>
                            <div class="summary-row">
                                <span>Cleaned filters</span>
                                <span>1.5h labor</span>
                            </div>
                            <div class="summary-row">
                                <span>System test</span>
                                <span>Passed</span>
                            </div>
                        </div>
                        <div class="section">
                            <div class="section-title">Signature pad</div>
                            <div class="signature-pad">Draw signature here</div>
                            <button class="btn btn-secondary">Clear signature</button>
                        </div>
                        <div class="section">
                            <div class="section-title">Satisfaction rating</div>
                            <div class="rating">
                                <button type="button">1</button>
                                <button type="button">2</button>
                                <button type="button">3</button>
                                <button type="button">4</button>
                                <button type="button">5</button>
                            </div>
                            <textarea class="input" rows="3" placeholder="Additional comments"></textarea>
                        </div>
                        <button class="btn btn-primary">Submit completion</button>
                    </div>
                    <details class="quick-actions">
                        <summary class="fab">QA</summary>
                        <div class="quick-menu">
                            <button type="button">Emergency support<span>Call</span></button>
                            <button type="button">Report a problem<span>Log</span></button>
                            <button type="button">Request parts delivery<span>ETA</span></button>
                            <button type="button">Check inventory<span>Stock</span></button>
                            <button type="button">View today schedule<span>Route</span></button>
                        </div>
                    </details>
                    <nav class="bottom-nav">
                        <div class="nav-item">
                            <div class="nav-icon">J</div>
                            Jobs
                        </div>
                        <div class="nav-item">
                            <div class="nav-icon">S</div>
                            Sign
                        </div>
                        <div class="nav-item active">
                            <div class="nav-icon">C</div>
                            Complete
                        </div>
                        <div class="nav-item">
                            <div class="nav-icon">A</div>
                            Archive
                        </div>
                    </nav>
                </section>
            </main>
        </div>
    </body>
</html>
