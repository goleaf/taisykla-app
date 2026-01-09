@extends('layouts.guest-page')

@section('title', 'Privacy Policy')

@section('content')
    <div class="prose-container">
        <h1>Privacy Policy</h1>
        <p class="last-updated">Last updated: {{ date('F j, Y') }}</p>

        <section>
            <h2>1. Introduction</h2>
            <p>{{ config('app.name', 'Taisykla') }} ("we," "our," or "us") is committed to protecting your privacy. This
                Privacy Policy explains how we collect, use, disclose, and safeguard your information when you use our
                equipment maintenance management platform.</p>
        </section>

        <section>
            <h2>2. Information We Collect</h2>
            <h3>Personal Information</h3>
            <p>We may collect personal information that you voluntarily provide when using our service, including:</p>
            <ul>
                <li>Name and contact information (email, phone number, address)</li>
                <li>Account credentials</li>
                <li>Company information</li>
                <li>Payment information</li>
                <li>Communication preferences</li>
            </ul>

            <h3>Automatically Collected Information</h3>
            <p>When you access our platform, we automatically collect:</p>
            <ul>
                <li>Device information (browser type, operating system)</li>
                <li>IP address and location data</li>
                <li>Usage patterns and preferences</li>
                <li>Log data and analytics</li>
            </ul>
        </section>

        <section>
            <h2>3. How We Use Your Information</h2>
            <p>We use the collected information for:</p>
            <ul>
                <li>Providing and maintaining our services</li>
                <li>Processing transactions and sending related information</li>
                <li>Sending administrative information and updates</li>
                <li>Responding to inquiries and providing customer support</li>
                <li>Improving our platform and developing new features</li>
                <li>Ensuring platform security and preventing fraud</li>
                <li>Complying with legal obligations</li>
            </ul>
        </section>

        <section>
            <h2>4. Information Sharing</h2>
            <p>We may share your information with:</p>
            <ul>
                <li><strong>Service Providers:</strong> Third-party vendors who assist in operating our platform</li>
                <li><strong>Business Partners:</strong> With your consent, for joint offerings</li>
                <li><strong>Legal Requirements:</strong> When required by law or to protect our rights</li>
                <li><strong>Business Transfers:</strong> In connection with mergers or acquisitions</li>
            </ul>
            <p>We do not sell your personal information to third parties.</p>
        </section>

        <section>
            <h2>5. Data Security</h2>
            <p>We implement appropriate technical and organizational measures to protect your personal information,
                including:</p>
            <ul>
                <li>Encryption of data in transit and at rest</li>
                <li>Regular security assessments</li>
                <li>Access controls and authentication</li>
                <li>Employee training on data protection</li>
            </ul>
        </section>

        <section>
            <h2>6. Your Rights</h2>
            <p>Depending on your location, you may have the right to:</p>
            <ul>
                <li>Access your personal information</li>
                <li>Correct inaccurate data</li>
                <li>Request deletion of your data</li>
                <li>Object to or restrict processing</li>
                <li>Data portability</li>
                <li>Withdraw consent</li>
            </ul>
            <p>To exercise these rights, please contact us using the information below.</p>
        </section>

        <section>
            <h2>7. Cookies and Tracking</h2>
            <p>We use cookies and similar tracking technologies to enhance your experience. You can control cookie
                preferences through your browser settings.</p>
        </section>

        <section>
            <h2>8. Data Retention</h2>
            <p>We retain your personal information for as long as necessary to fulfill the purposes outlined in this policy,
                unless a longer retention period is required by law.</p>
        </section>

        <section>
            <h2>9. Children's Privacy</h2>
            <p>Our service is not intended for individuals under 16 years of age. We do not knowingly collect personal
                information from children.</p>
        </section>

        <section>
            <h2>10. Changes to This Policy</h2>
            <p>We may update this Privacy Policy from time to time. We will notify you of any changes by posting the new
                policy on this page and updating the "Last updated" date.</p>
        </section>

        <section>
            <h2>11. Contact Us</h2>
            <p>If you have questions about this Privacy Policy, please contact us at:</p>
            <ul>
                <li>Email: privacy@{{ parse_url(config('app.url'), PHP_URL_HOST) ?: 'example.com' }}</li>
                <li>Address: [Your Business Address]</li>
            </ul>
        </section>
    </div>
@endsection