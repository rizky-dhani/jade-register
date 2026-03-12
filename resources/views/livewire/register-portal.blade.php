<div class="max-w-2xl mx-auto p-6">
    <div class="text-center mb-8">
        <div class="flex items-center justify-center gap-6 mb-4">
            <img src="{{ asset('assets/images/Jade_Logo.webp') }}" alt="Jakarta Dental Exhibition 2026" class="h-24">
            <img src="{{ asset('assets/images/PDGI_PENGWIL_JKT.webp') }}" alt="PDGI Pengwil DKI Jakarta" class="h-24">
        </div>
        <h1 class="text-3xl font-bold text-gray-800">Registration Portal</h1>
        <p class="text-gray-600 mt-2">Choose your registration type for Jakarta Dental Exhibition 2026</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <a href="/register/visitor" wire:navigate class="block h-full">
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 hover:shadow-md hover:border-blue-300 transition-all cursor-pointer text-center h-full flex flex-col">
                <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                    </svg>
                </div>
                <h2 class="text-xl font-semibold text-gray-800 mb-2">Visitor Registration</h2>
                <p class="text-gray-600 mb-4">Register as a visitor for free entrance</p>
                <span class="inline-flex items-center justify-center text-blue-600 font-medium">
                    Register Now
                    <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </span>
            </div>
        </a>

        <a href="/register/seminar" wire:navigate class="block h-full">
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 hover:shadow-md hover:border-green-300 transition-all cursor-pointer text-center h-full flex flex-col">
                <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"></path>
                    </svg>
                </div>
                <h2 class="text-xl font-semibold text-gray-800 mb-2">Seminar Registration</h2>
                <p class="text-gray-600 mb-4">Register for seminar and hands on sessions</p>
                <span class="inline-flex items-center justify-center text-green-600 font-medium">
                    Register Now
                    <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </span>
            </div>
        </a>
    </div>
</div>
