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
            <p class="text-green-700 mb-4">{{ __('seminar.success_thank_you') }}{{ $registration->name_license }}!</p>
            <p class="text-gray-600 mb-1">{{ __('seminar.registration_code') }} <strong>{{ $registration->registration_code }}</strong></p>
            <p class="text-gray-600 mb-6">{{ __('seminar.confirmation_email_sent') }} {{ $registration->email }}</p>

            @if (!$isInternational)
            {{-- Success Messages --}}
            <div class="mt-6 pt-6 border-t border-green-200">
                <p class="text-green-800 font-medium mb-3">{{ __('seminar.success_message_1') }}</p>
                <p class="text-green-700">{{ __('seminar.success_message_2') }}</p>
            </div>

            {{-- WhatsApp Group Section --}}
            <div class="mt-6 bg-white border-2 border-green-500 rounded-lg p-6 text-center">
                <div class="flex items-center justify-center mb-3">
                    <svg class="w-8 h-8 text-green-600 mr-2" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/>
                    </svg>
                    <h3 class="text-lg font-bold text-gray-800">{{ __('seminar.whatsapp_group_title') }}</h3>
                </div>
                <p class="text-gray-600 mb-4 text-sm">{{ __('seminar.whatsapp_group_description') }}</p>
                <a href="https://chat.whatsapp.com/KtELLi4Q22VHqJWFavOwhQ?mode=hq1tcla"
                   target="_blank"
                   class="inline-flex items-center justify-center px-6 py-3 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-lg transition-colors duration-200 shadow-md hover:shadow-lg">
                    <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/>
                    </svg>
                    {{ __('seminar.whatsapp_group_button') }}
                </a>
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
