@extends('layouts.guest')

@section('content')
    <div class="min-h-screen flex items-center justify-center bg-gray-100 dark:bg-gray-900 py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-lg w-full space-y-8">
            <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-8">
                <div class="text-center mb-8">
                    <div
                        class="mx-auto h-16 w-16 bg-yellow-100 dark:bg-yellow-900 rounded-full flex items-center justify-center">
                        <svg class="h-8 w-8 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z">
                            </path>
                        </svg>
                    </div>
                    <h2 class="mt-4 text-2xl font-bold text-gray-900 dark:text-white">Save Your Backup Codes</h2>
                    <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                        @if(isset($regenerated))
                            Your new backup codes have been generated.
                        @else
                            Two-factor authentication is now enabled!
                        @endif
                    </p>
                </div>

                <div
                    class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg p-4 mb-6">
                    <p class="text-sm text-yellow-800 dark:text-yellow-200">
                        <strong>Important:</strong> Store these codes in a safe place. Each code can only be used once.
                        You will not be able to see these codes again.
                    </p>
                </div>

                <div class="grid grid-cols-2 gap-3 mb-6">
                    @foreach($backupCodes as $code)
                        <code class="bg-gray-100 dark:bg-gray-700 p-3 rounded text-center font-mono text-sm">
                                {{ $code }}
                            </code>
                    @endforeach
                </div>

                <div class="flex space-x-4">
                    <button onclick="copyBackupCodes()"
                        class="flex-1 py-2 px-4 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                        <svg class="inline-block w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z">
                            </path>
                        </svg>
                        Copy Codes
                    </button>
                    <button onclick="downloadBackupCodes()"
                        class="flex-1 py-2 px-4 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                        <svg class="inline-block w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                        </svg>
                        Download
                    </button>
                </div>

                <div class="mt-8 text-center">
                    <a href="{{ route('profile') }}"
                        class="inline-flex items-center px-6 py-3 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700">
                        I've Saved My Codes
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script>
        const backupCodes = @json($backupCodes);

        function copyBackupCodes() {
            const text = backupCodes.join('\n');
            navigator.clipboard.writeText(text).then(() => {
                alert('Backup codes copied to clipboard!');
            });
        }

        function downloadBackupCodes() {
            const text = '{{ config("app.name") }} Two-Factor Authentication Backup Codes\n' +
                '============================================\n\n' +
                backupCodes.join('\n') + '\n\n' +
                'Generated: {{ now()->format("Y-m-d H:i:s") }}\n' +
                'Each code can only be used once.';

            const blob = new Blob([text], { type: 'text/plain' });
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = '2fa-backup-codes.txt';
            a.click();
            URL.revokeObjectURL(url);
        }
    </script>
@endsection