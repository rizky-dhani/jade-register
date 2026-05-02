{{-- Hands On Selection Partial --}}
<div class="space-y-6 px-3 sm:px-0">
    @foreach($availableHandsOn as $date => $events)
        <div>
            <h4 class="font-semibold text-gray-800 mb-3">
                @php
                    $dayMap = ['2026-11-13' => 1, '2026-11-14' => 2, '2026-11-15' => 3];
                    $dayNumber = $dayMap[$date] ?? \Carbon\Carbon::parse($date)->format('j');
                @endphp
                {{ __('seminar.day_number', ['day' => 'ke-' . $dayNumber]) }}
            </h4>
            
            <div class="space-y-2">
                @foreach($events as $event)
                    @php
                        $isSelected = isset($selectedHandsOn[$date]) && $selectedHandsOn[$date] == $event['id'];
                    @endphp
                    
                    <div class="border rounded-lg transition-colors
                        {{ $event['is_full'] || !$event['has_price'] ? 'bg-gray-100 border-gray-200 opacity-60' : '' }}
                        {{ $isSelected ? 'border-blue-500 bg-blue-50' : 'border-gray-200' }}">
                        
                        <label class="flex-col sm:flex-row p-3 cursor-pointer">
                            
                            {{-- Flyer thumbnail - full width on mobile, side on desktop --}}
                            @if($event['flyer_url'])
                                <div class="mb-2 sm:mb-0 sm:mr-3 sm:flex-shrink-0">
                                    <a href="{{ $event['flyer_url'] }}"
                                        data-glightbox="title: {{ $event['ho_code'] ?? '' }} - {{ $event['name'] }}"
                                        class="glightbox block">
                                        <img src="{{ $event['flyer_url'] }}" alt="Flyer"
                                            class="w-full sm:w-36 h-auto rounded-lg shadow-sm border border-gray-200 object-contain">
                                    </a>
                                </div>
                            @endif
                            
                            <div class="flex items-center gap-3 flex-1 min-w-0">
                                <input type="radio" 
                                    name="hands_on_{{ $date }}"
                                    wire:model.live="selectedHandsOn.{{ $date }}"
                                    value="{{ $event['id'] }}"
                                    {{ $event['is_full'] || !$event['has_price'] ? 'disabled' : '' }}
                                    class="w-4 h-4 text-blue-600 border-gray-300 focus:ring-blue-500 flex-shrink-0">
                                
                                <div class="min-w-0 flex-1">
                                    <p class="font-medium text-gray-800">
                                        {{ $event['ho_code'] ?? '' }} - {{ $event['name'] }}
                                    </p>
                                    @if($event['doctor_name'])
                                        <p class="text-sm text-gray-500">{{ $event['doctor_name'] }}</p>
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
                                    @elseif($event['max_seats'] === null)
                                        <span class="inline-flex items-center px-2 py-0.5 mt-1 text-xs font-medium text-green-700 bg-green-100 rounded">
                                            {{ __('seminar.unlimited') }}
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
                                
                                <div class="text-right flex-shrink-0">
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
                            </div>
                        </label>

                        {{-- Show SKP image when selected --}}
                        @if($isSelected && $event['skp_url'])
                            <div class="px-3 pb-3 pt-1 border-t border-blue-200 mt-1">
                                <div class="flex">
                                    <div class="flex-1 min-w-0">
                                        <p class="text-xs font-medium text-gray-500 mb-1">SKP</p>
                                        <a href="{{ $event['skp_url'] }}"
                                            data-glightbox="title: SKP - {{ $event['ho_code'] ?? '' }} - {{ $event['name'] }}"
                                            class="glightbox block">
                                            <img src="{{ $event['skp_url'] }}" alt="SKP"
                                                class="w-full h-auto max-w-xs rounded-lg shadow-sm object-contain">
                                        </a>
                                        <a href="{{ $event['skp_url'] }}"
                                            data-glightbox="title: SKP - {{ $event['ho_code'] ?? '' }} - {{ $event['name'] }}"
                                            class="inline-flex items-center mt-2 px-3 py-1.5 text-xs font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition-colors">
                                            <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                            </svg>
                                            Lihat
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
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
