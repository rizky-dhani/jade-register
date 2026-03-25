<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Verify Your Email - JADE 2026</title>
    @vite(['resources/css/app.css'])
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center p-4">
    <div class="max-w-md w-full bg-white rounded-lg shadow-lg p-8">
        <div class="text-center mb-6">
            <img src="{{ asset('assets/images/JADE_PDGI_Light.webp') }}" alt="JADE" class="h-24 mx-auto mb-4">
            <h1 class="text-2xl font-bold text-gray-800">{{ __('seminar.email_verify_important') }}</h1>
        </div>

        @if (session('status') == 'verification-link-sent')
            <div class="mb-4 p-4 bg-green-50 border border-green-200 rounded-lg">
                <p class="text-green-700 text-sm">{{ __('seminar.email_verify_link_sent') }}</p>
            </div>
        @endif

        <div class="text-gray-600 mb-6">
            <p class="mb-4">{{ __('seminar.email_verify_thank_you') }}</p>
            <p>{{ __('seminar.email_verify_check_inbox') }}</p>
        </div>

        <div class="space-y-4">
            <form method="POST" action="{{ route('verification.send') }}">
                @csrf
                <button type="submit" class="w-full bg-blue-600 text-white py-3 px-6 rounded-lg font-semibold hover:bg-blue-700 transition-colors">
                    {{ __('seminar.email_verify_resend') }}
                </button>
            </form>

            <form method="POST" action="{{ filament()->getLogoutUrl() }}">
                @csrf
                <button type="submit" class="w-full border border-gray-300 text-gray-700 py-3 px-6 rounded-lg font-medium hover:bg-gray-50 transition-colors">
                    {{ __('seminar.email_verify_logout') }}
                </button>
            </form>
        </div>
    </div>
</body>
</html>
