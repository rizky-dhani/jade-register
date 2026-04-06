<div class="max-w-2xl mx-auto p-6" x-data="{ locale: @entangle('locale'), countryOpen: false, countrySearch: '', paymentMethod: @entangle('payment_method') }" x-init="
    const savedLocale = localStorage.getItem('jade_locale');
    if (savedLocale && ['en', 'id'].includes(savedLocale)) {
        locale = savedLocale;
    }
    $watch('locale', value => {
        localStorage.setItem('jade_locale', value);
    });
" x-on:focus-element.window="
    const el = document.querySelector($event.detail.selector);
    if (el) {
        el.focus();
        el.select();
    }
">
    <div class="text-center mb-8">
        <div class="flex justify-end mb-4">
            <div class="flex items-center gap-2">
                <button
                    type="button"
                    wire:click="setLocale('en')"
                    class="text-sm font-medium {{ $locale === 'en' ? 'text-blue-600' : 'text-gray-500 hover:text-gray-700' }}"
                >
                    EN
                </button>
                <span class="text-gray-300">|</span>
                <button
                    type="button"
                    wire:click="setLocale('id')"
                    class="text-sm font-medium {{ $locale === 'id' ? 'text-blue-600' : 'text-gray-500 hover:text-gray-700' }}"
                >
                    ID
                </button>
            </div>
        </div>
        <img src="{{ asset('assets/images/JADE_PDGI_Light.webp') }}" alt="Jakarta Dental Exhibition 2026" class="h-36 mx-auto mb-4">
        <h1 class="text-3xl font-bold text-gray-800">{{ __('seminar.page_title') }}</h1>
        <p class="text-gray-600 mt-2">{{ __('seminar.page_subtitle') }}</p>
    </div>

        <form wire:key="form-state" wire:submit="submit" class="space-y-6">

            {{-- Already Registered Check --}}
            @if($is_already_registered === null)
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h2 class="text-xl font-semibold text-gray-800 mb-4">{{ __('seminar.already_registered') }}</h2>
                <p class="text-gray-600 mb-4">{{ __('seminar.already_registered_help') }}</p>
                
                <div class="grid grid-cols-2 gap-4">
                    <button type="button" wire:click="$set('is_already_registered', 'no')"
                        class="w-full px-6 py-3 border-2 rounded-lg font-medium transition-colors
                        {{ $is_already_registered === 'no' ? 'border-blue-500 bg-blue-50 text-blue-700' : 'border-gray-300 hover:border-gray-400' }}">
                        {{ __('seminar.no') }}
                    </button>
                    <button type="button" wire:click="$set('is_already_registered', 'yes')" 
                        class="w-full px-6 py-3 border-2 rounded-lg font-medium transition-colors
                        {{ $is_already_registered === 'yes' ? 'border-blue-500 bg-blue-50 text-blue-700' : 'border-gray-300 hover:border-gray-400' }}">
                        {{ __('seminar.yes') }}
                    </button>
                </div>
            </div>
            @endif

            {{-- Email Verification for Existing Registration --}}
            @if($is_already_registered === 'yes' && !$existingRegistration)
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h2 class="text-xl font-semibold text-gray-800 mb-4">{{ __('seminar.verify_registration') }}</h2>
                <div class="flex gap-2">
                    <input type="email" wire:model="verification_email" 
                        placeholder="{{ __('seminar.enter_email') }}"
                        class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <button type="button" wire:click="checkExistingRegistration" 
                        wire:loading.attr="disabled"
                        class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50">
                        <span wire:loading.remove wire:target="checkExistingRegistration">
                            {{ __('seminar.check') }}
                        </span>
                        <span wire:loading wire:target="checkExistingRegistration">
                            {{ __('seminar.checking') }}...
                        </span>
                    </button>
                </div>
                @if($showVerificationError)
                    <p class="text-red-500 text-sm mt-2">{{ __('seminar.registration_not_found') }}</p>
                    <p class="text-sm mt-1">
                        <a href="#" wire:click="$set('is_already_registered', 'no')" class="text-blue-600 underline">
                            {{ __('seminar.register_here') }}
                        </a>
                    </p>
                @endif
                <p class="text-sm mt-3 text-gray-600">
                    {{ __('seminar.not_registered_yet') }}
                    <a href="#" wire:click="$set('is_already_registered', 'no')" class="text-blue-600 underline font-medium">
                        {{ __('seminar.register_new') }}
                    </a>
                </p>
            </div>
            @endif

            {{-- Existing Registration Found - Show Details and Hands On Selection --}}
            @if($existingRegistration)
            <div class="bg-green-50 border border-green-200 rounded-lg p-6">
                <h2 class="text-xl font-semibold text-green-800 mb-4">{{ __('seminar.registration_found') }}</h2>
                
                {{-- Registration Details --}}
                <div class="space-y-2 mb-6 bg-white rounded-lg p-4 border border-green-200">
                    <p><strong>{{ __('seminar.name') }}:</strong> {{ $existingRegistration->name }}</p>
                    <p><strong>{{ __('seminar.email') }}:</strong> {{ $existingRegistration->email }}</p>
                    <p><strong>{{ __('seminar.registration_code') }}:</strong> {{ $existingRegistration->registration_code }}</p>
                    <p><strong>{{ __('seminar.selected_package') }}:</strong> {{ $existingRegistration->selected_seminar }}</p>
                    <p><strong>{{ __('seminar.payment_status') }}:</strong> 
                        <span class="{{ $existingRegistration->payment_status === 'verified' ? 'text-green-600 font-medium' : 'text-yellow-600 font-medium' }}">
                            {{ ucfirst($existingRegistration->payment_status) }}
                        </span>
                    </p>
                </div>

                @if($existingRegistration->payment_status === 'verified')
                    {{-- Hands On Selection (NO checkbox, direct selection) --}}
                    <div class="bg-white rounded-lg p-4 border border-green-200">
                        <h3 class="text-lg font-semibold text-gray-800 mb-2">{{ __('seminar.select_hands_on') }}</h3>
                        <p class="text-gray-600 mb-4 text-sm">{{ __('seminar.hands_on_separate_payment') }}</p>
                        
                        @include('livewire.partials.hands-on-selection', ['isSeparate' => true])
                    </div>
                @else
                    <p class="text-yellow-700">{{ __('seminar.complete_payment_first') }}</p>
                @endif
            </div>
            @endif

            {{-- Main Registration Form (Only show if NOT already registered) --}}
            @if($is_already_registered === 'no')
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h2 class="text-xl font-semibold text-gray-800 mb-4">{{ __('seminar.personal_information') }}</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('seminar.country') }} *</label>
                        {{-- Searchable Country Dropdown --}}
                        <div class="relative" @click.away="countryOpen = false">
                            <button type="button" @click="countryOpen = !countryOpen; if (countryOpen) $nextTick(() => $refs.countrySearch.focus())" 
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg text-left bg-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 flex justify-between items-center">
                                <span>
                                    {{ $country_id ? $countries->firstWhere('id', $country_id)->name : __('seminar.select_country') }}
                                </span>
                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </button>
                            
                            <div x-show="countryOpen" x-cloak 
                                class="absolute z-50 w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-lg max-h-80 overflow-y-auto">
                                <div class="p-2 border-b border-gray-200">
                                    <input type="text" x-model="countrySearch" x-ref="countrySearch"
                                        placeholder="{{ __('seminar.search_country') }}"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                        @keydown.escape="countryOpen = false">
                                </div>
                                <div class="max-h-60 overflow-y-auto">
                                    @foreach($countries as $country)
                                        <button type="button" 
                                            x-show="!countrySearch || '{{ strtolower($country->name) }}'.includes(countrySearch.toLowerCase())"
                                            wire:click="$set('country_id', {{ $country->id }}); countryOpen = false; countrySearch = ''"
                                            class="w-full px-4 py-2 text-left hover:bg-gray-100 {{ $country_id === $country->id ? 'bg-blue-50 text-blue-700' : '' }}">
                                            {{ $country->name }}
                                        </button>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                        @error('country_id') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            @if($country_id && $isIndonesia)
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h2 class="text-xl font-semibold text-gray-800 mb-4">{{ __('seminar.local_participant') }}</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('seminar.name_str') }} *</label>
                        <input type="text" wire:model="name" wire:key="name-str" autocomplete="name" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        @error('name') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('seminar.name_plataran') }} *</label>
                        <input type="text" wire:model="name_license" wire:key="name-plataran" autocomplete="name" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        @error('name_license') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('seminar.email_plataran') }} *</label>
                        <input type="email" wire:model.live.debounce.500ms="email" wire:key="email-plataran" autocomplete="email" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        @if($emailTaken)
                            <p class="text-red-500 text-sm mt-1">{{ __('seminar.email_already_registered') }}</p>
                        @endif
                        @error('email') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('seminar.whatsapp_number') }} *</label>
                        <input type="tel" wire:model="phone" autocomplete="tel" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="+6281234567890">
                        @error('phone') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('seminar.nik') }} *</label>
                        <input type="tel" wire:model="nik" inputmode="numeric" maxlength="16" pattern="[0-9]{16}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <p class="text-xs text-gray-500 mt-1">{{ __('seminar.nik_helper') }}</p>
                        @error('nik') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('seminar.pdgi_branch') }} *</label>
                        <input type="text" wire:model="pdgi_branch" autocomplete="organization" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        @error('pdgi_branch') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                    </div>
                    
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('seminar.competency') }} *</label>
                        <select wire:model="kompetensi" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="">{{ __('seminar.select_competency') }}</option>
                            <option value="Dokter Gigi Umum">{{ __('seminar.competency_gp') }}</option>
                            <option value="Sp.KG">{{ __('seminar.competency_sp_kg') }}</option>
                            <option value="Sp.KGA" disabled>{{ __('seminar.competency_sp_kga') }}</option>
                            <option value="Sp.Pros">{{ __('seminar.competency_sp_pros') }}</option>
                            <option value="Sp.B.M.M" disabled>{{ __('seminar.competency_sp_bmm') }}</option>
                            <option value="Sp.Perio">{{ __('seminar.competency_sp_perio') }}</option>
                            <option value="Sp.Ort" disabled>{{ __('seminar.competency_sp_ort') }}</option>
                            <option value="Sp.RKG">{{ __('seminar.competency_sp_rkg') }}</option>
                            <option value="Sp.PM">{{ __('seminar.competency_sp_pm') }}</option>
                            <option value="Sp.OF">{{ __('seminar.competency_sp_of') }}</option>
                            <option value="Sp.PMM">{{ __('seminar.competency_sp_pmm') }}</option>
                            <option value="Mahasiswa Kedokteran Gigi">{{ __('seminar.competency_dental_student') }}</option>
                            <option value="drg Internship">{{ __('seminar.competency_dentist_internship') }}</option>
                        </select>
                        @error('kompetensi') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>
            @elseif($country_id && !$isIndonesia)
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h2 class="text-xl font-semibold text-gray-800 mb-4">{{ __('seminar.international_participant') }}</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('seminar.name') }} *</label>
                        <input type="text" wire:model="name" wire:key="name-intl" autocomplete="name" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        @error('name') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('seminar.email') }} *</label>
                        <input type="email" wire:model="email" wire:key="email-intl" autocomplete="email" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        @error('email') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('seminar.whatsapp_number') }} *</label>
                        <input type="tel" wire:model="phone" wire:key="whatsapp-intl" autocomplete="tel" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="+1234567890">
                        @error('phone') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('seminar.status') }} *</label>
                        <select wire:model="status" wire:key="status-intl" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="">{{ __('seminar.select_status') }}</option>
                            <option value="Dentist">{{ __('seminar.dentist') }}</option>
                            <option value="Student">{{ __('seminar.student') }}</option>
                        </select>
                        @error('status') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>
            @endif

            @if($country_id)
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h2 class="text-xl font-semibold text-gray-800 mb-4">{{ __('seminar.registration_package') }}</h2>

                <div class="md:col-span-2">
                    <p class="text-xs text-gray-500 mb-3">{{ __('seminar.valid_until') }}</p>

                    @php
                    $tiers = $availableTiers ?? [];
                    @endphp
                    
                    @if(count($tiers) === 0)
                        <p class="text-gray-500 text-sm">{{ __('seminar.select_country_first') }}</p>
                    @else
                        <div class="grid grid-cols-1 gap-4">
                            @foreach($tiers as $tier)
                                <button
                                    type="button"
                                    wire:click="$set('selected_seminar', '{{ $tier['value'] }}')"
                                    class="p-4 rounded-lg border-2 text-left transition-all flex justify-between items-center {{ $selected_seminar === $tier['value'] ? 'border-blue-500 bg-blue-50' : ($tier['is_full'] ? 'border-gray-300 bg-gray-50 opacity-60' : 'border-gray-200 hover:border-gray-300 hover:bg-gray-50') }}"
                                    {{ $tier['is_full'] ? 'disabled' : '' }}
                                >
                                    <div class="flex-1">
                                        <div class="font-medium text-gray-800">{{ $tier['label'] }}</div>
                                        
                                        {{-- Stock indicator --}}
                                        @if($tier['is_full'])
                                            <span class="inline-flex items-center px-2 py-1 mt-1 text-xs font-medium text-red-700 bg-red-100 rounded">
                                                {{ __('seminar.sold_out') }}
                                            </span>
                                        @elseif($tier['remaining_stock'] <= 10)
                                            <span class="inline-flex items-center px-2 py-1 mt-1 text-xs font-medium text-orange-700 bg-orange-100 rounded">
                                                {{ __('seminar.limited_seats', ['count' => $tier['remaining_stock']]) }}
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2 py-1 mt-1 text-xs font-medium text-green-700 bg-green-100 rounded">
                                                {{ __('seminar.available') }}
                                            </span>
                                        @endif
                                    </div>
                                    
                                    <div class="text-right ml-4">
                                        {{-- Pricing with slash format --}}
                                        @if($tier['is_early_bird'] && $tier['discounted_price'])
                                            <div class="text-lg">
                                                <span class="text-gray-400 line-through text-sm">{{ $tier['original_price'] }}</span>
                                                <span class="font-bold text-green-600">{{ $tier['discounted_price'] }}</span>
                                            </div>
                                            <div class="text-xs text-green-600">
                                                {{ __('seminar.save_amount', ['amount' => $tier['savings']]) }}
                                            </div>
                                        @else
                                            <div class="font-semibold text-gray-700">{{ $tier['original_price'] }}</div>
                                        @endif
                                    </div>
                                </button>
                            @endforeach
                        </div>
                    @endif
                    @error('selected_seminar') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                </div>
            </div>
            @endif

            {{-- Hands On Section --}}
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h2 class="text-xl font-semibold text-gray-800 mb-4">{{ __('seminar.hands_on_sessions') }}</h2>
                
                <div class="flex items-start mb-4">
                    <input type="checkbox" wire:model.live="wants_hands_on" id="wants_hands_on" 
                        class="w-5 h-5 text-blue-600 border-gray-300 rounded focus:ring-blue-500 mt-0.5">
                    <label for="wants_hands_on" class="ml-2 text-sm text-gray-700">
                        {{ __('seminar.want_to_join_hands_on') }}
                    </label>
                </div>
                
                @if($wants_hands_on)
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                        <p class="text-sm text-blue-700 mb-4">
                            {{ __('seminar.hands_on_description') }}
                        </p>
                        
                        @include('livewire.partials.hands-on-selection', ['isSeparate' => false])
                    </div>
                @endif
            </div>

            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h2 class="text-xl font-semibold text-gray-800 mb-4">{{ __('seminar.contact_person') }}</h2>
                
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <p class="text-sm text-blue-700 mb-3">{{ __('seminar.contact_description') }}</p>
                    <div class="space-y-2">
                        <p class="text-sm text-blue-700"><strong>Drg Eka:</strong> <a href="https://wa.me/6285147013396" target="_blank" class="underline hover:text-blue-900">085147013396</a></p>
                        <p class="text-sm text-blue-700"><strong>Drg Helani:</strong> <a href="https://wa.me/628118766161" target="_blank" class="underline hover:text-blue-900">08118766161</a></p>
                        <p class="text-sm text-blue-700"><strong>Drg Fitri:</strong> <a href="https://wa.me/6281255710448" target="_blank" class="underline hover:text-blue-900">081255710448</a></p>
                        <p class="text-sm text-blue-700"><strong>Drg Annisa:</strong> <a href="https://wa.me/628195090409" target="_blank" class="underline hover:text-blue-900">08195090409</a></p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h2 class="text-xl font-semibold text-gray-800 mb-4">{{ __('seminar.payment_information') }}</h2>
                
                {{-- Payment Method Selection --}}
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-3">{{ __('seminar.payment_method') }}</label>
                    <div class="grid grid-cols-2 gap-4">
                        <button type="button" 
                            @click="paymentMethod = 'bank_transfer'"
                            :class="paymentMethod === 'bank_transfer' ? 'border-blue-500 bg-blue-50 text-blue-700' : 'border-gray-300 hover:border-gray-400'"
                            class="w-full px-6 py-3 border-2 rounded-lg font-medium transition-colors">
                            {{ __('seminar.bank_transfer') }}
                        </button>
                        <button type="button" 
                            @click="paymentMethod = 'qris'"
                            :class="paymentMethod === 'qris' ? 'border-blue-500 bg-blue-50 text-blue-700' : 'border-gray-300 hover:border-gray-400'"
                            class="w-full px-6 py-3 border-2 rounded-lg font-medium transition-colors">
                            QRIS
                        </button>
                    </div>
                </div>
                
                {{-- Bank Transfer Details (conditional) --}}
                <div x-show="paymentMethod === 'bank_transfer'" x-transition>
                    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-4">
                        <h3 class="font-semibold text-yellow-800 mb-2">{{ __('seminar.bank_transfer_details') }}</h3>
                        @if($isIndonesia)
                        <p class="text-sm text-yellow-700"><strong>{{ __('seminar.bank') }}:</strong> {{ config('settings.bank_name') }}</p>
                        <p class="text-sm text-yellow-700"><strong>{{ __('seminar.account_name') }}:</strong> {{ config('settings.bank_account_name') }}</p>
                        <p class="text-sm text-yellow-700"><strong>{{ __('seminar.account_number') }}:</strong> {{ config('settings.bank_account_number') }}</p>
                        @else
                        <p class="text-sm text-yellow-700"><strong>{{ __('seminar.bank') }}:</strong> {{ config('settings.bank_name') }}</p>
                        <p class="text-sm text-yellow-700"><strong>{{ __('seminar.account_name') }}:</strong> {{ config('settings.bank_account_name') }}</p>
                        <p class="text-sm text-yellow-700"><strong>{{ __('seminar.account_number') }}:</strong> {{ config('settings.bank_account_number') }}</p>
                        <p class="text-sm text-yellow-700"><strong>{{ __('seminar.swift_code') }}:</strong> {{ config('settings.bank_swift_code') }}</p>
                        @endif
                        <p class="text-sm text-yellow-700 mt-2">{{ __('seminar.transfer_instructions') }}</p>
                        <div class="mt-4 pt-4 border-t border-yellow-300">
                            <p class="text-sm text-yellow-800 font-medium">{{ __('seminar.disclaimer_1') }}</p>
                            <p class="text-sm text-yellow-800 font-medium mt-2">{{ __('seminar.disclaimer_2') }}</p>
                        </div>
                    </div>
                </div>
                
                {{-- QRIS Display (conditional) --}}
                <div x-show="paymentMethod === 'qris'" x-transition class="mb-4">
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-6 text-center">
                        <h3 class="font-semibold text-blue-800 mb-4">Scan QRIS untuk Pembayaran</h3>
                        <img src="{{ asset('assets/images/QRIS_BNI_WKCI.webp') }}" alt="QRIS Code" class="w-full max-w-xs sm:max-w-sm md:max-w-md mx-auto rounded-lg shadow-md mb-4">
                        <a href="{{ asset('assets/images/QRIS_BNI_WKCI.webp') }}" download="QRIS_BNI_WKCI.webp" 
                            class="inline-flex items-center px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                            </svg>
                            Download QRIS Code
                        </a>
                    </div>
                </div>
                
                {{-- Total Amount Display --}}
                @if($wants_hands_on && $handsOnTotalPrice > 0 && $selected_seminar)
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-4">
                    <div class="flex justify-between items-center mb-2">
                        <span class="text-gray-700">{{ __('seminar.seminar_fee') }}</span>
                        <span class="font-medium">
                            @php
                                $package = \App\Models\Seminar::where('code', $selected_seminar)->first();
                                $seminarAmount = $package ? $package->amount : 0;
                                $isIdr = $package && $package->currency === 'IDR';
                            @endphp
                            {{ $isIdr ? 'Rp ' . number_format($seminarAmount, 0, ',', '.') : '$' . $seminarAmount }}
                        </span>
                    </div>
                    <div class="flex justify-between items-center mb-2">
                        <span class="text-gray-700">{{ __('seminar.hands_on_fee') }}</span>
                        <span class="font-medium">Rp {{ number_format($handsOnTotalPrice, 0, ',', '.') }}</span>
                    </div>
                    <div class="border-t border-blue-200 pt-2 mt-2">
                        <div class="flex justify-between items-center">
                            <span class="font-semibold text-gray-800">{{ __('seminar.total_amount') }}</span>
                            <span class="font-bold text-lg text-gray-800">
                                @if($isIdr)
                                    Rp {{ number_format($seminarAmount + $handsOnTotalPrice, 0, ',', '.') }}
                                @else
                                    ${{ $seminarAmount }} + Rp {{ number_format($handsOnTotalPrice, 0, ',', '.') }}
                                @endif
                            </span>
                        </div>
                    </div>
                </div>
                @endif
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('seminar.payment_proof') }} *</label>
                    <input type="file" wire:model="payment_proof" accept="image/jpeg,image/png,application/pdf" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <p class="text-xs text-gray-500 mt-1">{{ __('seminar.accepted_formats') }}</p>
                    @error('payment_proof') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            @if($isIndonesia)
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h2 class="text-xl font-semibold text-gray-800 mb-4">{{ __('seminar.scientific_poster_competition') }}</h2>
                
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-4">
                    <p class="text-sm text-blue-700">
                        {{ __('seminar.poster_description') }}
                        <a href="https://jakartadentalexhibitions.id/poster-competition/" target="_blank" class="underline font-medium">{{ __('seminar.learn_more') }}</a>
                    </p>
                </div>
                
                <div class="flex items-start">
                    <input type="checkbox" wire:model="wants_poster_competition" id="wants_poster_competition" class="w-5 h-5 text-blue-600 border-gray-300 rounded focus:ring-blue-500 mt-0.5">
                    <label for="wants_poster_competition" class="ml-2 text-sm text-gray-700">
                        {{ __('seminar.want_to_participate') }}
                    </label>
                </div>
            </div>
            @endif

            <button type="submit" wire:loading.attr="disabled" wire:target="submit" class="w-full bg-blue-600 text-white py-3 px-6 rounded-lg font-semibold hover:bg-blue-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                <span wire:loading.remove wire:target="submit">{{ __('seminar.submit_registration') }}</span>
                <span wire:loading wire:target="submit">{{ __('seminar.processing') }}...</span>
            </button>
            @endif
        </form>
</div>
