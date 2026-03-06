<div class="max-w-2xl mx-auto p-6">
    <div class="text-center mb-8">
        <img src="{{ asset('assets/images/Jade_Logo.webp') }}" alt="Jakarta Dental Exhibition 2026" class="h-24 mx-auto mb-4">
        <h1 class="text-3xl font-bold text-gray-800">Seminar Registration</h1>
        <p class="text-gray-600 mt-2">Register for the Jakarta Dental Exhibition 2026 Seminar</p>
    </div>

    @if($isSuccess)
        <div class="bg-green-50 border border-green-200 rounded-lg p-6 text-center">
            <svg class="w-16 h-16 text-green-500 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
            </svg>
            <h2 class="text-2xl font-bold text-green-800 mb-2">Registration Submitted!</h2>
            <p class="text-green-700 mb-4">Thank you for registering, {{ $registration->name }}!</p>
            <p class="text-gray-600">Your Registration Code: <strong>{{ $registration->registration_code }}</strong></p>
            <p class="text-gray-600">We have sent a confirmation email to {{ $registration->email }}</p>
        </div>
    @else
        <form wire:submit.prevent="submit" class="space-y-6">
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h2 class="text-xl font-semibold text-gray-800 mb-4">Personal Information</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nama Sesuai STR (tanpa gelar) *</label>
                        <input type="text" wire:model="name" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        @error('name') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nama Sesuai KTP *</label>
                        <input type="text" wire:model="name_license" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        @error('name_license') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
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
                        <label class="block text-sm font-medium text-gray-700 mb-1">NIK *</label>
                        <input type="text" wire:model="nik" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        @error('nik') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nomor NPA (6 digit terakhir) *</label>
                        <input type="text" wire:model="npa" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        @error('npa') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                    </div>
                    
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">PDGI Cabang *</label>
                        <input type="text" wire:model="pdgi_branch" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        @error('pdgi_branch') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h2 class="text-xl font-semibold text-gray-800 mb-4">Registration Package</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Country *</label>
                        <select wire:model.live="country_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="">Select Country</option>
                            @foreach($countries as $country)
                                <option value="{{ (int) $country->id }}">{{ $country->name }}</option>
                            @endforeach
                        </select>
                        @error('country_id') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                    </div>
                    
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-3">Pricing Tier *</label>
                        
                        @php 
                        $tiers = $availableTiers ?? [];
                        @endphp
                        
                        @if(count($tiers) === 0)
                            <p class="text-gray-500 text-sm">Please select a country first to see available pricing tiers.</p>
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
            </div>

            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h2 class="text-xl font-semibold text-gray-800 mb-4">Payment Information</h2>
                
                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-4">
                    <h3 class="font-semibold text-yellow-800 mb-2">Bank Transfer Details</h3>
                    <p class="text-sm text-yellow-700"><strong>Bank:</strong> Bank Central Asia (BCA)</p>
                    <p class="text-sm text-yellow-700"><strong>Account Name:</strong> PT Jakarta Dental Exhibition</p>
                    <p class="text-sm text-yellow-700"><strong>Account Number:</strong> 1234567890</p>
                    <p class="text-sm text-yellow-700 mt-2">Please transfer the exact amount and upload your payment proof.</p>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Payment Proof *</label>
                    <input type="file" wire:model="payment_proof" accept="image/jpeg,image/png,application/pdf" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <p class="text-xs text-gray-500 mt-1">Accepted formats: JPG, PNG, PDF. Max size: 5MB</p>
                    @error('payment_proof') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <button type="submit" class="w-full bg-blue-600 text-white py-3 px-6 rounded-lg font-semibold hover:bg-blue-700 transition-colors">
                Submit Registration
            </button>
        </form>
    @endif
</div>
