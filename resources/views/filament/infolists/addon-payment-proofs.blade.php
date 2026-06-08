@php
    $addonRegistrations = $getRecord()->addonRegistrations->filter(fn ($r) => $r->payment_proof_path !== null);
@endphp

@if($addonRegistrations->isNotEmpty())
    <div class="space-y-3">
        @foreach($addonRegistrations as $registration)
            <button type="button"
                    wire:click="mountAction('viewAddonPaymentProof', {{ json_encode(['addonRegistrationId' => $registration->id]) }})"
                    class="w-full flex items-center justify-between p-3 rounded-lg bg-gray-50 hover:bg-gray-100 dark:bg-gray-800 dark:hover:bg-gray-700 transition-colors text-left">
                <span class="text-sm font-medium text-gray-900 dark:text-white">{{ $registration->addon->name }}</span>
                <span class="inline-flex items-center gap-1 text-xs font-medium text-primary-600 dark:text-primary-400">
                    <svg class="w-3.5 h-3.5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/>
                        <circle cx="12" cy="12" r="3"/>
                    </svg>
                    {{ __('filament.actions.view') }}
                </span>
            </button>
        @endforeach
    </div>
@endif
