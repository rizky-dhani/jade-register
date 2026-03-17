<div class="min-h-screen bg-gray-50 py-8 px-4">
    <div class="max-w-md mx-auto">
        <div class="text-center mb-6">
            <img src="{{ asset('assets/images/JADE_PDGI_Light.webp') }}" alt="Jakarta Dental Exhibition 2026" class="h-24 mx-auto mb-4">
            <h1 class="text-2xl font-bold text-gray-800">Your QR Code</h1>
        </div>

        @if(!$isValid)
            @if($isExpired)
                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-6 text-center">
                    <svg class="w-16 h-16 text-yellow-500 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                    </svg>
                    <h2 class="text-xl font-bold text-yellow-800 mb-2">QR Code Expired</h2>
                    <p class="text-yellow-700">This QR code has expired. Please contact the event organizers if you need assistance.</p>
                </div>
            @else
                <div class="bg-red-50 border border-red-200 rounded-lg p-6 text-center">
                    <svg class="w-16 h-16 text-red-500 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                    <h2 class="text-xl font-bold text-red-800 mb-2">Invalid QR Code</h2>
                    <p class="text-red-700">This QR code is not valid. Please check your link or contact the event organizers.</p>
                </div>
            @endif
        @else
            <div class="bg-white rounded-lg shadow-lg border border-gray-200 overflow-hidden">
                <div class="bg-blue-600 text-white px-6 py-4">
                    <h2 class="text-lg font-semibold">Participant Details</h2>
                </div>

                <div class="p-6">
                    <div class="space-y-3 mb-6">
                        <div>
                            <p class="text-sm text-gray-500">Name</p>
                            <p class="font-semibold text-gray-900">{{ $registration->name }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Email</p>
                            <p class="text-gray-900">{{ $registration->email }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Registration Code</p>
                            <p class="font-mono font-bold text-blue-600">{{ $registration->registration_code }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Payment Status</p>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-{{ $this->paymentStatusColor }}-100 text-{{ $this->paymentStatusColor }}-800">
                                {{ $this->paymentStatusLabel }}
                            </span>
                        </div>
                    </div>

                    @if($this->handsOnSessions->isNotEmpty())
                        <div class="border-t pt-4 mb-6">
                            <p class="text-sm text-gray-500 mb-2">Hands-On Sessions</p>
                            <div class="space-y-2">
                                @foreach($this->handsOnSessions as $session)
                                    <div class="bg-gray-50 rounded px-3 py-2">
                                        <p class="font-medium text-gray-900">{{ $session['name'] }}</p>
                                        <p class="text-sm text-gray-500">{{ $session['date'] }}</p>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <div class="border-t pt-4">
                        <p class="text-sm text-gray-500 mb-3 text-center">Scan this QR code at the venue</p>
                        <div class="bg-white p-4 rounded-lg border flex justify-center">
                            {!! DNS2D::getBarcodeHTML($this->qrCodeUrl, 'QRCODE', 8, 8) !!}
                        </div>
                    </div>

                    @if($registration->qr_expires_at)
                        <div class="mt-4 text-center text-sm text-gray-500">
                            <p>Valid until: {{ $registration->qr_expires_at->format('d M Y H:i') }}</p>
                        </div>
                    @endif
                </div>
            </div>
        @endif
    </div>
</div>