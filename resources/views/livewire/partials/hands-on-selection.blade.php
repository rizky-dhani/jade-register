{{-- Hands On Selection Partial --}}
<div class="space-y-6">
    @foreach($availableHandsOn as $date => $events)
        <div class="border border-gray-200 rounded-lg p-4">
            <h4 class="font-semibold text-gray-800 mb-3">
                {{ \Carbon\Carbon::parse($date)->format('F d, Y') }}
            </h4>
            
            <div class="space-y-2">
                @foreach($events as $event)
                    @php
                        $isSelected = isset($selectedHandsOn[$date]) && $selectedHandsOn[$date] == $event['id'];
                    @endphp
                    
                    <label class="flex items-center justify-between p-3 border rounded-lg cursor-pointer transition-colors
                        {{ $event['is_full'] ? 'bg-gray-100 border-gray-200 opacity-60' : '' }}
                        {{ $isSelected ? 'border-blue-500 bg-blue-50' : 'border-gray-200 hover:border-gray-300' }}">
                        
                        <div class="flex items-center gap-3">
                            <input type="radio" 
                                name="hands_on_{{ $date }}"
                                wire:model.live="selectedHandsOn.{{ $date }}"
                                value="{{ $event['id'] }}"
                                {{ $event['is_full'] ? 'disabled' : '' }}
                                class="w-4 h-4 text-blue-600 border-gray-300 focus:ring-blue-500">
                            
                            <div>
                                <p class="font-medium text-gray-800">{{ $event['name'] }}</p>
                                @if($event['description'])
                                    <p class="text-sm text-gray-600">{{ $event['description'] }}</p>
                                @endif
                            </div>
                        </div>
                        
                        <div class="text-right">
                            <p class="font-semibold text-gray-700">Rp {{ number_format($event['price'], 0, ',', '.') }}</p>
                            @if($event['is_full'])
                                <span class="text-xs font-medium text-red-600">Full</span>
                            @else
                                <span class="text-xs {{ $event['available_seats'] <= 5 ? 'text-orange-600 font-medium' : 'text-green-600' }}">
                                    {{ $event['available_seats'] }} seats left
                                </span>
                            @endif
                        </div>
                    </label>
                @endforeach
            </div>
        </div>
    @endforeach
    
    @if($handsOnTotalPrice > 0)
        <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
            <div class="flex justify-between items-center">
                <span class="font-semibold text-gray-800">{{ __('seminar.hands_on_total') }}</span>
                <span class="text-xl font-bold text-gray-800">Rp {{ number_format($handsOnTotalPrice, 0, ',', '.') }}</span>
            </div>
        </div>
    @endif
</div>
