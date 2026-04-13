@php
    $registration = $getRecord();
    $seminar = $registration->seminarPackage;
@endphp

@if($seminar)
    <div class="flex flex-col gap-3">
        {{-- Seminar Package Name --}}
        <div class="flex items-center justify-between">
            <span class="text-sm text-gray-500 dark:text-gray-400">{{ __('seminar.seminar_package') }}</span>
            <span class="text-sm font-medium text-gray-900 dark:text-white">{{ $seminar->name }}</span>
        </div>

        {{-- Price Breakdown --}}
        @php
            // Check if early bird was active at the time of registration
            $wasEarlyBirdActive = $seminar->early_bird_deadline !== null 
                && $registration->created_at < $seminar->early_bird_deadline;
        @endphp

        <div class="space-y-2 rounded-lg bg-gray-50 p-3 dark:bg-gray-800">
            @if($wasEarlyBirdActive && $seminar->discounted_price)
                {{-- Early Bird Was Active: Show original with strikethrough --}}
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-500 dark:text-gray-400">{{ __('seminar.original_price') }}</span>
                    <span class="text-sm text-gray-500 line-through dark:text-gray-400">{{ $seminar->formatted_original_price }}</span>
                </div>

                {{-- Discounted Price --}}
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-500 dark:text-gray-400">{{ __('seminar.early_bird_price') }}</span>
                    <span class="text-sm font-semibold text-success-600 dark:text-success-400">{{ $seminar->formatted_discounted_price }}</span>
                </div>

                {{-- Savings --}}
                @if($seminar->savings_amount > 0)
                    <div class="flex items-center justify-between border-t border-gray-200 pt-2 dark:border-gray-700">
                        <span class="text-sm font-medium text-success-600 dark:text-success-400">{{ __('seminar.early_bird_savings') }}</span>
                        <span class="text-sm font-bold text-success-600 dark:text-success-400">-{{ $seminar->formatted_savings }}</span>
                    </div>
                @endif

                {{-- Early Bird Badge --}}
                <div class="mt-2">
                    <span class="inline-flex items-center gap-1 rounded-full bg-success-50 px-2 py-1 text-xs font-medium text-success-700 dark:bg-success-400/10 dark:text-success-400">
                        <svg class="h-3 w-3" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                            <path fill-rule="evenodd" d="M12 2.25c-5.385 0-9.75 4.365-9.75 9.75s4.365 9.75 9.75 9.75 9.75-4.365 9.75-9.75S17.385 2.25 12 2.25Zm4.28 10.28a.75.75 0 0 0 0-1.06l-3-3a.75.75 0 1 0-1.06 1.06l1.72 1.72H8.25a.75.75 0 0 0 0 1.5h5.69l-1.72 1.72a.75.75 0 1 0 1.06 1.06l3-3Z" clip-rule="evenodd" />
                        </svg>
                        {{ __('seminar.early_bird_active') }}
                    </span>
                </div>
            @else
                {{-- Regular Price Display --}}
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-500 dark:text-gray-400">{{ __('seminar.package_price') }}</span>
                    <span class="text-sm font-semibold text-gray-900 dark:text-white">{{ $seminar->formatted_original_price }}</span>
                </div>
            @endif
        </div>

        {{-- Registration Amount (what user actually paid) --}}
        <div class="flex items-center justify-between border-t border-gray-200 pt-3 dark:border-gray-700">
            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('seminar.amount_paid') }}</span>
            <span class="text-base font-bold text-gray-900 dark:text-white">{{ $registration->formatted_amount }}</span>
        </div>
    </div>
@else
    <span class="text-sm text-gray-500">—</span>
@endif
