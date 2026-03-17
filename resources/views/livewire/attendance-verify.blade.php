<div class="min-h-screen bg-gray-50 py-8 px-4">
    <div class="max-w-lg mx-auto">
        <div class="text-center mb-6">
            <img src="{{ asset('assets/images/JADE_PDGI_Light.webp') }}" alt="Jakarta Dental Exhibition 2026" class="h-20 mx-auto mb-4">
            <h1 class="text-2xl font-bold text-gray-800">{{ __('seminar.attendance_verification_title') }}</h1>
        </div>

        @if(!$isValid)
            @if($isExpired)
                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-6 text-center">
                    <svg class="w-16 h-16 text-yellow-500 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                    </svg>
                    <h2 class="text-xl font-bold text-yellow-800 mb-2">{{ __('seminar.qr_code_expired_title') }}</h2>
                    <p class="text-yellow-700">{{ __('seminar.qr_code_expired_message') }}</p>
                </div>
            @else
                <div class="bg-red-50 border border-red-200 rounded-lg p-6 text-center">
                    <svg class="w-16 h-16 text-red-500 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                    <h2 class="text-xl font-bold text-red-800 mb-2">{{ __('seminar.invalid_qr_code_title') }}</h2>
                    <p class="text-red-700">{{ __('seminar.invalid_qr_code_message') }}</p>
                </div>
            @endif
        @else
            @if($showSuccess)
                <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-4" wire:transition>
                    <p class="text-green-800 font-medium">{{ __('seminar.attendance_check_in_success') }}</p>
                </div>
            @endif

            @error('payment')
                <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-4">
                    <p class="text-red-800">{{ $message }}</p>
                </div>
            @enderror

            <div class="bg-white rounded-lg shadow-lg border border-gray-200 overflow-hidden">
                <div class="bg-blue-600 text-white px-6 py-4">
                    <h2 class="text-lg font-semibold">{{ __('seminar.participant_details_title') }}</h2>
                </div>

                <div class="p-6">
                    <div class="space-y-3 mb-6">
                        <div>
                            <p class="text-sm text-gray-500">{{ __('seminar.name_label') }}</p>
                            <p class="font-semibold text-gray-900">{{ $registration->name }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">{{ __('seminar.email_label') }}</p>
                            <p class="text-gray-900">{{ $registration->email }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">{{ __('seminar.registration_code_label') }}</p>
                            <p class="font-mono font-bold text-blue-600">{{ $registration->registration_code }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">{{ __('seminar.payment_status_label') }}</p>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-{{ $this->paymentStatusColor }}-100 text-{{ $this->paymentStatusColor }}-800">
                                {{ $this->paymentStatusLabel }}
                            </span>
                        </div>
                    </div>

                    @if($registration->payment_status !== 'verified')
                        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-6">
                            <div class="flex items-center">
                                <svg class="w-5 h-5 text-yellow-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                                </svg>
                                <p class="text-yellow-800 font-medium">{{ __('seminar.payment_not_verified_checkin_disabled') }}</p>
                            </div>
                        </div>
                    @endif

                    <div class="border-t pt-4">
                        <h3 class="font-semibold text-gray-900 mb-3">{{ __('seminar.seminar_attendance_title') }}</h3>
                        @if($seminarCheckedInAt)
                            <div class="bg-green-50 border border-green-200 rounded-lg p-3 flex items-center justify-between">
                                <span class="text-green-800">{{ __('seminar.already_checked_in') }}</span>
                                <span class="text-sm text-green-600">{{ $seminarCheckedInAt }}</span>
                            </div>
                        @else
                            <button
                                wire:click="checkInSeminar"
                                class="w-full bg-blue-600 text-white py-3 px-6 rounded-lg font-semibold hover:bg-blue-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                                @if($registration->payment_status !== 'verified') disabled @endif
                            >
                                {{ __('seminar.check_in_seminar_button') }}
                            </button>
                        @endif
                    </div>

                    @if($this->handsOnSessions->isNotEmpty())
                        <div class="border-t pt-4 mt-4">
                            <h3 class="font-semibold text-gray-900 mb-3">{{ __('seminar.hands_on_sessions_title') }}</h3>
                            <div class="space-y-3">
                                @foreach($this->handsOnSessions as $session)
                                    <div class="bg-gray-50 rounded-lg p-3">
                                        <div class="flex justify-between items-start mb-2">
                                            <div>
                                                <p class="font-medium text-gray-900">{{ $session['name'] }}</p>
                                                <p class="text-sm text-gray-500">{{ $session['date'] }}</p>
                                            </div>
                                            @if($session['payment_status'] !== 'verified')
                                                <span class="text-xs bg-yellow-100 text-yellow-800 px-2 py-1 rounded">{{ __('seminar.payment_pending') }}</span>
                                            @endif
                                        </div>
                                        @if($session['checked_in'])
                                            <div class="bg-green-50 border border-green-200 rounded p-2 flex items-center justify-between">
                                                <span class="text-green-800 text-sm">{{ __('seminar.checked_in') }}</span>
                                                <span class="text-xs text-green-600">{{ $session['checked_in'] }}</span>
                                            </div>
                                        @else
                                            <button
                                                wire:click="checkInHandsOn({{ $session['id'] }})"
                                                class="w-full bg-blue-600 text-white py-2 px-4 rounded font-medium hover:bg-blue-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed text-sm"
                                                @if($session['payment_status'] !== 'verified') disabled @endif
                                            >
                                                {{ __('seminar.check_in_button') }}
                                            </button>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        @endif
    </div>
</div>