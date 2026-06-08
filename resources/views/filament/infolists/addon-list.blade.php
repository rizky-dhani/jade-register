<div class="space-y-3">
    @foreach($getRecord()->addonRegistrations as $registration)
        <div class="p-3 bg-gray-50 rounded-lg">
            <div class="flex justify-between items-center">
                <div>
                    <p class="font-medium">{{ $registration->addon->name }}</p>
                    <p class="text-sm text-gray-600">{{ Number::currency($registration->amount, $registration->currency) }}</p>
                </div>
                <span class="px-2 py-1 text-xs font-medium rounded-full
                    @if($registration->payment_status === 'verified') bg-green-100 text-green-700
                    @elseif($registration->payment_status === 'rejected') bg-red-100 text-red-700
                    @else bg-yellow-100 text-yellow-700 @endif">
                    {{ ucfirst($registration->payment_status) }}
                </span>
            </div>
        </div>
    @endforeach
</div>
