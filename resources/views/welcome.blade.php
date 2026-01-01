<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ config('app.name', 'Maintenance Manager') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,600&display=swap" rel="stylesheet" />
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="antialiased font-sans bg-gray-50 text-gray-900">
        <div class="min-h-screen flex flex-col">
            <header class="max-w-7xl mx-auto w-full px-6 py-6 flex items-center justify-between">
                <div class="text-lg font-semibold">Maintenance Manager</div>
                <div class="space-x-4 text-sm">
                    @if (Route::has('login'))
                        @auth
                            <a href="{{ url('/dashboard') }}" class="text-indigo-600 font-medium">Dashboard</a>
                        @else
                            <a href="{{ route('login') }}" class="text-gray-600 hover:text-gray-900">Log in</a>
                            @if (Route::has('register'))
                                <a href="{{ route('register') }}" class="text-indigo-600 font-medium">Register</a>
                            @endif
                        @endauth
                    @endif
                </div>
            </header>

            <main class="flex-1">
                <section class="max-w-7xl mx-auto px-6 py-16 grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
                    <div>
                        <p class="text-sm uppercase tracking-wide text-indigo-600">Computer & Equipment Maintenance</p>
                        <h1 class="mt-4 text-4xl font-semibold">Manage service requests, technicians, and customer assets in one platform.</h1>
                        <p class="mt-4 text-gray-600">Track every work order, schedule technicians, manage inventory, and keep customers informed with real-time updates.</p>
                        <div class="mt-6 flex items-center gap-4">
                            <a href="{{ route('login') }}" class="px-4 py-2 bg-indigo-600 text-white rounded-md">Get Started</a>
                            <a href="{{ route('register') }}" class="px-4 py-2 border border-gray-300 rounded-md">Create Account</a>
                        </div>
                    </div>
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                        <h2 class="text-lg font-semibold mb-4">What you can do</h2>
                        <ul class="space-y-3 text-sm text-gray-600">
                            <li>Work order intake, assignment, and status tracking</li>
                            <li>Equipment inventory with warranty and maintenance history</li>
                            <li>Parts management with stock levels and reorder alerts</li>
                            <li>Client portal for requests, invoices, and updates</li>
                            <li>Dispatch scheduling, route planning, and technician views</li>
                            <li>Reports, analytics, and audit logs for compliance</li>
                        </ul>
                    </div>
                </section>
            </main>

            <footer class="border-t border-gray-200 py-6 text-sm text-gray-500">
                <div class="max-w-7xl mx-auto px-6 flex items-center justify-between">
                    <span>Maintenance Manager</span>
                    <span>{{ date('Y') }} All rights reserved.</span>
                </div>
            </footer>
        </div>
    </body>
</html>
