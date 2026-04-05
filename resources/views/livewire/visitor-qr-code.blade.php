<div class="max-w-2xl mx-auto p-6">
    <div class="text-center mb-8">
        <img src="{{ asset('assets/images/JADE_PDGI_Light.webp') }}" alt="Jakarta Dental Exhibition 2026" class="h-24 mx-auto mb-4">
        <h1 class="text-2xl font-bold text-gray-800">{{ __('seminar.visitor_qr_code_title') }}</h1>
    </div>

    @if(!$isValid)
        <div class="bg-red-50 border border-red-200 rounded-lg p-6 text-center">
            <svg class="w-16 h-16 text-red-500 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <h2 class="text-xl font-bold text-red-800 mb-2">{{ __('seminar.qr_code_invalid') }}</h2>
            <p class="text-red-700">{{ __('seminar.qr_code_invalid_message') }}</p>
        </div>
    @else
        <div class="bg-white rounded-lg shadow-lg border border-gray-200 p-6">
            {{-- Visitor Details --}}
            <div class="mb-6 pb-6 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-800 mb-4">{{ __('seminar.visitor_details') }}</h2>
                <div class="space-y-2">
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
                    <div class="flex justify-between">
                        <span class="text-gray-600">{{ __('seminar.visitor_registration_id') }}:</span>
                        <span class="font-medium">VIS-{{ $visitor->id }}</span>
                    </div>
                </div>
            </div>

            {{-- QR Code --}}
            <div class="text-center">
                <div class="bg-gray-100 p-4 rounded-lg inline-block mb-4" style="max-width: 256px;">
                    <div style="width: 100%; aspect-ratio: 1 / 1;">
                        {!! DNS2D::getBarcodeHTML($this->qrCodeUrl, 'QRCODE', 8, 8) !!}
                    </div>
                </div>
                <p class="text-sm text-gray-500 mb-3 text-center">{{ __('seminar.visitor_qr_code_scan_instruction') }}</p>
            </div>

            {{-- Status --}}
            <div class="mt-6 p-4 rounded-lg {{ $this->scannedStatusColor === 'green' ? 'bg-green-50 border border-green-200' : 'bg-red-50 border border-red-200' }}">
                <div class="flex items-center justify-center gap-2">
                    @if($this->scannedStatusColor === 'green')
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    @else
                        <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    @endif
                    <span class="font-semibold {{ $this->scannedStatusColor === 'green' ? 'text-green-800' : 'text-red-800' }}">
                        {{ $this->scannedStatus }}
                    </span>
                </div>
                @if($visitor->scanned_at)
                    <p class="text-center text-sm mt-2 {{ $this->scannedStatusColor === 'green' ? 'text-green-600' : 'text-red-600' }}">
                        {{ __('seminar.scanned_at') }}: {{ $this->scannedAt }}
                    </p>
                @endif
            </div>
        </div>
    @endif
</div>
