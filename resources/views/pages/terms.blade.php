@extends('layouts.guest-page')

@section('title', 'Terms of Service')

@section('content')
    <div class="prose-container">
        <h1>Terms of Service</h1>
        <p class="last-updated">Last updated: {{ date('F j, Y') }}</p>

        <section>
            <h2>1. Agreement to Terms</h2>
            <p>By accessing or using {{ config('app.name', 'Taisykla') }} ("the Service"), you agree to be bound by these
                Terms of Service and all applicable laws and regulations. If you do not agree with any of these terms, you
                are prohibited from using the Service.</p>
        </section>

        <section>
            <h2>2. Description of Service</h2>
            <p>{{ config('app.name', 'Taisykla') }} provides an equipment maintenance management platform that enables
                service companies to manage work orders, track technicians, schedule appointments, and maintain equipment
                records.</p>
        </section>

        <section>
            <h2>3. User Accounts</h2>
            <h3>Account Registration</h3>
            <p>To access certain features, you must register for an account. You agree to:</p>
            <ul>
                <li>Provide accurate, current, and complete information</li>
                <li>Maintain and promptly update your account information</li>
                <li>Keep your password secure and confidential</li>
                <li>Accept responsibility for all activities under your account</li>
                <li>Notify us immediately of any unauthorized access</li>
            </ul>

            <h3>Account Termination</h3>
            <p>We reserve the right to suspend or terminate accounts that violate these terms or for any other reason at our
                discretion.</p>
        </section>

        <section>
            <h2>4. Acceptable Use</h2>
            <p>You agree not to:</p>
            <ul>
                <li>Use the Service for any unlawful purpose</li>
                <li>Violate any applicable laws or regulations</li>
                <li>Infringe on intellectual property rights</li>
                <li>Transmit harmful code or malware</li>
                <li>Attempt to gain unauthorized access to any systems</li>
                <li>Interfere with or disrupt the Service</li>
                <li>Collect user information without consent</li>
                <li>Use the Service to send spam or unsolicited communications</li>
            </ul>
        </section>

        <section>
            <h2>5. Intellectual Property</h2>
            <p>The Service and its original content, features, and functionality are owned by
                {{ config('app.name', 'Taisykla') }} and are protected by international copyright, trademark, and other
                intellectual property laws.</p>
            <p>You retain ownership of any data you submit to the Service. By submitting data, you grant us a license to use
                it for providing and improving the Service.</p>
        </section>

        <section>
            <h2>6. Payment Terms</h2>
            <p>For paid subscriptions:</p>
            <ul>
                <li>Fees are charged in advance on a subscription basis</li>
                <li>All fees are non-refundable unless otherwise stated</li>
                <li>We may change pricing with 30 days' notice</li>
                <li>You are responsible for all applicable taxes</li>
                <li>Failure to pay may result in service suspension</li>
            </ul>
        </section>

        <section>
            <h2>7. Data and Privacy</h2>
            <p>Your use of the Service is also governed by our <a href="{{ route('privacy') }}">Privacy Policy</a>. By using
                the Service, you consent to the collection and use of information as described in that policy.</p>
        </section>

        <section>
            <h2>8. Service Availability</h2>
            <p>We strive to maintain high availability but do not guarantee uninterrupted access. We may:</p>
            <ul>
                <li>Perform scheduled maintenance with advance notice</li>
                <li>Make emergency changes to protect the Service</li>
                <li>Modify or discontinue features at our discretion</li>
            </ul>
        </section>

        <section>
            <h2>9. Limitation of Liability</h2>
            <p>To the maximum extent permitted by law, {{ config('app.name', 'Taisykla') }} shall not be liable for:</p>
            <ul>
                <li>Indirect, incidental, special, or consequential damages</li>
                <li>Loss of profits, data, or business opportunities</li>
                <li>Service interruptions or data breaches beyond our control</li>
            </ul>
            <p>Our total liability shall not exceed the amount paid by you in the 12 months preceding the claim.</p>
        </section>

        <section>
            <h2>10. Indemnification</h2>
            <p>You agree to indemnify and hold harmless {{ config('app.name', 'Taisykla') }} and its affiliates from any
                claims, damages, or expenses arising from your use of the Service or violation of these terms.</p>
        </section>

        <section>
            <h2>11. Dispute Resolution</h2>
            <p>Any disputes arising from these terms or the Service shall be resolved through:</p>
            <ol>
                <li>Good faith negotiation between the parties</li>
                <li>Mediation if negotiation fails</li>
                <li>Binding arbitration as a last resort</li>
            </ol>
        </section>

        <section>
            <h2>12. Modifications to Terms</h2>
            <p>We reserve the right to modify these terms at any time. Material changes will be communicated via email or
                through the Service. Continued use after changes constitutes acceptance of the new terms.</p>
        </section>

        <section>
            <h2>13. Governing Law</h2>
            <p>These terms shall be governed by and construed in accordance with the laws of [Your Jurisdiction], without
                regard to conflict of law provisions.</p>
        </section>

        <section>
            <h2>14. Contact Information</h2>
            <p>For questions about these Terms of Service, please contact us at:</p>
            <ul>
                <li>Email: legal@{{ parse_url(config('app.url'), PHP_URL_HOST) ?: 'example.com' }}</li>
                <li>Address: [Your Business Address]</li>
            </ul>
        </section>
    </div>
@endsection