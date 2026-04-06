<div class="min-h-screen bg-gray-50 py-8 px-4">
    <div class="max-w-2xl mx-auto">
        <div class="text-center mb-8">
            <img src="{{ asset('assets/images/JADE_PDGI_Light.webp') }}" alt="Jakarta Dental Exhibition 2026" class="h-36 mx-auto mb-4">
        </div>

        <div class="bg-green-50 border border-green-200 rounded-lg p-6 text-center">
            <svg class="w-16 h-16 text-green-500 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" strokejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
            </svg>
            <h1 class="text-2xl font-bold text-green-800 mb-2">{{ __('seminar.success_title') }}</h1>
            <p class="text-green-700 mb-4">{{ __('seminar.success_thank_you') }}{{ $registration->name }}!</p>
            <p class="text-gray-600 mb-1">{{ __('seminar.registration_code') }} <strong>{{ $registration->registration_code }}</strong></p>
            <p class="text-gray-600 mb-6">{{ __('seminar.confirmation_email_sent') }} {{ $registration->email }}</p>

            @if (!$isInternational)
            <div class="mt-6 pt-6 border-t border-green-200 text-left">
                <p class="text-green-800 font-medium mb-3">{{ __('seminar.success_message_1') }}</p>
                <p class="text-green-700 mb-3">{{ __('seminar.success_message_2') }}</p>
                <p class="text-green-700">{{ __('seminar.success_message_3') }} <a href="https://chat.whatsapp.com/KtELLi4Q22VHqJWFavOwhQ?mode=hq1tcla" target="_blank" class="underline font-semibold hover:text-green-900">https://chat.whatsapp.com/KtELLi4Q22VHqJWFavOwhQ</a></p>
            </div>
            @endif

            @auth
                <div class="mt-6 pt-6 border-t border-green-200"
                     x-data="{ countdown: 5 }"
                     x-init="
                        const timer = setInterval(() => {
                            if (countdown > 0) {
                                countdown--;
                            } else {
                                clearInterval(timer);
                                window.location.href = '{{ route('register.seminar', ['locale' => app()->getLocale()]) }}';
                            }
                        }, 1000);
                     ">
                    <p class="text-green-600 font-medium">
                        {{ __('seminar.redirecting_to_registration') }} <span x-text="countdown"></span>...
                    </p>
                </div>
            @endauth
        </div>
    </div>
</div>
