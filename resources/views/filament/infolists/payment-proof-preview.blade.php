<div class="flex justify-center">
    @php
        $path = $getRecord()->payment_proof_path;
        $url = asset('storage/' . $path);
        $extension = pathinfo($path, PATHINFO_EXTENSION);
    @endphp

    @if(strtolower($extension) === 'pdf')
        <iframe src="{{ $url }}" class="w-full rounded-lg shadow-lg" style="height: 60vh;" frameborder="0"></iframe>
    @else
        <img src="{{ $url }}" alt="Payment Proof" class="max-w-full h-auto rounded-lg shadow-lg">
    @endif
</div>
