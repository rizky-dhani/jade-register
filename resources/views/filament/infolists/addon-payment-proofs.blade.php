@php
    $addonRegistrations = $getRecord()->addonRegistrations->filter(fn ($r) => $r->payment_proof_path !== null);
@endphp

@if($addonRegistrations->isNotEmpty())
    <div class="space-y-4">
        @foreach($addonRegistrations as $registration)
            <div>
                <p class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    {{ $registration->addon->name }}
                </p>
                <div class="flex justify-center">
                    @php
                        $path = $registration->payment_proof_path;
                        $url = asset('storage/' . $path);
                        $extension = pathinfo($path, PATHINFO_EXTENSION);
                    @endphp

                    @if(strtolower($extension) === 'pdf')
                        <iframe src="{{ $url }}" class="w-full rounded-lg shadow-lg" style="height: 40vh;" frameborder="0"></iframe>
                    @else
                        <img src="{{ $url }}" alt="{{ $registration->addon->name }} Payment Proof" class="max-w-full h-auto rounded-lg shadow-lg">
                    @endif
                </div>
            </div>
        @endforeach
    </div>
@endif
