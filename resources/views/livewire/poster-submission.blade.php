<div class="max-w-3xl mx-auto p-6" x-data="{ locale: @entangle('locale') }" x-init="
    const savedLocale = localStorage.getItem('jade_locale');
    if (savedLocale && ['en', 'id'].includes(savedLocale)) {
        locale = savedLocale;
    }
    $watch('locale', value => {
        localStorage.setItem('jade_locale', value);
    });
">
    <div class="text-center mb-8">
        <div class="flex items-center justify-center gap-6 mb-4">
            <img src="{{ asset('assets/images/Jade_Logo.webp') }}" alt="Jakarta Dental Exhibition 2026" class="h-24">
            <img src="{{ asset('assets/images/PDGI_PENGWIL_JKT.webp') }}" alt="PDGI Pengwil DKI Jakarta" class="h-24">
        </div>
        <h1 class="text-3xl font-bold text-gray-800">{{ __('seminar.scientific_poster_submission_title') }}</h1>
        <p class="text-gray-600 mt-2">{{ __('seminar.poster_submission_subtitle') }}</p>
    </div>

    @auth
        @if($canSubmit || $this->isSuperAdmin())
            @if($isSuccess)
                <div class="bg-green-50 border border-green-200 rounded-lg p-6 text-center">
                    <svg class="w-16 h-16 text-green-500 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    <h2 class="text-2xl font-bold text-green-800 mb-2">{{ __('seminar.poster_submitted_success_title') }}</h2>
                    <p class="text-green-700 mb-4">{{ __('seminar.poster_submitted_thank_you') }}, {{ auth()->user()->name }}!</p>
                    <p class="text-gray-600">{{ __('seminar.poster_submitted_your_poster') }} <strong>{{ $submission->title }}</strong></p>
                    <p class="text-gray-600 mt-2">{{ __('seminar.poster_submitted_status') }} <span class="font-semibold capitalize">{{ $submission->status }}</span></p>
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
                        <h2 class="text-xl font-semibold text-gray-800 mb-4">{{ __('seminar.poster_details_section') }}</h2>
                        
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('seminar.poster_title_label') }} *</label>
                                <input type="text" wire:model="title" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                @error('title') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                            </div>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('seminar.poster_category_label') }} *</label>
                                    <select wire:model="poster_category_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                        <option value="">{{ __('seminar.poster_select_category') }}</option>
                                        @foreach($categories as $category)
                                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('poster_category_id') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('seminar.poster_topic_label') }} *</label>
                                    <select wire:model="poster_topic_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                        <option value="">{{ __('seminar.poster_select_topic') }}</option>
                                        @foreach($topics as $topic)
                                            <option value="{{ $topic->id }}">{{ $topic->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('poster_topic_id') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                        <h2 class="text-xl font-semibold text-gray-800 mb-4">{{ __('seminar.poster_abstract_section') }}</h2>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('seminar.poster_abstract_text_label') }} *</label>
                            <textarea wire:model="abstract_text" rows="6" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="{{ __('seminar.poster_abstract_placeholder') }}"></textarea>
                            <p class="text-xs text-gray-500 mt-1">{{ strlen($abstract_text) }}/1500 {{ __('seminar.poster_characters') }}</p>
                            @error('abstract_text') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                        <h2 class="text-xl font-semibold text-gray-800 mb-4">{{ __('seminar.poster_authors_affiliation_section') }}</h2>
                        
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('seminar.poster_author_names_label') }} *</label>
                                <input type="text" wire:model="author_names" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="{{ __('seminar.poster_author_names_placeholder') }}">
                                <p class="text-xs text-gray-500 mt-1">{{ __('seminar.poster_author_names_hint') }}</p>
                                @error('author_names') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('seminar.poster_author_emails_label') }} *</label>
                                <input type="text" wire:model="author_emails" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="{{ __('seminar.poster_author_emails_placeholder') }}">
                                @error('author_emails') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('seminar.poster_affiliation_label') }} *</label>
                                <input type="text" wire:model="affiliation" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="{{ __('seminar.poster_affiliation_placeholder') }}">
                                @error('affiliation') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('seminar.poster_presenter_name_label') }} *</label>
                                <input type="text" wire:model="presenter_name" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                @error('presenter_name') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                        <h2 class="text-xl font-semibold text-gray-800 mb-4">{{ __('seminar.poster_file_optional_section') }}</h2>
                        
                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-4">
                            <p class="text-sm text-blue-700">
                                {{ __('seminar.poster_file_info_text') }} <strong>February 15, 2026</strong>.
                            </p>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('seminar.poster_upload_label') }}</label>
                            <input type="file" wire:model="poster_file" accept="application/pdf" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <p class="text-xs text-gray-500 mt-1">{{ __('seminar.poster_file_accepted_format') }}</p>
                            @error('poster_file') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <button type="submit" class="w-full bg-blue-600 text-white py-3 px-6 rounded-lg font-semibold hover:bg-blue-700 transition-colors">
                        {{ __('seminar.poster_submit_button') }}
                    </button>
                </form>
            @endif
        @else
            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-6 text-center">
                <svg class="w-16 h-16 text-yellow-500 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                </svg>
                <h2 class="text-xl font-bold text-yellow-800 mb-2">{{ __('seminar.poster_access_restricted_title') }}</h2>
                <p class="text-yellow-700 mb-4">
                    {{ __('seminar.poster_access_restricted_message') }}
                </p>
                <a href="{{ route('register.seminar') }}" class="inline-block bg-blue-600 text-white py-2 px-6 rounded-lg font-semibold hover:bg-blue-700 transition-colors">
                    {{ __('seminar.poster_register_seminar_button') }}
                </a>
            </div>
        @endauth
    @else
        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-6 text-center">
            <svg class="w-16 h-16 text-yellow-500 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
            </svg>
            <h2 class="text-xl font-bold text-yellow-800 mb-2">{{ __('seminar.poster_login_required_title') }}</h2>
            <p class="text-yellow-700 mb-4">
                {{ __('seminar.poster_login_required_message') }}
            </p>
            <div class="flex justify-center gap-4">
                <a href="{{ route('login') }}" class="bg-blue-600 text-white py-2 px-6 rounded-lg font-semibold hover:bg-blue-700 transition-colors">
                    {{ __('seminar.poster_login_button') }}
                </a>
                <a href="{{ route('register.seminar') }}" class="bg-gray-200 text-gray-800 py-2 px-6 rounded-lg font-semibold hover:bg-gray-300 transition-colors">
                    {{ __('seminar.poster_register_seminar_button') }}
                </a>
            </div>
        </div>
    @endauth
</div>
