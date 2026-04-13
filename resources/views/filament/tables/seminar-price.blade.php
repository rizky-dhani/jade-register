@php
    $registration = $getRecord();
    $seminar = $registration->seminarPackage;
@endphp

@if($seminar)
    <div class="flex flex-col gap-1">
        @if($seminar->isEarlyBirdActive() && $seminar->discounted_price)
            <div class="flex items-center gap-2">
                <span class="text-sm text-gray-500 line-through">
                    {{ $seminar->formatted_original_price }}
                </span>
                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium bg-success-50 text-success-700 dark:bg-success-400/10 dark:text-success-400">
                    {{ __('seminar.early_bird') }}
                </span>
            </div>
            <div class="text-sm font-semibold text-gray-900 dark:text-white">
                {{ $seminar->formatted_current_price }}
            </div>
            @if($seminar->savings_amount > 0)
                <div class="text-xs text-success-600 dark:text-success-400">
                    {{ __('seminar.save_amount', ['amount' => $seminar->formatted_savings]) }}
                </div>
            @endif
        @else
            <div class="text-sm font-semibold text-gray-900 dark:text-white">
                {{ $seminar->formatted_current_price }}
            </div>
        @endif
    </div>
@else
    <span class="text-sm text-gray-500">—</span>
@endif
