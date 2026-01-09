@extends('layouts.guest')

@section('content')
    <div class="min-h-screen flex items-center justify-center bg-gray-100 dark:bg-gray-900 py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-8">
            <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-8">
                <div class="text-center mb-8">
                    <div
                        class="mx-auto h-16 w-16 bg-green-100 dark:bg-green-900 rounded-full flex items-center justify-center">
                        <svg class="h-8 w-8 text-green-600 dark:text-green-400" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z">
                            </path>
                        </svg>
                    </div>
                    <h2 class="mt-4 text-2xl font-bold text-gray-900 dark:text-white">Enable Two-Factor Authentication</h2>
                    <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">Scan the QR code with your authenticator app
                    </p>
                </div>

                @if($errors->any())
                    <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                        @foreach($errors->all() as $error)
                            <p>{{ $error }}</p>
                        @endforeach
                    </div>
                @endif

                <div class="flex justify-center mb-6">
                    <div class="p-4 bg-white rounded-lg shadow-inner">
                        <img src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&data={{ urlencode($qrCodeUrl) }}"
                            alt="QR Code" class="w-48 h-48">
                    </div>
                </div>

                <div class="mb-6">
                    <p class="text-sm text-gray-600 dark:text-gray-400 text-center mb-2">Can't scan? Enter this code
                        manually:</p>
                    <code class="block bg-gray-100 dark:bg-gray-700 p-3 rounded text-center font-mono text-sm break-all">
                        {{ $secret }}
                    </code>
                </div>

                <form action="{{ route('2fa.enable') }}" method="POST" class="space-y-6">
                    @csrf

                    <div>
                        <label for="code" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Verification Code
                        </label>
                        <input type="text" name="code" id="code" maxlength="6" pattern="[0-9]{6}"
                            class="mt-1 block w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white text-center text-2xl tracking-widest"
                            placeholder="000000" required autofocus>
                        <p class="mt-1 text-xs text-gray-500">Enter the 6-digit code from your authenticator app</p>
                    </div>

                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Confirm Password
                        </label>
                        <input type="password" name="password" id="password"
                            class="mt-1 block w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                            required>
                    </div>

                    <button type="submit"
                        class="w-full flex justify-center py-3 px-4 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                        Enable Two-Factor Authentication
                    </button>
                </form>

                <div class="mt-6 text-center">
                    <a href="{{ route('profile') }}"
                        class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white">
                        Cancel
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection