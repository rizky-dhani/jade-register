<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <title>{{ $title ?? config('app.name') }}</title>

        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <link rel="stylesheet" href="{{ asset('assets/vendor/glightbox/glightbox.min.css') }}" />
    </head>
    <body>
        {{ $slot }}

        <script src="{{ asset('assets/vendor/glightbox/glightbox.min.js') }}"></script>
        <script>
            function initGLightbox() {
                if (typeof GLightbox === 'undefined') return;
                if (window.__gl) window.__gl.destroy();
                window.__gl = GLightbox({
                    selector: '.glightbox',
                    touchNavigation: true,
                    loop: false,
                    zoomable: true,
                    draggable: true,
                });
            }

            initGLightbox();
            document.addEventListener('livewire:navigated', initGLightbox);

            document.addEventListener('livewire:initialized', () => {
                if (window.Livewire && Livewire.hook) {
                    Livewire.hook('morph.added', ({ el }) => {
                        if (el.matches?.('.glightbox') || el.querySelector?.('.glightbox')) {
                            initGLightbox();
                        }
                    });
                }
            });
        </script>
    </body>
</html>
