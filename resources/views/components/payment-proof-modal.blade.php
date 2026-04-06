<div class="w-full">
    @if($extension === 'pdf')
        <iframe
            src="{{ $url }}"
            class="w-full rounded-lg shadow-lg"
            style="height: 80vh;"
            frameborder="0">
        </iframe>
    @else
        <img
            src="{{ $url }}"
            alt="Payment Proof"
            class="w-full h-auto rounded-lg shadow-lg">
    @endif
</div>
