<div class="space-y-2 rounded-lg bg-gray-50 p-3 dark:bg-gray-800">
    @foreach($getRecord()->addonRegistrations as $registration)
        <div class="flex items-center justify-between gap-3 @if(!$loop->first) border-t border-gray-200 pt-2 dark:border-gray-700 @endif">
            <div class="flex flex-col min-w-0">
                <span class="text-sm font-medium text-gray-900 dark:text-white">{{ $registration->addon->name }}</span>
                <span class="text-sm text-gray-500 dark:text-gray-400">{{ Number::currency($registration->amount, $registration->currency) }}</span>
            </div>

            <div class="flex items-center gap-2 shrink-0">
                @if($registration->payment_status === 'pending')
                    <button type="button"
                            wire:click="mountAction('verifyAddonPayment', {{ json_encode(['addonRegistrationId' => $registration->id]) }})"
                            class="inline-flex items-center gap-1 px-2 py-1 text-xs font-medium rounded-lg bg-warning-100 text-warning-700 hover:bg-warning-200 dark:bg-warning-500/10 dark:text-warning-400 dark:hover:bg-warning-500/20 transition-colors">
                        <svg class="w-3.5 h-3.5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                        {{ __('seminar.verify_payment') }}
                    </button>
                @endif

                <span class="px-2 py-1 text-xs font-medium rounded-full whitespace-nowrap
                    @if($registration->payment_status === 'verified') bg-green-100 text-green-700 dark:bg-green-500/10 dark:text-green-400
                    @elseif($registration->payment_status === 'rejected') bg-red-100 text-red-700 dark:bg-red-500/10 dark:text-red-400
                    @else bg-yellow-100 text-yellow-700 dark:bg-yellow-500/10 dark:text-yellow-400 @endif">
                    {{ ucfirst($registration->payment_status) }}
                </span>
            </div>
        </div>
    @endforeach
</div>
