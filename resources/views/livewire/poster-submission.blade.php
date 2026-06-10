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
        <div class="flex items-center justify-center gap-6 mb-4">
            <img src="{{ asset('assets/images/Jade_Logo.webp') }}" alt="Jakarta Dental Exhibition 2026" class="h-24">
            <img src="{{ asset('assets/images/PDGI_PENGWIL_JKT.webp') }}" alt="PDGI Pengwil DKI Jakarta" class="h-24">
        </div>
        <h1 class="text-3xl font-bold text-gray-800">{{ __('seminar.scientific_poster_submission_title') }}</h1>
        <p class="text-gray-600 mt-2">{{ __('seminar.poster_submission_subtitle') }}</p>
    </div>

    {{-- STEP 1: Verify by Email or NIK --}}
    @if ($step === 'verify')
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-8 max-w-lg mx-auto">
            <h2 class="text-xl font-semibold text-gray-800 mb-2">{{ __('seminar.scientific_poster_competition') }}</h2>
            <p class="text-gray-600 text-sm mb-6">{{ __('seminar.poster_description') }}</p>

            <div class="mb-4">
                <label for="verificationInput" class="block text-sm font-medium text-gray-700 mb-1">
                    {{ __('seminar.email_or_nik') }}
                </label>
                <input
                    id="verificationInput"
                    type="text"
                    wire:model="verificationInput"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    placeholder="{{ __('seminar.email_or_nik') }}"
                >
            </div>

            @if ($verificationError)
                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-4">
                    <p class="text-yellow-800 text-sm">{{ $verificationError }}</p>
                </div>
            @endif

            <button
                type="button"
                wire:click="verifyRegistration"
                class="w-full bg-blue-600 text-white py-3 px-6 rounded-lg font-semibold hover:bg-blue-700 transition-colors"
            >
                {{ __('seminar.verify_continue') }}
            </button>
        </div>

    {{-- STEP 2: Poster Submission Form --}}
    @elseif ($step === 'submit')
        <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-6">
            <p class="text-green-800 text-sm">
                {{ __('seminar.poster_verified_info', ['name' => $verifiedRegistration->name, 'email' => $verifiedRegistration->email]) }}
            </p>
        </div>

        {{-- Download Template Section --}}
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <h2 class="text-xl font-semibold text-gray-800 mb-4">{{ __('seminar.poster_template_section_title') }}</h2>
            <p class="text-gray-600 text-sm mb-4">{{ __('seminar.poster_template_description') }}</p>
            <div class="flex flex-wrap gap-4">
                <a href="{{ asset('assets/templates/TEMPLATE_CASE_REPORT_JADE_2026.pptx') }}"
                   target="_blank"
                   class="inline-flex items-center gap-2 bg-purple-600 text-white px-4 py-2 rounded-lg hover:bg-purple-700 transition-colors text-sm font-medium">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    {{ __('seminar.poster_template_case_report') }}
                </a>
                <a href="{{ asset('assets/templates/TEMPLATE_RESEARCH_JADE_2026.pptx') }}"
                   target="_blank"
                   class="inline-flex items-center gap-2 bg-purple-600 text-white px-4 py-2 rounded-lg hover:bg-purple-700 transition-colors text-sm font-medium">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    {{ __('seminar.poster_template_research') }}
                </a>
            </div>
        </div>

        <form wire:submit.prevent="submit" class="space-y-6">
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

    {{-- STEP 3: Success --}}
    @elseif ($step === 'success')
        <div class="bg-green-50 border border-green-200 rounded-lg p-6 text-center">
            <svg class="w-16 h-16 text-green-500 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
            </svg>
            <h2 class="text-2xl font-bold text-green-800 mb-2">{{ __('seminar.poster_submitted_success_title') }}</h2>
            <p class="text-green-700 mb-4">{{ __('seminar.poster_submitted_thank_you') }}, {{ $submission->presenter_name }}!</p>
            <p class="text-gray-600">{{ __('seminar.poster_submitted_your_poster') }} <strong>{{ $submission->title }}</strong></p>
            <p class="text-gray-600 mt-2">{{ __('seminar.poster_submitted_status') }} <span class="font-semibold capitalize">{{ $submission->status }}</span></p>
        </div>
    @endif
</div>
