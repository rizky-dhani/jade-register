<div class="max-w-2xl mx-auto p-6">
    <div class="text-center mb-8">
        <div class="flex justify-end mb-4">
            <div class="flex items-center gap-2">
                <button
                    wire:click="setLocale('en')"
                    class="text-sm font-medium {{ $locale === 'en' ? 'text-blue-600' : 'text-gray-500 hover:text-gray-700' }}"
                >
                    EN
                </button>
                <span class="text-gray-300">|</span>
                <button
                    wire:click="setLocale('id')"
                    class="text-sm font-medium {{ $locale === 'id' ? 'text-blue-600' : 'text-gray-500 hover:text-gray-700' }}"
                >
                    ID
                </button>
            </div>
        </div>
        <img src="{{ asset('assets/images/JADE_PDGI_Light.webp') }}" alt="Jakarta Dental Exhibition 2026" class="h-36 mx-auto mb-4">
        <h1 class="text-3xl font-bold text-gray-800">{{ __('seminar.visitor_registration_title') }}</h1>
        <p class="text-gray-600 mt-2">{{ __('seminar.visitor_registration_subtitle') }}</p>
    </div>

    @if($isSuccess)
        <div class="bg-green-50 border border-green-200 rounded-lg p-6 text-center">
            <svg class="w-16 h-16 text-green-500 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
            </svg>
            <h2 class="text-2xl font-bold text-green-800 mb-2">{{ __('seminar.visitor_registration_success_title') }}</h2>
            <p class="text-green-700 mb-4">{{ __('seminar.visitor_registration_thank_you') }}, {{ $visitor->name }}!</p>
            <p class="text-gray-600 mb-6">{{ __('seminar.visitor_confirmation_email_sent') }} {{ $visitor->email }}</p>
            
            {{-- QR Code Display --}}
            @if($visitor->barcode)
                <div class="mt-6 pt-6 border-t border-green-200">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">{{ __('seminar.visitor_qr_code_title') }}</h3>
                    <p class="text-gray-600 mb-4">{{ __('seminar.visitor_qr_code_description') }}</p>
                    <div class="bg-white p-4 rounded-lg inline-block shadow-md">
                        <img src="{{ asset('storage/'.$visitor->barcode) }}" alt="QR Code" class="w-48 h-48">
                    </div>
                    <p class="text-sm text-gray-500 mt-4 text-center">{{ __('seminar.visitor_qr_code_scan_instruction') }}</p>
                </div>
            @endif
        </div>
    @else
        <form wire:submit.prevent="submit" class="space-y-6">
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h2 class="text-xl font-semibold text-gray-800 mb-4">{{ __('seminar.visitor_personal_information_section') }}</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('seminar.visitor_name_label') }} *</label>
                        <input type="text" wire:model="name" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        @error('name') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('seminar.visitor_email_label') }} *</label>
                        <input type="email" wire:model="email" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        @error('email') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('seminar.visitor_phone_label') }} *</label>
                        <input type="text" wire:model="phone" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        @error('phone') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('seminar.visitor_affiliation_label') }}</label>
                        <input type="text" wire:model="affiliation" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        @error('affiliation') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            <button type="submit" wire:loading.attr="disabled" :disabled="$isSubmitting" class="w-full bg-blue-600 text-white py-3 px-6 rounded-lg font-semibold hover:bg-blue-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                <span wire:loading.remove>{{ __('seminar.visitor_register_button') }}</span>
                <span wire:loading>{{ __('seminar.processing') }}...</span>
            </button>
        </form>
    @endif
</div>
