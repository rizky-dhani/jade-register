<div class="max-w-2xl mx-auto p-6">
    <div class="bg-green-50 border border-green-200 rounded-lg p-6 text-center">
        <svg class="w-16 h-16 text-green-500 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
        </svg>
        <h1 class="text-2xl font-bold text-green-800 mb-2">{{ __('seminar.digital_workshop_success_title') }}</h1>
        <p class="text-green-700">{{ __('seminar.digital_workshop_success_message') }}</p>
    </div>

    <div class="mt-6 border border-gray-200 rounded-lg p-6">
        <dl class="space-y-3">
            <div class="flex justify-between gap-4">
                <dt class="text-gray-600">{{ __('seminar.registration_code') }}</dt>
                <dd class="font-semibold text-gray-900">{{ $registration->registration_code }}</dd>
            </div>
            <div class="flex justify-between gap-4">
                <dt class="text-gray-600">{{ __('seminar.digital_workshop_title') }}</dt>
                <dd class="font-semibold text-gray-900">{{ $registration->digitalWorkshop?->name }}</dd>
            </div>
            <div class="flex justify-between gap-4">
                <dt class="text-gray-600">{{ __('seminar.event_date') }}</dt>
                <dd class="font-semibold text-gray-900">
                    {{ $registration->digitalWorkshop?->event_date?->format('d M Y') }}
                </dd>
            </div>
            <div class="flex justify-between gap-4">
                <dt class="text-gray-600">{{ __('seminar.total_amount') }}</dt>
                <dd class="font-semibold text-gray-900">
                    IDR {{ number_format($registration->amount, 0, ',', '.') }}
                </dd>
            </div>
            <div class="flex justify-between gap-4">
                <dt class="text-gray-600">{{ __('seminar.payment_status') }}</dt>
                <dd class="font-semibold text-gray-900">{{ ucfirst($registration->payment_status) }}</dd>
            </div>
        </dl>

        <p class="text-gray-600 text-sm mt-6 pt-6 border-t border-gray-200">
            {{ __('seminar.digital_workshop_confirmation_sent') }}
        </p>
    </div>
</div>
