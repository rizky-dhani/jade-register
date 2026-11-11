<div class="min-h-screen bg-gray-50 py-8 px-4">
    <div class="max-w-xl mx-auto">
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
            <h1 class="text-2xl font-bold text-gray-800 mb-2">{{ __('seminar.resend_confirmation_title') }}</h1>
            <p class="text-gray-600">{{ __('seminar.resend_confirmation_subtitle') }}</p>
        </div>

        @if($isSuccess)
            <div class="bg-green-50 border border-green-200 rounded-lg p-6 text-center">
                <svg class="w-16 h-16 text-green-500 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
                <h2 class="text-xl font-bold text-green-800 mb-2">{{ __('seminar.resend_confirmation_success_title') }}</h2>
                <p class="text-green-700 mb-4">{{ __('seminar.resend_confirmation_success_description') }}</p>
            </div>
        @else
            <form wire:submit.prevent="submit" class="space-y-6">
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('seminar.resend_confirmation_email_label') }} *</label>
                            <input
                                type="email"
                                wire:model.defer="email"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                autocomplete="email"
                                placeholder="you@example.com"
                            >
                            @error('email')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                    </div>
                </div>

                @if($errorMessage)
                    <div class="bg-red-50 border border-red-200 rounded-lg p-4 text-center">
                        <p class="text-red-700 text-sm">{{ $errorMessage }}</p>
                    </div>
                @endif

                <button
                    type="submit"
                    wire:loading.attr="disabled"
                    class="w-full bg-blue-600 text-white py-3 px-6 rounded-lg font-semibold hover:bg-blue-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                >
                    <span wire:loading.remove>{{ __('seminar.resend_confirmation_submit') }}</span>
                    <span wire:loading>{{ __('seminar.processing') }}...</span>
                </button>
            </form>
        @endif
    </div>
</div>