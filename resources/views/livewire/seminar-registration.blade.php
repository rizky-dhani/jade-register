<div class="max-w-2xl mx-auto p-6" x-data="{ locale: @entangle('locale') }" x-init="
    const savedLocale = localStorage.getItem('jade_locale');
    if (savedLocale && ['en', 'id'].includes(savedLocale)) {
        locale = savedLocale;
    }
    $watch('locale', value => {
        localStorage.setItem('jade_locale', value);
    });
">
    <div class="text-center mb-8">
        <img src="{{ asset('assets/images/Jade_Logo.webp') }}" alt="Jakarta Dental Exhibition 2026" class="h-24 mx-auto mb-4">
        <h1 class="text-3xl font-bold text-gray-800">{{ __('seminar.page_title') }}</h1>
        <p class="text-gray-600 mt-2">{{ __('seminar.page_subtitle') }}</p>
    </div>

    @if($isSuccess)
        <div class="bg-green-50 border border-green-200 rounded-lg p-6 text-center">
            <svg class="w-16 h-16 text-green-500 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
            </svg>
            <h2 class="text-2xl font-bold text-green-800 mb-2">{{ __('seminar.success_title') }}</h2>
            <p class="text-green-700 mb-4">{{ __('seminar.success_thank_you') }}{{ $registration->name }}!</p>
            <p class="text-gray-600">{{ __('seminar.registration_code') }}<strong>{{ $registration->registration_code }}</strong></p>
            <p class="text-gray-600">{{ __('seminar.confirmation_email_sent') }}{{ $registration->email }}</p>
            <div class="mt-6 pt-6 border-t border-green-200 text-center text-left">
                <p class="text-green-800 font-medium mb-3">{{ __('seminar.success_message_1') }}</p>
                <p class="text-green-700 mb-3">{{ __('seminar.success_message_2') }}</p>
                <p class="text-green-700">{{ __('seminar.success_message_3') }}<a href="https://chat.whatsapp.com/KtELLi4Q22VHqJWFavOwhQ?mode=hq1tcla" target="_blank" class="underline font-semibold hover:text-green-900">https://chat.whatsapp.com/KtELLi4Q22VHqJWFavOwhQ</a></p>
            </div>
        </div>
    @else
        <form wire:submit.prevent="submit" class="space-y-6">
            {{-- Language Selector --}}
            <div class="flex justify-end">
                <div class="inline-flex rounded-lg border border-gray-300 bg-white">
                    <button
                        type="button"
                        wire:click="setLocale('en')"
                        class="px-4 py-2 text-sm font-medium rounded-l-lg transition-colors {{ $locale === 'en' ? 'bg-blue-600 text-white' : 'text-gray-700 hover:bg-gray-100' }}"
                    >
                        {{ __('seminar.english') }}
                    </button>
                    <button
                        type="button"
                        wire:click="setLocale('id')"
                        class="px-4 py-2 text-sm font-medium rounded-r-lg transition-colors {{ $locale === 'id' ? 'bg-blue-600 text-white' : 'text-gray-700 hover:bg-gray-100' }}"
                    >
                        {{ __('seminar.bahasa') }}
                    </button>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h2 class="text-xl font-semibold text-gray-800 mb-4">{{ __('seminar.personal_information') }}</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('seminar.country') }} *</label>
                        <select wire:model.live="country_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="">{{ __('seminar.select_country') }}</option>
                            @foreach($countries as $country)
                                <option value="{{ (int) $country->id }}">{{ $country->name }}</option>
                            @endforeach
                        </select>
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
                        <input type="text" wire:model="name" wire:key="name-str" autocomplete="off" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        @error('name') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('seminar.name_plataran') }} *</label>
                        <input type="text" wire:model="name_license" wire:key="name-plataran" autocomplete="new-name-plataran" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        @error('name_license') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('seminar.email_plataran') }} *</label>
                        <input type="email" wire:model="email" wire:key="email-plataran" autocomplete="new-email-plataran" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        @error('email') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('seminar.whatsapp_number') }} *</label>
                        <input type="text" wire:model="phone" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        @error('phone') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('seminar.nik') }} *</label>
                        <input type="text" wire:model="nik" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        @error('nik') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('seminar.pdgi_branch') }} *</label>
                        <input type="text" wire:model="pdgi_branch" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        @error('pdgi_branch') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                    </div>
                    
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('seminar.competency') }} *</label>
                        <select wire:model="kompetensi" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="">{{ __('seminar.select_competency') }}</option>
                            <option value="GP">{{ __('seminar.competency_gp') }}</option>
                            <option value="Sp.KG">{{ __('seminar.competency_sp_kg') }}</option>
                            <option value="Sp.KGA">{{ __('seminar.competency_sp_kga') }}</option>
                            <option value="Sp.Pros">{{ __('seminar.competency_sp_pros') }}</option>
                            <option value="Sp.B.M.M">{{ __('seminar.competency_sp_bmm') }}</option>
                            <option value="Sp.Perio">{{ __('seminar.competency_sp_perio') }}</option>
                            <option value="Sp.Ort">{{ __('seminar.competency_sp_ort') }}</option>
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
                        <input type="text" wire:model="name" wire:key="name-intl" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        @error('name') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('seminar.email') }} *</label>
                        <input type="email" wire:model="email" wire:key="email-intl" autocomplete="email" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        @error('email') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('seminar.whatsapp_number') }} *</label>
                        <input type="text" wire:model="phone" wire:key="whatsapp-intl" autocomplete="tel" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="+1234567890">
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
                    <label class="block text-sm font-medium text-gray-700 mb-3">{{ __('seminar.pricing_tier') }} *</label>
                    
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
                                    wire:click="$set('pricing_tier', '{{ $tier['value'] }}')"
                                    class="p-4 rounded-lg border-2 text-left transition-all flex justify-between items-center {{ $pricing_tier === $tier['value'] ? 'border-blue-500 bg-blue-50' : 'border-gray-200 hover:border-gray-300 hover:bg-gray-50' }}"
                                >
                                    <div class="font-medium text-gray-800">{{ $tier['label'] }}</div>
                                    <div class="font-semibold text-gray-700">{{ $tier['price'] }}</div>
                                </button>
                            @endforeach
                        </div>
                    @endif
                    @error('pricing_tier') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                </div>
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
                
                <div class="flex items-start mb-4">
                    <input type="checkbox" wire:model="wants_poster_competition" id="wants_poster_competition" class="w-5 h-5 text-blue-600 border-gray-300 rounded focus:ring-blue-500 mt-0.5">
                    <label for="wants_poster_competition" class="ml-2 text-sm text-gray-700">
                        {{ __('seminar.want_to_participate') }}
                    </label>
                </div>
                
                @if($wants_poster_competition)
                    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                        <p class="text-sm text-yellow-700 mb-4">
                            {{ __('seminar.password_info') }}
                        </p>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('seminar.password') }} *</label>
                            <input type="password" wire:model="password" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            @error('password') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                        </div>
                        
                        <div class="mt-4">
                            <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('seminar.confirm_password') }} *</label>
                            <input type="password" wire:model="password_confirmation" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            @error('password_confirmation') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                @endif
            </div>
            @endif

            <button type="submit" class="w-full bg-blue-600 text-white py-3 px-6 rounded-lg font-semibold hover:bg-blue-700 transition-colors">
                {{ __('seminar.submit_registration') }}
            </button>
            @endif
        </form>
    @endif
</div>
