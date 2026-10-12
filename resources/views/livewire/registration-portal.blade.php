<div class="max-w-4xl mx-auto p-6">
    <div class="text-center mb-10">
        <div class="flex justify-end mb-4">
            <div class="flex items-center gap-2">
                <button
                    type="button"
                    wire:click="setLocale('en')"
                    class="text-sm font-medium {{ $locale === 'en' ? 'text-blue-600' : 'text-gray-500 hover:text-gray-700' }}"
                >
                    EN
                </button>
                <span class="text-gray-300">|</span>
                <button
                    type="button"
                    wire:click="setLocale('id')"
                    class="text-sm font-medium {{ $locale === 'id' ? 'text-blue-600' : 'text-gray-500 hover:text-gray-700' }}"
                >
                    ID
                </button>
            </div>
        </div>
        <img src="{{ asset('assets/images/JADE_PDGI_Light.webp') }}" alt="Jakarta Dental Exhibition 2026" class="h-36 mx-auto mb-4">
        <h1 class="text-3xl font-bold text-gray-800">{{ __('portal.title') }}</h1>
        <p class="text-gray-600 mt-2">{{ __('portal.subtitle') }}</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <a href="{{ route('register.seminar', ['lang' => $locale]) }}"
            wire:navigate
            class="group flex flex-col bg-white rounded-xl shadow-sm border border-gray-200 p-8 text-center hover:shadow-lg hover:border-blue-300 hover:-translate-y-1 transition-all duration-200">
            <div class="w-16 h-16 mx-auto mb-5 rounded-full bg-blue-50 group-hover:bg-blue-100 flex items-center justify-center transition-colors">
                <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                </svg>
            </div>
            <h2 class="text-xl font-semibold text-gray-800 mb-2">{{ __('portal.seminar_title') }}</h2>
            <p class="text-sm text-gray-600 mb-6">{{ __('portal.seminar_description') }}</p>
            <span class="inline-flex items-center justify-center mt-auto text-sm font-medium text-blue-600 group-hover:gap-2 gap-1 transition-all">
                {{ __('portal.register_now') }}
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
            </span>
        </a>

        <a href="{{ route('register.hands-on', ['lang' => $locale]) }}"
            wire:navigate
            class="group flex flex-col bg-white rounded-xl shadow-sm border border-gray-200 p-8 text-center hover:shadow-lg hover:border-teal-300 hover:-translate-y-1 transition-all duration-200">
            <div class="w-16 h-16 mx-auto mb-5 rounded-full bg-teal-50 group-hover:bg-teal-100 flex items-center justify-center transition-colors">
                <svg class="w-8 h-8 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M14.121 14.121L19 19m-7-7l2.879-2.879m0 0a3 3 0 104.243-4.242 3 3 0 00-4.243 4.242zM21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
            <h2 class="text-xl font-semibold text-gray-800 mb-2">{{ __('portal.hands_on_title') }}</h2>
            <p class="text-sm text-gray-600 mb-6">{{ __('portal.hands_on_description') }}</p>
            <span class="inline-flex items-center justify-center mt-auto text-sm font-medium text-teal-600 group-hover:gap-2 gap-1 transition-all">
                {{ __('portal.register_now') }}
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
            </span>
        </a>

        <a href="{{ route('register.visitor', ['lang' => $locale]) }}"
            wire:navigate
            class="group flex flex-col bg-white rounded-xl shadow-sm border border-gray-200 p-8 text-center hover:shadow-lg hover:border-amber-300 hover:-translate-y-1 transition-all duration-200">
            <div class="w-16 h-16 mx-auto mb-5 rounded-full bg-amber-50 group-hover:bg-amber-100 flex items-center justify-center transition-colors">
                <svg class="w-8 h-8 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                </svg>
            </div>
            <h2 class="text-xl font-semibold text-gray-800 mb-2">{{ __('portal.visitor_title') }}</h2>
            <p class="text-sm text-gray-600 mb-6">{{ __('portal.visitor_description') }}</p>
            <span class="inline-flex items-center justify-center mt-auto text-sm font-medium text-amber-600 group-hover:gap-2 gap-1 transition-all">
                {{ __('portal.register_now') }}
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
            </span>
        </a>

        <a href="{{ route('register.digital-workshop', ['lang' => $locale]) }}"
            wire:navigate
            class="group flex flex-col bg-white rounded-xl shadow-sm border border-gray-200 p-8 text-center hover:shadow-lg hover:border-violet-300 hover:-translate-y-1 transition-all duration-200">
            <div class="w-16 h-16 mx-auto mb-5 rounded-full bg-violet-50 group-hover:bg-violet-100 flex items-center justify-center transition-colors">
                <svg class="w-8 h-8 text-violet-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                </svg>
            </div>
            <h2 class="text-xl font-semibold text-gray-800 mb-2">{{ __('portal.digital_workshop_title') }}</h2>
            <p class="text-sm text-gray-600 mb-6">{{ __('portal.digital_workshop_description') }}</p>
            <span class="inline-flex items-center justify-center mt-auto text-sm font-medium text-violet-600 group-hover:gap-2 gap-1 transition-all">
                {{ __('portal.register_now') }}
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
            </span>
        </a>
    </div>
</div>
