<div class="max-w-2xl mx-auto p-6">
    <div class="text-center mb-8">
        <img src="{{ asset('assets/images/Jade_Logo.webp') }}" alt="Jakarta Dental Exhibition 2026" class="h-24 mx-auto mb-4">
        <h1 class="text-3xl font-bold text-gray-800">Visitor Registration</h1>
        <p class="text-gray-600 mt-2">Register for free entrance to Jakarta Dental Exhibition 2026</p>
    </div>

    @if($isSuccess)
        <div class="bg-green-50 border border-green-200 rounded-lg p-6 text-center">
            <svg class="w-16 h-16 text-green-500 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
            </svg>
            <h2 class="text-2xl font-bold text-green-800 mb-2">Registration Successful!</h2>
            <p class="text-green-700 mb-4">Thank you for registering, {{ $visitor->name }}!</p>
            <p class="text-gray-600">We have sent a confirmation email to {{ $visitor->email }}</p>
            <p class="text-gray-600 mt-2">Your preferred visit date: <strong>{{ \Carbon\Carbon::parse($visitor->preferred_visit_date)->format('d F Y') }}</strong></p>
        </div>
    @else
        <form wire:submit.prevent="submit" class="space-y-6">
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h2 class="text-xl font-semibold text-gray-800 mb-4">Personal Information</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Name *</label>
                        <input type="text" wire:model="name" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        @error('name') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Email *</label>
                        <input type="email" wire:model="email" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        @error('email') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Phone *</label>
                        <input type="text" wire:model="phone" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        @error('phone') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Affiliation/Institution</label>
                        <input type="text" wire:model="affiliation" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        @error('affiliation') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h2 class="text-xl font-semibold text-gray-800 mb-4">Additional Information</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Profession *</label>
                        <select wire:model="profession" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="">Select Profession</option>
                            @foreach($professions as $profession)
                                <option value="{{ $profession->name }}">{{ $profession->name }}</option>
                            @endforeach
                        </select>
                        @error('profession') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Preferred Visit Date *</label>
                        <select wire:model="preferred_visit_date" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="2026-11-13">13 November 2026 (Friday)</option>
                            <option value="2026-11-14">14 November 2026 (Saturday)</option>
                            <option value="2026-11-15">15 November 2026 (Sunday)</option>
                        </select>
                        @error('preferred_visit_date') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                    </div>
                    
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">How did you hear about this event?</label>
                        <select wire:model="marketing_source" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="">Select Source</option>
                            @foreach($marketingSources as $source)
                                <option value="{{ $source->name }}">{{ $source->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <button type="submit" class="w-full bg-blue-600 text-white py-3 px-6 rounded-lg font-semibold hover:bg-blue-700 transition-colors">
                Register Now
            </button>
        </form>
    @endif
</div>
