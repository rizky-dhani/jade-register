<div class="max-w-2xl mx-auto p-6">
    <div class="text-center mb-8">
        <img src="{{ asset('assets/images/JADE_PDGI_Light.webp') }}" alt="Jakarta Dental Exhibition 2026" class="h-24 mx-auto mb-4">
        <h1 class="text-2xl font-bold text-gray-800">{{ __('seminar.visitor_verification_title') }}</h1>
    </div>

    @if(!$isValid)
        <div class="bg-red-50 border border-red-200 rounded-lg p-6 text-center">
            <svg class="w-16 h-16 text-red-500 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <h2 class="text-xl font-bold text-red-800 mb-2">{{ __('seminar.qr_code_invalid') }}</h2>
            <p class="text-red-700">{{ __('seminar.qr_code_invalid_message') }}</p>
        </div>
    @elseif($isAlreadyScanned && !$showSuccess)
        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-6 text-center">
            <svg class="w-16 h-16 text-yellow-500 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
            </svg>
            <h2 class="text-xl font-bold text-yellow-800 mb-2">{{ __('seminar.visitor_already_scanned') }}</h2>
            <p class="text-yellow-700">{{ __('seminar.visitor_already_scanned_message') }}</p>
            @if($this->scannedAt)
                <p class="text-yellow-600 mt-2">{{ __('seminar.scanned_at') }}: {{ $this->scannedAt }}</p>
            @endif
        </div>
    @else
        <div class="bg-white rounded-lg shadow-lg border border-gray-200 p-6">
            @if($showSuccess)
                <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-6 text-center">
                    <svg class="w-12 h-12 text-green-500 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    <h3 class="text-lg font-bold text-green-800">{{ __('seminar.visitor_check_in_success') }}</h3>
                    <p class="text-green-700">{{ __('seminar.visitor_check_in_success_message') }}</p>
                </div>
            @endif

            {{-- Visitor Details --}}
            <div class="mb-6">
                <h2 class="text-lg font-semibold text-gray-800 mb-4">{{ __('seminar.visitor_details') }}</h2>
                <div class="bg-gray-50 rounded-lg p-4 space-y-3">
                    <div class="flex justify-between">
                        <span class="text-gray-600">{{ __('seminar.visitor_name_label') }}:</span>
                        <span class="font-medium">{{ $visitor->name }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">{{ __('seminar.visitor_email_label') }}:</span>
                        <span class="font-medium">{{ $visitor->email }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">{{ __('seminar.visitor_phone_label') }}:</span>
                        <span class="font-medium">{{ $visitor->phone }}</span>
                    </div>
                    @if($visitor->affiliation)
                        <div class="flex justify-between">
                            <span class="text-gray-600">{{ __('seminar.visitor_affiliation_label') }}:</span>
                            <span class="font-medium">{{ $visitor->affiliation }}</span>
                        </div>
                    @endif
                    <div class="flex justify-between border-t border-gray-200 pt-3 mt-3">
                        <span class="text-gray-600">{{ __('seminar.visitor_registration_id') }}:</span>
                        <span class="font-medium">VIS-{{ $visitor->id }}</span>
                    </div>
                </div>
            </div>

            @error('auth')
                <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-4">
                    <p class="text-red-700 text-center">{{ $message }}</p>
                </div>
            @enderror

            {{-- Confirm Attendance Button --}}
            @if(!$isAlreadyScanned && !$showSuccess)
                <div class="text-center">
                    <button wire:click="confirmAttendance" 
                            wire:loading.attr="disabled"
                            class="w-full bg-green-600 hover:bg-green-700 text-white font-bold py-4 px-6 rounded-lg transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                        <span wire:loading.remove>{{ __('seminar.confirm_visitor_attendance') }}</span>
                        <span wire:loading>{{ __('seminar.processing') }}...</span>
                    </button>
                    <p class="text-sm text-gray-500 mt-2">{{ __('seminar.admin_only_action') }}</p>
                </div>
            @endif

            @if($isAlreadyScanned || $showSuccess)
                <div class="bg-green-50 border border-green-200 rounded-lg p-4 text-center">
                    <div class="flex items-center justify-center gap-2">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <span class="font-semibold text-green-800">{{ __('seminar.visitor_attendance_confirmed') }}</span>
                    </div>
                    <p class="text-green-600 text-sm mt-1">{{ __('seminar.scanned_at') }}: {{ $this->scannedAt ?? now()->format('d M Y H:i') }}</p>
                </div>
            @endif
        </div>
    @endif
</div>
