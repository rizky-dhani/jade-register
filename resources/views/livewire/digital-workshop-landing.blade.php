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
        <h1 class="text-3xl font-bold text-gray-800">{{ __('seminar.digital_workshop_title') }}</h1>
        <p class="text-gray-600 mt-2">{{ __('seminar.digital_workshop_subtitle') }}</p>
    </div>

    <div class="mb-8">
        <h2 class="text-xl font-bold text-gray-800 mb-4">{{ __('seminar.digital_workshop_expect_heading') }}</h2>

        <ul class="space-y-3">
            @foreach ([
                'digital_workshop_benefit_followers',
                'digital_workshop_benefit_algorithm',
                'digital_workshop_benefit_depth',
                'digital_workshop_benefit_followup',
            ] as $benefit)
                <li class="flex items-start gap-3">
                    <svg class="w-5 h-5 text-blue-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span class="text-gray-700">{{ __('seminar.'.$benefit) }}</span>
                </li>
            @endforeach
        </ul>
    </div>

    <p class="text-gray-700 mb-8">{{ __('seminar.digital_workshop_closing') }}</p>

    @if ($this->formattedStandalonePrice !== null)
        <div class="bg-gray-50 border border-gray-200 rounded-lg p-4 mb-6">
            <p class="text-gray-800">{{ __('seminar.digital_workshop_price_standalone', ['price' => $this->formattedStandalonePrice]) }}</p>
            @if ($this->formattedBundlePrice !== null)
                <p class="text-gray-800 mt-1">{{ __('seminar.digital_workshop_price_bundle', ['price' => $this->formattedBundlePrice]) }}</p>
            @endif
        </div>
    @endif

    @if ($workshop)
        <div class="text-center">
            <a
                href="{{ route('register.digital-workshop') }}"
                class="inline-block bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-8 rounded-lg transition"
            >
                {{ __('seminar.digital_workshop_cta') }}
            </a>
        </div>
    @else
        <p class="text-center text-gray-600">{{ __('seminar.digital_workshop_opens_soon') }}</p>
    @endif
</div>
