@php
    /** @var string|null $in */
    /** @var string|null $out */
@endphp

@if ($in === null)
    <span class="text-sm text-gray-400 dark:text-gray-500">-</span>
@else
    <div class="flex flex-col items-start gap-1">
        <span class="inline-flex items-center rounded-md bg-success-50 px-1.5 py-0.5 text-xs font-medium text-success-700 dark:bg-success-400/10 dark:text-success-400">
            {{ $in }}
        </span>

        @if ($out !== null)
            <span class="inline-flex items-center rounded-md bg-gray-100 px-1.5 py-0.5 text-xs font-medium text-gray-600 dark:bg-white/10 dark:text-gray-300">
                {{ $out }}
            </span>
        @endif
    </div>
@endif
