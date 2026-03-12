<div class="max-w-3xl mx-auto p-6">
    <div class="text-center mb-8">
        <div class="flex items-center justify-center gap-6 mb-4">
            <img src="{{ asset('assets/images/Jade_Logo.webp') }}" alt="Jakarta Dental Exhibition 2026" class="h-24">
            <img src="{{ asset('assets/images/PDGI_PENGWIL_JKT.webp') }}" alt="PDGI Pengwil DKI Jakarta" class="h-24">
        </div>
        <h1 class="text-3xl font-bold text-gray-800">Scientific Poster Submission</h1>
        <p class="text-gray-600 mt-2">Submit your research or case report for JADE 2026</p>
    </div>

    @auth
        @if($canSubmit)
            @if($isSuccess)
                <div class="bg-green-50 border border-green-200 rounded-lg p-6 text-center">
                    <svg class="w-16 h-16 text-green-500 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    <h2 class="text-2xl font-bold text-green-800 mb-2">Poster Submitted Successfully!</h2>
                    <p class="text-green-700 mb-4">Thank you for your submission, {{ auth()->user()->name }}!</p>
                    <p class="text-gray-600">Your poster: <strong>{{ $submission->title }}</strong></p>
                    <p class="text-gray-600 mt-2">Status: <span class="font-semibold capitalize">{{ $submission->status }}</span></p>
                </div>
            @else
                <form wire:submit.prevent="submit" class="space-y-6">
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                        <h2 class="text-xl font-semibold text-gray-800 mb-4">Poster Details</h2>
                        
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Poster Title *</label>
                                <input type="text" wire:model="title" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                @error('title') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                            </div>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Category *</label>
                                    <select wire:model="poster_category_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                        <option value="">Select Category</option>
                                        @foreach($categories as $category)
                                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('poster_category_id') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Topic *</label>
                                    <select wire:model="poster_topic_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                        <option value="">Select Topic</option>
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
                        <h2 class="text-xl font-semibold text-gray-800 mb-4">Abstract</h2>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Abstract Text (max 300 words) *</label>
                            <textarea wire:model="abstract_text" rows="6" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Enter your abstract here..."></textarea>
                            <p class="text-xs text-gray-500 mt-1">{{ strlen($abstract_text) }}/1500 characters</p>
                            @error('abstract_text') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                        <h2 class="text-xl font-semibold text-gray-800 mb-4">Authors & Affiliation</h2>
                        
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Author Names *</label>
                                <input type="text" wire:model="author_names" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="e.g., John Doe, Jane Smith">
                                <p class="text-xs text-gray-500 mt-1">Separate multiple authors with commas</p>
                                @error('author_names') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Author Emails *</label>
                                <input type="text" wire:model="author_emails" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="e.g., john@example.com, jane@example.com">
                                @error('author_emails') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Affiliation *</label>
                                <input type="text" wire:model="affiliation" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="e.g., Faculty of Dentistry, University of Indonesia">
                                @error('affiliation') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Presenter Name *</label>
                                <input type="text" wire:model="presenter_name" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                @error('presenter_name') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                        <h2 class="text-xl font-semibold text-gray-800 mb-4">Poster File (Optional)</h2>
                        
                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-4">
                            <p class="text-sm text-blue-700">
                                You can submit your poster now or save as draft and upload the file later. 
                                The final poster deadline is <strong>February 15, 2026</strong>.
                            </p>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Upload Poster (PDF only, A1 vertical)</label>
                            <input type="file" wire:model="poster_file" accept="application/pdf" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <p class="text-xs text-gray-500 mt-1">Accepted format: PDF. Max size: 10MB</p>
                            @error('poster_file') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <button type="submit" class="w-full bg-blue-600 text-white py-3 px-6 rounded-lg font-semibold hover:bg-blue-700 transition-colors">
                        Submit Poster
                    </button>
                </form>
            @endif
        @else
            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-6 text-center">
                <svg class="w-16 h-16 text-yellow-500 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                </svg>
                <h2 class="text-xl font-bold text-yellow-800 mb-2">Access Restricted</h2>
                <p class="text-yellow-700 mb-4">
                    You need to register for JADE 2026 Seminar and opt-in for the Poster Competition to submit a poster.
                </p>
                <a href="{{ route('register.seminar') }}" class="inline-block bg-blue-600 text-white py-2 px-6 rounded-lg font-semibold hover:bg-blue-700 transition-colors">
                    Register for Seminar
                </a>
            </div>
        @endauth
    @else
        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-6 text-center">
            <svg class="w-16 h-16 text-yellow-500 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
            </svg>
            <h2 class="text-xl font-bold text-yellow-800 mb-2">Login Required</h2>
            <p class="text-yellow-700 mb-4">
                You need to be logged in to submit a poster. Please login or register for the seminar first.
            </p>
            <div class="flex justify-center gap-4">
                <a href="{{ route('login') }}" class="bg-blue-600 text-white py-2 px-6 rounded-lg font-semibold hover:bg-blue-700 transition-colors">
                    Login
                </a>
                <a href="{{ route('register.seminar') }}" class="bg-gray-200 text-gray-800 py-2 px-6 rounded-lg font-semibold hover:bg-gray-300 transition-colors">
                    Register for Seminar
                </a>
            </div>
        </div>
    @endauth
</div>
