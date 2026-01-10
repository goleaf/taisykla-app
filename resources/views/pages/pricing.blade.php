@extends('layouts.guest-page')

@section('title', 'Pricing - Taisykla Field Ops')
@section('description', 'Simple, transparent pricing for equipment maintenance management. Choose the plan that fits your business.')

@section('content')
    <div class="pricing-page">
        <!-- Hero Section -->
        <section class="pricing-hero">
            <div class="container">
                <span class="hero-badge">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="16" height="16">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Transparent Pricing
                </span>
                <h1 class="hero-title">Plans that grow with your business</h1>
                <p class="hero-subtitle">Start with a free trial. No credit card required. Cancel anytime.</p>

                <!-- Billing Toggle -->
                <div class="billing-toggle" x-data="{ annual: true }">
                    <button @click="annual = false" :class="{ 'active': !annual }">Monthly</button>
                    <button @click="annual = true" :class="{ 'active': annual }">
                        Annual
                        <span class="save-badge">Save 20%</span>
                    </button>
                </div>
            </div>
        </section>

        <!-- Pricing Cards -->
        <section class="pricing-cards" x-data="{ annual: true }">
            <div class="container">
                <div class="cards-grid">
                    <!-- Starter Plan -->
                    <div class="pricing-card">
                        <div class="card-header">
                            <div class="plan-icon starter">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M13 10V3L4 14h7v7l9-11h-7z" />
                                </svg>
                            </div>
                            <h3 class="plan-name">Starter</h3>
                            <p class="plan-desc">Perfect for small service teams getting started.</p>
                        </div>
                        <div class="card-price">
                            <div class="price">
                                <span class="currency">€</span>
                                <span class="amount" x-text="annual ? '29' : '39'">29</span>
                                <span class="period">/month</span>
                            </div>
                            <p class="price-note" x-show="annual">Billed annually (€348/year)</p>
                            <p class="price-note" x-show="!annual">Billed monthly</p>
                        </div>
                        <ul class="features-list">
                            <li>
                                <svg class="check" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M5 13l4 4L19 7" />
                                </svg>
                                Up to 3 technicians
                            </li>
                            <li>
                                <svg class="check" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M5 13l4 4L19 7" />
                                </svg>
                                100 work orders/month
                            </li>
                            <li>
                                <svg class="check" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M5 13l4 4L19 7" />
                                </svg>
                                Equipment tracking
                            </li>
                            <li>
                                <svg class="check" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M5 13l4 4L19 7" />
                                </svg>
                                Basic scheduling
                            </li>
                            <li>
                                <svg class="check" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M5 13l4 4L19 7" />
                                </svg>
                                Email support
                            </li>
                        </ul>
                        <a href="{{ route('register') }}" class="cta-button secondary">Start free trial</a>
                    </div>

                    <!-- Professional Plan (Featured) -->
                    <div class="pricing-card featured">
                        <div class="featured-badge">Most Popular</div>
                        <div class="card-header">
                            <div class="plan-icon professional">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                </svg>
                            </div>
                            <h3 class="plan-name">Professional</h3>
                            <p class="plan-desc">For growing service businesses with multiple teams.</p>
                        </div>
                        <div class="card-price">
                            <div class="price">
                                <span class="currency">€</span>
                                <span class="amount" x-text="annual ? '79' : '99'">79</span>
                                <span class="period">/month</span>
                            </div>
                            <p class="price-note" x-show="annual">Billed annually (€948/year)</p>
                            <p class="price-note" x-show="!annual">Billed monthly</p>
                        </div>
                        <ul class="features-list">
                            <li>
                                <svg class="check" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M5 13l4 4L19 7" />
                                </svg>
                                Up to 15 technicians
                            </li>
                            <li>
                                <svg class="check" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M5 13l4 4L19 7" />
                                </svg>
                                Unlimited work orders
                            </li>
                            <li>
                                <svg class="check" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M5 13l4 4L19 7" />
                                </svg>
                                Inventory management
                            </li>
                            <li>
                                <svg class="check" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M5 13l4 4L19 7" />
                                </svg>
                                Route optimization
                            </li>
                            <li>
                                <svg class="check" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M5 13l4 4L19 7" />
                                </svg>
                                Customer portal
                            </li>
                            <li>
                                <svg class="check" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M5 13l4 4L19 7" />
                                </svg>
                                Invoicing & billing
                            </li>
                            <li>
                                <svg class="check" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M5 13l4 4L19 7" />
                                </svg>
                                Priority support
                            </li>
                        </ul>
                        <a href="{{ route('register') }}" class="cta-button primary">Start free trial</a>
                    </div>

                    <!-- Enterprise Plan -->
                    <div class="pricing-card">
                        <div class="card-header">
                            <div class="plan-icon enterprise">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9" />
                                </svg>
                            </div>
                            <h3 class="plan-name">Enterprise</h3>
                            <p class="plan-desc">For large organizations with custom requirements.</p>
                        </div>
                        <div class="card-price">
                            <div class="price">
                                <span class="amount custom">Custom</span>
                            </div>
                            <p class="price-note">Tailored to your needs</p>
                        </div>
                        <ul class="features-list">
                            <li>
                                <svg class="check" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M5 13l4 4L19 7" />
                                </svg>
                                Unlimited technicians
                            </li>
                            <li>
                                <svg class="check" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M5 13l4 4L19 7" />
                                </svg>
                                Multi-location support
                            </li>
                            <li>
                                <svg class="check" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M5 13l4 4L19 7" />
                                </svg>
                                Advanced analytics
                            </li>
                            <li>
                                <svg class="check" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M5 13l4 4L19 7" />
                                </svg>
                                API access
                            </li>
                            <li>
                                <svg class="check" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M5 13l4 4L19 7" />
                                </svg>
                                Custom integrations
                            </li>
                            <li>
                                <svg class="check" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M5 13l4 4L19 7" />
                                </svg>
                                SLA guarantee
                            </li>
                            <li>
                                <svg class="check" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M5 13l4 4L19 7" />
                                </svg>
                                Dedicated account manager
                            </li>
                        </ul>
                        <a href="{{ route('support') }}" class="cta-button secondary">Contact sales</a>
                    </div>
                </div>
            </div>
        </section>

        <!-- Feature Comparison -->
        <section class="feature-comparison">
            <div class="container">
                <h2 class="section-title">Compare all features</h2>
                <p class="section-subtitle">Find the perfect plan for your business needs</p>

                <div class="comparison-table-wrapper">
                    <table class="comparison-table">
                        <thead>
                            <tr>
                                <th>Features</th>
                                <th>Starter</th>
                                <th class="featured">Professional</th>
                                <th>Enterprise</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr class="category-row">
                                <td colspan="4">Core Features</td>
                            </tr>
                            <tr>
                                <td>Work order management</td>
                                <td>100/month</td>
                                <td class="featured">Unlimited</td>
                                <td>Unlimited</td>
                            </tr>
                            <tr>
                                <td>Technicians</td>
                                <td>3</td>
                                <td class="featured">15</td>
                                <td>Unlimited</td>
                            </tr>
                            <tr>
                                <td>Equipment tracking</td>
                                <td><svg class="icon-check" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M5 13l4 4L19 7" />
                                    </svg></td>
                                <td class="featured"><svg class="icon-check" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M5 13l4 4L19 7" />
                                    </svg></td>
                                <td><svg class="icon-check" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M5 13l4 4L19 7" />
                                    </svg></td>
                            </tr>
                            <tr>
                                <td>Mobile app access</td>
                                <td><svg class="icon-check" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M5 13l4 4L19 7" />
                                    </svg></td>
                                <td class="featured"><svg class="icon-check" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M5 13l4 4L19 7" />
                                    </svg></td>
                                <td><svg class="icon-check" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M5 13l4 4L19 7" />
                                    </svg></td>
                            </tr>
                            <tr class="category-row">
                                <td colspan="4">Scheduling & Dispatch</td>
                            </tr>
                            <tr>
                                <td>Calendar scheduling</td>
                                <td><svg class="icon-check" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M5 13l4 4L19 7" />
                                    </svg></td>
                                <td class="featured"><svg class="icon-check" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M5 13l4 4L19 7" />
                                    </svg></td>
                                <td><svg class="icon-check" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M5 13l4 4L19 7" />
                                    </svg></td>
                            </tr>
                            <tr>
                                <td>Route optimization</td>
                                <td><svg class="icon-x" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M6 18L18 6M6 6l12 12" />
                                    </svg></td>
                                <td class="featured"><svg class="icon-check" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M5 13l4 4L19 7" />
                                    </svg></td>
                                <td><svg class="icon-check" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M5 13l4 4L19 7" />
                                    </svg></td>
                            </tr>
                            <tr>
                                <td>Intelligent assignment</td>
                                <td><svg class="icon-x" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M6 18L18 6M6 6l12 12" />
                                    </svg></td>
                                <td class="featured"><svg class="icon-check" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M5 13l4 4L19 7" />
                                    </svg></td>
                                <td><svg class="icon-check" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M5 13l4 4L19 7" />
                                    </svg></td>
                            </tr>
                            <tr class="category-row">
                                <td colspan="4">Billing & Payments</td>
                            </tr>
                            <tr>
                                <td>Invoicing</td>
                                <td><svg class="icon-x" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M6 18L18 6M6 6l12 12" />
                                    </svg></td>
                                <td class="featured"><svg class="icon-check" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M5 13l4 4L19 7" />
                                    </svg></td>
                                <td><svg class="icon-check" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M5 13l4 4L19 7" />
                                    </svg></td>
                            </tr>
                            <tr>
                                <td>Online payments</td>
                                <td><svg class="icon-x" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M6 18L18 6M6 6l12 12" />
                                    </svg></td>
                                <td class="featured"><svg class="icon-check" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M5 13l4 4L19 7" />
                                    </svg></td>
                                <td><svg class="icon-check" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M5 13l4 4L19 7" />
                                    </svg></td>
                            </tr>
                            <tr class="category-row">
                                <td colspan="4">Support</td>
                            </tr>
                            <tr>
                                <td>Support level</td>
                                <td>Email</td>
                                <td class="featured">Priority</td>
                                <td>Dedicated</td>
                            </tr>
                            <tr>
                                <td>Onboarding</td>
                                <td>Self-service</td>
                                <td class="featured">Guided</td>
                                <td>White-glove</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

        <!-- FAQ Section -->
        <section class="pricing-faq">
            <div class="container">
                <h2 class="section-title">Frequently asked questions</h2>
                <div class="faq-grid">
                    <div class="faq-item" x-data="{ open: false }">
                        <button class="faq-question" @click="open = !open">
                            <span>Can I change plans later?</span>
                            <svg :class="{ 'rotated': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                        <div class="faq-answer" x-show="open" x-transition>
                            Yes! You can upgrade or downgrade your plan at any time. Changes take effect at the start of
                            your next billing cycle. When upgrading, you'll be prorated for the remainder of your current
                            billing period.
                        </div>
                    </div>
                    <div class="faq-item" x-data="{ open: false }">
                        <button class="faq-question" @click="open = !open">
                            <span>Is there a free trial?</span>
                            <svg :class="{ 'rotated': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                        <div class="faq-answer" x-show="open" x-transition>
                            Absolutely! All plans come with a 14-day free trial with full access to all features. No credit
                            card required to start. You can explore the platform risk-free before committing.
                        </div>
                    </div>
                    <div class="faq-item" x-data="{ open: false }">
                        <button class="faq-question" @click="open = !open">
                            <span>What payment methods do you accept?</span>
                            <svg :class="{ 'rotated': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                        <div class="faq-answer" x-show="open" x-transition>
                            We accept all major credit cards (Visa, Mastercard, American Express), PayPal, and bank
                            transfers for annual plans. Enterprise customers can also pay by invoice with NET 30 terms.
                        </div>
                    </div>
                    <div class="faq-item" x-data="{ open: false }">
                        <button class="faq-question" @click="open = !open">
                            <span>Can I cancel my subscription?</span>
                            <svg :class="{ 'rotated': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                        <div class="faq-answer" x-show="open" x-transition>
                            Yes, you can cancel at any time. Your subscription will remain active until the end of your
                            current billing period. We also offer a 30-day money-back guarantee for new customers.
                        </div>
                    </div>
                    <div class="faq-item" x-data="{ open: false }">
                        <button class="faq-question" @click="open = !open">
                            <span>Do you offer discounts for nonprofits?</span>
                            <svg :class="{ 'rotated': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                        <div class="faq-answer" x-show="open" x-transition>
                            Yes! We offer 25% off for verified nonprofits and educational institutions. Please contact our
                            sales team with proof of nonprofit status to receive your discount code.
                        </div>
                    </div>
                    <div class="faq-item" x-data="{ open: false }">
                        <button class="faq-question" @click="open = !open">
                            <span>What happens to my data if I cancel?</span>
                            <svg :class="{ 'rotated': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                        <div class="faq-answer" x-show="open" x-transition>
                            Your data is retained for 90 days after cancellation, allowing you to reactivate your account if
                            you change your mind. You can also export all your data at any time in standard formats (CSV,
                            JSON).
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- CTA Section -->
        <section class="pricing-cta">
            <div class="container">
                <div class="cta-card">
                    <h2>Ready to streamline your service operations?</h2>
                    <p>Start your 14-day free trial today. No credit card required.</p>
                    <div class="cta-buttons">
                        <a href="{{ route('register') }}" class="cta-button primary large">
                            Get started for free
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13 7l5 5m0 0l-5 5m5-5H6" />
                            </svg>
                        </a>
                        <a href="{{ route('support') }}" class="cta-button outline large">Talk to sales</a>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <style>
        .pricing-page {
            --primary: #0f766e;
            --primary-dark: #0d5e58;
            --primary-light: #14b8a6;
            font-family: 'Inter', -apple-system, sans-serif;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 24px;
        }

        /* Hero */
        .pricing-hero {
            text-align: center;
            padding: 80px 0 60px;
            background: linear-gradient(180deg, #f0fdfa 0%, #ffffff 100%);
        }

        .hero-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 16px;
            background: linear-gradient(135deg, rgba(15, 118, 110, 0.1), rgba(20, 184, 166, 0.08));
            border: 1px solid rgba(15, 118, 110, 0.2);
            border-radius: 100px;
            font-size: 14px;
            font-weight: 600;
            color: var(--primary);
            margin-bottom: 24px;
        }

        .hero-title {
            font-size: clamp(32px, 5vw, 48px);
            font-weight: 800;
            color: #0f172a;
            margin: 0 0 16px;
            line-height: 1.2;
        }

        .hero-subtitle {
            font-size: 18px;
            color: #64748b;
            margin: 0 0 32px;
            max-width: 500px;
            margin-left: auto;
            margin-right: auto;
        }

        .billing-toggle {
            display: inline-flex;
            gap: 4px;
            padding: 4px;
            background: #f1f5f9;
            border-radius: 12px;
        }

        .billing-toggle button {
            padding: 10px 20px;
            border: none;
            background: transparent;
            font-size: 14px;
            font-weight: 600;
            color: #64748b;
            cursor: pointer;
            border-radius: 10px;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .billing-toggle button.active {
            background: white;
            color: #0f172a;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        }

        .save-badge {
            padding: 2px 8px;
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
            color: white;
            font-size: 11px;
            font-weight: 600;
            border-radius: 100px;
        }

        /* Pricing Cards */
        .pricing-cards {
            padding: 40px 0 80px;
        }

        .cards-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 24px;
            max-width: 1100px;
            margin: 0 auto;
        }

        .pricing-card {
            background: white;
            border: 1px solid #e2e8f0;
            border-radius: 24px;
            padding: 32px;
            display: flex;
            flex-direction: column;
            position: relative;
            transition: all 0.3s;
        }

        .pricing-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 20px 40px rgba(15, 23, 42, 0.1);
        }

        .pricing-card.featured {
            border: 2px solid var(--primary);
            background: linear-gradient(180deg, rgba(15, 118, 110, 0.02) 0%, white 20%);
        }

        .featured-badge {
            position: absolute;
            top: -12px;
            left: 50%;
            transform: translateX(-50%);
            padding: 6px 16px;
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
            color: white;
            font-size: 12px;
            font-weight: 700;
            border-radius: 100px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .card-header {
            margin-bottom: 24px;
        }

        .plan-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 16px;
        }

        .plan-icon svg {
            width: 24px;
            height: 24px;
        }

        .plan-icon.starter {
            background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
            color: #b45309;
        }

        .plan-icon.professional {
            background: linear-gradient(135deg, #ccfbf1 0%, #99f6e4 100%);
            color: var(--primary);
        }

        .plan-icon.enterprise {
            background: linear-gradient(135deg, #e0e7ff 0%, #c7d2fe 100%);
            color: #4338ca;
        }

        .plan-name {
            font-size: 24px;
            font-weight: 700;
            color: #0f172a;
            margin: 0 0 8px;
        }

        .plan-desc {
            font-size: 14px;
            color: #64748b;
            margin: 0;
            line-height: 1.5;
        }

        .card-price {
            padding: 24px 0;
            border-top: 1px solid #f1f5f9;
            border-bottom: 1px solid #f1f5f9;
            margin-bottom: 24px;
        }

        .price {
            display: flex;
            align-items: baseline;
            gap: 4px;
        }

        .currency {
            font-size: 24px;
            font-weight: 600;
            color: #64748b;
        }

        .amount {
            font-size: 48px;
            font-weight: 800;
            color: #0f172a;
            line-height: 1;
        }

        .amount.custom {
            font-size: 36px;
        }

        .period {
            font-size: 16px;
            color: #64748b;
        }

        .price-note {
            font-size: 13px;
            color: #94a3b8;
            margin: 8px 0 0;
        }

        .features-list {
            list-style: none;
            padding: 0;
            margin: 0 0 32px;
            flex: 1;
        }

        .features-list li {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 10px 0;
            font-size: 14px;
            color: #334155;
        }

        .features-list .check {
            width: 20px;
            height: 20px;
            color: var(--primary);
            flex-shrink: 0;
        }

        .cta-button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 14px 24px;
            font-size: 15px;
            font-weight: 600;
            border-radius: 12px;
            text-decoration: none;
            transition: all 0.2s;
            cursor: pointer;
            border: none;
        }

        .cta-button.primary {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            color: white;
            box-shadow: 0 4px 16px rgba(15, 118, 110, 0.3);
        }

        .cta-button.primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(15, 118, 110, 0.4);
        }

        .cta-button.secondary {
            background: #f1f5f9;
            color: #334155;
        }

        .cta-button.secondary:hover {
            background: #e2e8f0;
        }

        .cta-button.outline {
            background: transparent;
            color: var(--primary);
            border: 2px solid var(--primary);
        }

        .cta-button.outline:hover {
            background: rgba(15, 118, 110, 0.05);
        }

        .cta-button.large {
            padding: 16px 32px;
            font-size: 16px;
        }

        /* Feature Comparison */
        .feature-comparison {
            padding: 80px 0;
            background: #fafbfc;
        }

        .section-title {
            font-size: 32px;
            font-weight: 800;
            color: #0f172a;
            text-align: center;
            margin: 0 0 12px;
        }

        .section-subtitle {
            font-size: 16px;
            color: #64748b;
            text-align: center;
            margin: 0 0 48px;
        }

        .comparison-table-wrapper {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        .comparison-table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 4px 24px rgba(0, 0, 0, 0.06);
        }

        .comparison-table th,
        .comparison-table td {
            padding: 16px 20px;
            text-align: center;
            font-size: 14px;
        }

        .comparison-table th {
            background: #f8fafc;
            font-weight: 700;
            color: #0f172a;
        }

        .comparison-table th.featured {
            background: linear-gradient(180deg, rgba(15, 118, 110, 0.1) 0%, rgba(15, 118, 110, 0.05) 100%);
            color: var(--primary);
        }

        .comparison-table th:first-child {
            text-align: left;
        }

        .comparison-table td:first-child {
            text-align: left;
            font-weight: 500;
            color: #334155;
        }

        .comparison-table td.featured {
            background: rgba(15, 118, 110, 0.02);
        }

        .comparison-table tbody tr {
            border-bottom: 1px solid #f1f5f9;
        }

        .comparison-table tbody tr:last-child {
            border-bottom: none;
        }

        .comparison-table .category-row td {
            background: #f8fafc;
            font-weight: 700;
            color: #0f172a;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 12px 20px;
        }

        .icon-check {
            width: 20px;
            height: 20px;
            color: var(--primary);
        }

        .icon-x {
            width: 20px;
            height: 20px;
            color: #cbd5e1;
        }

        /* FAQ */
        .pricing-faq {
            padding: 80px 0;
        }

        .faq-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
            gap: 16px;
            max-width: 900px;
            margin: 0 auto;
        }

        @media (max-width: 500px) {
            .faq-grid {
                grid-template-columns: 1fr;
            }
        }

        .faq-item {
            background: white;
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            overflow: hidden;
        }

        .faq-question {
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 20px 24px;
            font-size: 15px;
            font-weight: 600;
            color: #0f172a;
            background: none;
            border: none;
            cursor: pointer;
            text-align: left;
        }

        .faq-question svg {
            width: 20px;
            height: 20px;
            color: #64748b;
            transition: transform 0.2s;
            flex-shrink: 0;
        }

        .faq-question svg.rotated {
            transform: rotate(180deg);
        }

        .faq-answer {
            padding: 0 24px 20px;
            font-size: 14px;
            color: #64748b;
            line-height: 1.6;
        }

        /* CTA Section */
        .pricing-cta {
            padding: 0 0 80px;
        }

        .cta-card {
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            border-radius: 24px;
            padding: 60px 40px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .cta-card::before {
            content: '';
            position: absolute;
            top: -100px;
            right: -100px;
            width: 300px;
            height: 300px;
            background: radial-gradient(circle, rgba(20, 184, 166, 0.3), transparent);
            border-radius: 50%;
        }

        .cta-card::after {
            content: '';
            position: absolute;
            bottom: -100px;
            left: -100px;
            width: 300px;
            height: 300px;
            background: radial-gradient(circle, rgba(245, 158, 11, 0.2), transparent);
            border-radius: 50%;
        }

        .cta-card h2 {
            font-size: 32px;
            font-weight: 800;
            color: white;
            margin: 0 0 12px;
            position: relative;
            z-index: 1;
        }

        .cta-card p {
            font-size: 18px;
            color: rgba(255, 255, 255, 0.7);
            margin: 0 0 32px;
            position: relative;
            z-index: 1;
        }

        .cta-buttons {
            display: flex;
            justify-content: center;
            gap: 16px;
            flex-wrap: wrap;
            position: relative;
            z-index: 1;
        }

        .cta-button.outline {
            border-color: white;
            color: white;
        }

        .cta-button.outline:hover {
            background: rgba(255, 255, 255, 0.1);
        }

        /* Responsive */
        @media (max-width: 768px) {
            .pricing-hero {
                padding: 60px 0 40px;
            }

            .cards-grid {
                grid-template-columns: 1fr;
            }

            .pricing-card.featured {
                order: -1;
            }

            .cta-card {
                padding: 40px 24px;
            }

            .cta-card h2 {
                font-size: 24px;
            }
        }
    </style>
@endsection