<div class="max-w-2xl mx-auto p-6">
    <div class="text-center mb-8">
        <div class="flex justify-end mb-4">
            <div class="flex items-center gap-2">
                <button
                    wire:click="setLocale('en')"
                    class="text-sm font-medium {{ $locale === 'en' ? 'text-blue-600' : 'text-gray-500 hover:text-gray-700' }}"
                >
                    EN
                </button>
                <span class="text-gray-300">|</span>
                <button
                    wire:click="setLocale('id')"
                    class="text-sm font-medium {{ $locale === 'id' ? 'text-blue-600' : 'text-gray-500 hover:text-gray-700' }}"
                >
                    ID
                </button>
            </div>
        </div>
        <img src="{{ asset('assets/images/JADE_PDGI_Light.webp') }}" alt="Jakarta Dental Exhibition 2026" class="h-36 mx-auto mb-4">
        <h1 class="text-3xl font-bold text-gray-800">{{ __('seminar.digital_workshop_title') }}</h1>
        <p class="text-gray-600 mt-2">{{ __('seminar.digital_workshop_form_subtitle') }}</p>
    </div>

    @if (session('error'))
        <div class="bg-red-50 border border-red-200 text-red-700 rounded-lg p-4 mb-6">
            {{ session('error') }}
        </div>
    @endif

    <form wire:submit="submit" class="bg-white border border-gray-200 rounded-lg p-6 space-y-6">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
                {{ __('seminar.participant_type') }}
            </label>
            <div class="flex gap-4">
                <label class="inline-flex items-center gap-2">
                    <input type="radio" wire:model.live="is_local" value="1" class="text-blue-600">
                    <span class="text-gray-700">{{ __('seminar.local_participant') }}</span>
                </label>
                <label class="inline-flex items-center gap-2">
                    <input type="radio" wire:model.live="is_local" value="0" class="text-blue-600">
                    <span class="text-gray-700">{{ __('seminar.international_participant') }}</span>
                </label>
            </div>
        </div>

        @if ($is_local)
            <div>
                <label for="name_license" class="block text-sm font-medium text-gray-700 mb-1">
                    {{ __('seminar.name_plataran') }}
                </label>
                <input id="name_license" type="text" wire:model="name_license"
                       class="w-full border border-gray-300 rounded-md px-3 py-2">
                @error('name_license') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="nik" class="block text-sm font-medium text-gray-700 mb-1">
                        {{ __('seminar.nik') }}
                    </label>
                    <input id="nik" type="text" wire:model="nik"
                           class="w-full border border-gray-300 rounded-md px-3 py-2">
                    @error('nik') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="pdgi_branch" class="block text-sm font-medium text-gray-700 mb-1">
                        {{ __('seminar.pdgi_branch') }}
                    </label>
                    <input id="pdgi_branch" type="text" wire:model="pdgi_branch"
                           class="w-full border border-gray-300 rounded-md px-3 py-2">
                    @error('pdgi_branch') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <div>
                <label for="kompetensi" class="block text-sm font-medium text-gray-700 mb-1">
                    {{ __('seminar.competency') }}
                </label>
                <input id="kompetensi" type="text" wire:model="kompetensi"
                       class="w-full border border-gray-300 rounded-md px-3 py-2">
                @error('kompetensi') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
            </div>
        @else
            <div>
                <label for="name" class="block text-sm font-medium text-gray-700 mb-1">
                    {{ __('seminar.name') }}
                </label>
                <input id="name" type="text" wire:model="name"
                       class="w-full border border-gray-300 rounded-md px-3 py-2">
                @error('name') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="status" class="block text-sm font-medium text-gray-700 mb-1">
                    {{ __('seminar.status') }}
                </label>
                <select id="status" wire:model="status"
                        class="w-full border border-gray-300 rounded-md px-3 py-2">
                    <option value="">{{ __('seminar.select_competency') }}</option>
                    <option value="Dentist">{{ __('seminar.dentist') }}</option>
                    <option value="Student">{{ __('seminar.student') }}</option>
                </select>
                @error('status') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
            </div>
        @endif

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label for="email" class="block text-sm font-medium text-gray-700 mb-1">
                    {{ __('seminar.email') }}
                </label>
                <input id="email" type="email" wire:model.live.debounce.500ms="email"
                       class="w-full border border-gray-300 rounded-md px-3 py-2">
                @error('email') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">
                    {{ __('seminar.phone') }}
                </label>
                <input id="phone" type="text" wire:model="phone"
                       class="w-full border border-gray-300 rounded-md px-3 py-2">
                @error('phone') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
            </div>
        </div>

        <div>
            <label for="country_id" class="block text-sm font-medium text-gray-700 mb-1">
                {{ __('seminar.country') }}
            </label>
            <select id="country_id" wire:model.live="country_id"
                    class="w-full border border-gray-300 rounded-md px-3 py-2">
                @foreach ($countries as $country)
                    <option value="{{ $country->id }}">{{ $country->name }}</option>
                @endforeach
            </select>
            @error('country_id') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="payment_method" class="block text-sm font-medium text-gray-700 mb-1">
                {{ __('seminar.payment_method') }}
            </label>
            <select id="payment_method" wire:model="payment_method"
                    class="w-full border border-gray-300 rounded-md px-3 py-2">
                <option value="bank_transfer">{{ __('seminar.bank_transfer') }}</option>
                <option value="qris">QRIS</option>
            </select>
            @error('payment_method') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="payment_proof" class="block text-sm font-medium text-gray-700 mb-1">
                {{ __('seminar.payment_proof') }}
            </label>
            <input id="payment_proof" type="file" wire:model="payment_proof"
                   accept="image/jpeg,image/png,application/pdf"
                   class="w-full border border-gray-300 rounded-md px-3 py-2">
            <p class="text-gray-500 text-sm mt-1">{{ __('seminar.payment_proof_hint') }}</p>
            <div wire:loading wire:target="payment_proof" class="text-gray-500 text-sm mt-1">
                {{ __('seminar.uploading') }}
            </div>
            @error('payment_proof') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
        </div>

        <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
            <div class="flex items-center justify-between">
                <span class="text-gray-700 font-medium">{{ __('seminar.total_amount') }}</span>
                <span class="text-xl font-bold text-gray-900">
                    IDR {{ number_format($priceBreakdown['amount'], 0, ',', '.') }}
                </span>
            </div>
            @if ($priceBreakdown['registration_type'] === 'bundled')
                <p class="text-green-700 text-sm mt-2">{{ __('seminar.digital_workshop_bundle_applied') }}</p>
            @endif
        </div>

        <button type="submit" @disabled($isSubmitting)
                class="w-full bg-blue-600 hover:bg-blue-700 disabled:opacity-50 text-white font-semibold rounded-md px-4 py-3">
            <span wire:loading.remove wire:target="submit">{{ __('seminar.submit_registration') }}</span>
            <span wire:loading wire:target="submit">{{ __('seminar.submitting') }}</span>
        </button>
    </form>
</div>
