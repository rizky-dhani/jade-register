<div class="min-h-screen bg-gray-50 py-8 px-4">
    <div class="max-w-2xl mx-auto">
        <div class="text-center mb-8">
            <img src="{{ asset('assets/images/JADE_PDGI_Light.webp') }}" alt="Jakarta Dental Exhibition 2026" class="h-36 mx-auto mb-4">
        </div>

        <div class="bg-green-50 border border-green-200 rounded-lg p-6 text-center">
            <svg class="w-16 h-16 text-green-500 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
            </svg>
            <h1 class="text-2xl font-bold text-green-800 mb-2">{{ __('seminar.visitor_registration_success_title') }}</h1>
            <p class="text-green-700 mb-4">{{ __('seminar.visitor_registration_thank_you') }}, {{ $visitor->name }}!</p>
            <p class="text-gray-600 mb-6">{{ __('seminar.visitor_confirmation_email_sent') }} {{ $visitor->email }}</p>

            @if ($visitor->barcode)
                <div class="mt-6 pt-6 border-t border-green-200">
                    <h2 class="text-lg font-semibold text-gray-800 mb-4">{{ __('seminar.visitor_qr_code_title') }}</h2>
                    <p class="text-gray-600 mb-4">{{ __('seminar.visitor_qr_code_description') }}</p>
                    <div class="bg-white p-4 rounded-lg inline-block shadow-md">
                        <img src="{{ asset('storage/'.$visitor->barcode) }}" alt="QR Code" class="w-48 h-48">
                    </div>
                    <p class="text-sm text-gray-500 mt-4 text-center">{{ __('seminar.visitor_qr_code_scan_instruction') }}</p>
                </div>
            @endif
        </div>
    </div>
</div>
