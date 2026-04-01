{{-- Hands On Selection Partial --}}
<div class="space-y-6">
    @foreach($availableHandsOn as $date => $events)
        <div class="border border-gray-200 rounded-lg p-4">
            <h4 class="font-semibold text-gray-800 mb-3">
                {{ __('seminar.day_number', ['day' => \Carbon\Carbon::parse($date)->format('j')]) }}
            </h4>
            
            <div class="space-y-2">
                @foreach($events as $event)
                    @php
                        $isSelected = isset($selectedHandsOn[$date]) && $selectedHandsOn[$date] == $event['id'];
                    @endphp
                    
                    <label class="flex items-center justify-between p-3 border rounded-lg transition-colors
                        {{ $event['is_full'] || !$event['has_price'] ? 'bg-gray-100 border-gray-200 opacity-60' : '' }}
                        {{ $isSelected ? 'border-blue-500 bg-blue-50' : 'border-gray-200 hover:border-gray-300' }}
                        {{ !$event['has_price'] ? 'cursor-not-allowed' : 'cursor-pointer' }}">
                        
                        <div class="flex items-center gap-3">
                            <input type="radio" 
                                name="hands_on_{{ $date }}"
                                wire:model.live="selectedHandsOn.{{ $date }}"
                                value="{{ $event['id'] }}"
                                {{ $event['is_full'] || !$event['has_price'] ? 'disabled' : '' }}
                                class="w-4 h-4 text-blue-600 border-gray-300 focus:ring-blue-500">
                            
                            <div>
                                <p class="font-medium text-gray-800">{{ $event['name'] }}</p>
                                @if($event['description'])
                                    <p class="text-sm text-gray-600">{{ $event['description'] }}</p>
                                @endif
                                
                                {{-- Stock indicator --}}
                                @if(!$event['has_price'])
                                    <span class="inline-flex items-center px-2 py-0.5 mt-1 text-xs font-medium text-gray-600 bg-gray-200 rounded">
                                        {{ __('seminar.coming_soon') }}
                                    </span>
                                @elseif($event['is_full'])
                                    <span class="inline-flex items-center px-2 py-0.5 mt-1 text-xs font-medium text-red-700 bg-red-100 rounded">
                                        {{ __('seminar.sold_out') }}
                                    </span>
                                @elseif($event['remaining_stock'] <= 5)
                                    <span class="inline-flex items-center px-2 py-0.5 mt-1 text-xs font-medium text-orange-700 bg-orange-100 rounded">
                                        {{ $event['remaining_stock'] }} {{ __('seminar.seats_left') }}
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 mt-1 text-xs font-medium text-green-700 bg-green-100 rounded">
                                        {{ $event['remaining_stock'] }} {{ __('seminar.seats_left') }}
                                    </span>
                                @endif
                            </div>
                        </div>
                        
                        <div class="text-right">
                            {{-- Pricing with slash format --}}
                            @if(!$event['has_price'])
                                <div class="text-sm text-gray-500">
                                    {{ __('seminar.coming_soon') }}
                                </div>
                            @elseif($event['is_early_bird'] && $event['discounted_price'])
                                <div class="text-lg">
                                    <span class="text-gray-400 line-through text-sm">{{ $event['original_price'] }}</span>
                                    <span class="font-bold text-green-600">{{ $event['discounted_price'] }}</span>
                                </div>
                                <div class="text-xs text-green-600">
                                    {{ __('seminar.save_amount', ['amount' => $event['savings']]) }}
                                </div>
                            @else
                                <div class="font-semibold text-gray-700">
                                    {{ $event['original_price'] }}
                                </div>
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
