<div class="max-w-4xl mx-auto p-6">
    <div class="text-center mb-8">
        <img src="{{ asset('assets/images/JADE_PDGI_Light.webp') }}" alt="Jakarta Dental Exhibition 2026" class="h-24 mx-auto mb-4">
        <h1 class="text-3xl font-bold text-gray-800">{{ __('seminar.total_participant_title') }}</h1>
        <p class="text-gray-600 mt-2">{{ __('seminar.total_participant_subtitle') }}</p>
    </div>

    {{-- Totals --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-8">
        <div class="bg-white rounded-lg shadow border border-blue-200 p-6 text-center">
            <p class="text-4xl font-bold text-blue-600">{{ number_format($totalSeminar) }}</p>
            <p class="text-gray-600 mt-1">{{ __('seminar.total_seminar_registrations') }}</p>
        </div>
        <div class="bg-white rounded-lg shadow border border-green-200 p-6 text-center">
            <p class="text-4xl font-bold text-green-600">{{ number_format($totalHandsOn) }}</p>
            <p class="text-gray-600 mt-1">{{ __('seminar.total_hands_on_registrations') }}</p>
        </div>
    </div>

    {{-- Seminar Packages --}}
    @if(count($seminarPackages))
    <div class="mb-8">
        <h2 class="text-xl font-semibold text-gray-800 mb-4">{{ __('seminar.seminar_package_breakdown') }}</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($seminarPackages as $pkg)
                <div class="bg-white rounded-lg shadow border border-gray-200 p-4">
                    <p class="font-semibold text-gray-900">{{ $pkg['name'] }}</p>
                    <p class="text-sm text-gray-500">{{ $pkg['code'] }}</p>
                    <p class="text-2xl font-bold text-blue-600 mt-2">{{ number_format($pkg['count']) }}</p>
                    @if($pkg['max_seats'])
                        <p class="text-xs text-gray-400 mt-1">{{ __('seminar.seats') }}: {{ number_format($pkg['count']) }} / {{ number_format($pkg['max_seats']) }}</p>
                    @endif
                </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Hands-On Sessions --}}
    @if(count($handsOnSessions))
    <div>
        <h2 class="text-xl font-semibold text-gray-800 mb-4">{{ __('seminar.hands_on_session_breakdown') }}</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($handsOnSessions as $ho)
                <div class="bg-white rounded-lg shadow border border-gray-200 p-4">
                    <p class="font-semibold text-gray-900">{{ $ho['name'] }}</p>
                    <p class="text-sm text-gray-500">{{ $ho['ho_code'] }}</p>
                    @if($ho['doctor_name'])
                        <p class="text-xs text-gray-400">Dr. {{ $ho['doctor_name'] }}</p>
                    @endif
                    <p class="text-2xl font-bold text-green-600 mt-2">{{ number_format($ho['count']) }}</p>
                    @if($ho['max_seats'])
                        <p class="text-xs text-gray-400 mt-1">{{ __('seminar.seats') }}: {{ number_format($ho['count']) }} {{ __('seminar.of') }} {{ number_format($ho['max_seats']) }}</p>
                    @endif
                    @if($ho['event_date'])
                        <p class="text-xs text-gray-400">{{ $ho['event_date'] }}</p>
                    @endif
                </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Empty state --}}
    @if(!count($seminarPackages) && !count($handsOnSessions))
        <div class="text-center py-12 text-gray-400">
            <p>{{ __('seminar.no_data_available') }}</p>
        </div>
    @endif
</div>
