@extends('layouts.guest-page')

@section('title', 'Support')

@section('content')
    <div class="support-page">
        <div class="support-header">
            <h1>How can we help?</h1>
            <p>Get the support you need to keep your operations running smoothly.</p>
        </div>

        <div class="support-grid">
            <!-- Quick Help Cards -->
            <div class="help-card">
                <div class="help-icon">
                    <svg width="32" height="32" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                    </svg>
                </div>
                <h3>Documentation</h3>
                <p>Browse our comprehensive guides and tutorials to learn how to use every feature.</p>
                <a href="#" class="help-link">View Documentation →</a>
            </div>

            <div class="help-card">
                <div class="help-icon">
                    <svg width="32" height="32" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <h3>FAQ</h3>
                <p>Find quick answers to frequently asked questions about billing, features, and more.</p>
                <a href="#faq" class="help-link">View FAQ →</a>
            </div>

            <div class="help-card">
                <div class="help-icon">
                    <svg width="32" height="32" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                    </svg>
                </div>
                <h3>Video Tutorials</h3>
                <p>Watch step-by-step video guides for common tasks and advanced features.</p>
                <a href="#" class="help-link">Watch Videos →</a>
            </div>

            <div class="help-card highlight">
                <div class="help-icon">
                    <svg width="32" height="32" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                    </svg>
                </div>
                <h3>Contact Support</h3>
                <p>Can't find what you're looking for? Our support team is here to help.</p>
                <a href="mailto:support@{{ parse_url(config('app.url'), PHP_URL_HOST) ?: 'example.com' }}"
                    class="help-link">Email Support →</a>
            </div>
        </div>

        <!-- Contact Form -->
        <div class="contact-section">
            <h2>Send us a message</h2>
            <p>Fill out the form below and we'll get back to you within 24 hours.</p>

            <form class="contact-form" action="#" method="POST">
                @csrf
                <div class="form-row">
                    <div class="form-group">
                        <label for="name">Name</label>
                        <input type="text" id="name" name="name" required placeholder="Your name">
                    </div>
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" required placeholder="you@example.com">
                    </div>
                </div>

                <div class="form-group">
                    <label for="subject">Subject</label>
                    <select id="subject" name="subject" required>
                        <option value="">Select a topic...</option>
                        <option value="general">General Inquiry</option>
                        <option value="billing">Billing Question</option>
                        <option value="technical">Technical Support</option>
                        <option value="feature">Feature Request</option>
                        <option value="bug">Report a Bug</option>
                        <option value="other">Other</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="message">Message</label>
                    <textarea id="message" name="message" rows="5" required
                        placeholder="Describe your issue or question..."></textarea>
                </div>

                <button type="submit" class="submit-btn">Send Message</button>
            </form>
        </div>

        <!-- FAQ Section -->
        <div class="faq-section" id="faq">
            <h2>Frequently Asked Questions</h2>

            <div class="faq-list">
                <details class="faq-item">
                    <summary>How do I reset my password?</summary>
                    <p>Click the "Forgot Password" link on the login page. Enter your email address, and we'll send you a
                        secure link to reset your password. The link expires after 60 minutes.</p>
                </details>

                <details class="faq-item">
                    <summary>Can I add multiple technicians to my account?</summary>
                    <p>Yes! Depending on your subscription plan, you can add unlimited technicians. Go to Settings → Team
                        Management to invite new team members via email.</p>
                </details>

                <details class="faq-item">
                    <summary>How does the mobile app work offline?</summary>
                    <p>Our mobile app automatically caches your assigned work orders and essential data. When you're back
                        online, all changes sync automatically. Conflict resolution follows a "last update wins" policy.</p>
                </details>

                <details class="faq-item">
                    <summary>What payment methods do you accept?</summary>
                    <p>We accept all major credit cards (Visa, Mastercard, American Express), PayPal, and bank transfers for
                        annual subscriptions. All payments are processed securely through Stripe.</p>
                </details>

                <details class="faq-item">
                    <summary>Can I export my data?</summary>
                    <p>Absolutely. You can export all your data at any time from Settings → Data Export. We support CSV,
                        Excel, and JSON formats. Your data belongs to you.</p>
                </details>

                <details class="faq-item">
                    <summary>Is my data secure?</summary>
                    <p>Security is our top priority. We use AES-256 encryption for data at rest, TLS 1.3 for data in
                        transit, and maintain SOC 2 Type II compliance. Regular security audits ensure we meet the highest
                        standards.</p>
                </details>

                <details class="faq-item">
                    <summary>How do I cancel my subscription?</summary>
                    <p>You can cancel your subscription anytime from Settings → Billing. Your access continues until the end
                        of your current billing period. We don't charge cancellation fees.</p>
                </details>

                <details class="faq-item">
                    <summary>Do you offer custom integrations?</summary>
                    <p>Yes, we offer API access on our Professional and Enterprise plans. For custom integrations, contact
                        our enterprise team to discuss your requirements.</p>
                </details>
            </div>
        </div>

        <!-- Emergency Contact -->
        <div class="emergency-section">
            <div class="emergency-card">
                <div class="emergency-icon">
                    <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                    </svg>
                </div>
                <div>
                    <h3>Need urgent help?</h3>
                    <p>For critical issues affecting your operations, call our priority support line.</p>
                </div>
                <a href="tel:+1234567890" class="emergency-btn">Call Priority Support</a>
            </div>
        </div>
    </div>

    <style>
        .support-page {
            max-width: 1000px;
            margin: 0 auto;
        }

        .support-header {
            text-align: center;
            margin-bottom: 48px;
        }

        .support-header h1 {
            font-size: 40px;
            font-weight: 800;
            margin-bottom: 12px;
        }

        .support-header p {
            font-size: 18px;
            color: #64748b;
        }

        .support-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 20px;
            margin-bottom: 64px;
        }

        .help-card {
            background: white;
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            padding: 28px;
            transition: all 0.2s;
        }

        .help-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(15, 23, 42, 0.08);
        }

        .help-card.highlight {
            background: linear-gradient(135deg, #0f766e 0%, #0d5e58 100%);
            color: white;
            border-color: transparent;
        }

        .help-card.highlight .help-icon {
            background: rgba(255, 255, 255, 0.15);
            color: white;
        }

        .help-card.highlight p {
            color: rgba(255, 255, 255, 0.85);
        }

        .help-card.highlight .help-link {
            color: white;
        }

        .help-icon {
            width: 56px;
            height: 56px;
            background: #f0fdfa;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #0f766e;
            margin-bottom: 16px;
        }

        .help-card h3 {
            font-size: 18px;
            font-weight: 700;
            margin-bottom: 8px;
        }

        .help-card p {
            color: #64748b;
            font-size: 14px;
            line-height: 1.6;
            margin-bottom: 16px;
        }

        .help-link {
            color: #0f766e;
            font-weight: 600;
            text-decoration: none;
            font-size: 14px;
        }

        .contact-section {
            background: white;
            border: 1px solid #e2e8f0;
            border-radius: 20px;
            padding: 40px;
            margin-bottom: 64px;
        }

        .contact-section h2 {
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 8px;
        }

        .contact-section>p {
            color: #64748b;
            margin-bottom: 32px;
        }

        .contact-form {
            max-width: 600px;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            font-weight: 600;
            font-size: 14px;
            margin-bottom: 8px;
            color: #334155;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 12px 16px;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            font-size: 15px;
            transition: border-color 0.2s, box-shadow 0.2s;
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #0f766e;
            box-shadow: 0 0 0 3px rgba(15, 118, 110, 0.1);
        }

        .submit-btn {
            background: linear-gradient(135deg, #0f766e 0%, #0d5e58 100%);
            color: white;
            border: none;
            padding: 14px 32px;
            border-radius: 12px;
            font-weight: 600;
            font-size: 15px;
            cursor: pointer;
            transition: all 0.2s;
        }

        .submit-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 14px rgba(15, 118, 110, 0.35);
        }

        .faq-section {
            margin-bottom: 64px;
        }

        .faq-section h2 {
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 24px;
        }

        .faq-list {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .faq-item {
            background: white;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            overflow: hidden;
        }

        .faq-item summary {
            padding: 20px 24px;
            font-weight: 600;
            cursor: pointer;
            list-style: none;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .faq-item summary::-webkit-details-marker {
            display: none;
        }

        .faq-item summary::after {
            content: '+';
            font-size: 20px;
            color: #64748b;
            transition: transform 0.2s;
        }

        .faq-item[open] summary::after {
            transform: rotate(45deg);
        }

        .faq-item p {
            padding: 0 24px 20px;
            color: #64748b;
            line-height: 1.7;
        }

        .emergency-section {
            margin-bottom: 40px;
        }

        .emergency-card {
            background: #fef3c7;
            border: 1px solid #fcd34d;
            border-radius: 16px;
            padding: 24px 32px;
            display: flex;
            align-items: center;
            gap: 20px;
            flex-wrap: wrap;
        }

        .emergency-icon {
            width: 48px;
            height: 48px;
            background: #f59e0b;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            flex-shrink: 0;
        }

        .emergency-card h3 {
            font-size: 16px;
            font-weight: 700;
            margin-bottom: 4px;
        }

        .emergency-card>div {
            flex: 1;
            min-width: 200px;
        }

        .emergency-card p {
            color: #92400e;
            font-size: 14px;
        }

        .emergency-btn {
            background: #f59e0b;
            color: white;
            padding: 12px 24px;
            border-radius: 10px;
            font-weight: 600;
            text-decoration: none;
            font-size: 14px;
            transition: all 0.2s;
        }

        .emergency-btn:hover {
            background: #d97706;
        }

        @media (max-width: 640px) {
            .form-row {
                grid-template-columns: 1fr;
            }

            .contact-section {
                padding: 24px;
            }

            .emergency-card {
                flex-direction: column;
                text-align: center;
            }
        }
    </style>
@endsection