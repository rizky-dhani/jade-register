<x-filament-widgets::widget>
    <x-filament::section :heading="$this->getHeading()">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <x-filament::button
                :href="route('register.seminar')"
                tag="a"
                color="primary"
                icon="heroicon-o-academic-cap"
            >
                Seminar & Hands On Registration
            </x-filament::button>

            @if ($this->hasVerifiedSeminarRegistration())
                <x-filament::button
                    :href="route('poster.submit')"
                    tag="a"
                    color="success"
                    icon="heroicon-o-photo"
                >
                    Poster Registration
                </x-filament::button>
            @else
                <div
                    x-data="{ tooltip: false }"
                    x-on:mouseenter="tooltip = true"
                    x-on:mouseleave="tooltip = false"
                    class="relative"
                >
                    <x-filament::button
                        disabled
                        color="gray"
                        icon="heroicon-o-photo"
                        class="w-full"
                    >
                        Poster Registration
                    </x-filament::button>

                    <div
                        x-show="tooltip"
                        x-transition:enter="transition ease-out duration-200"
                        x-transition:enter-start="opacity-0 translate-y-1"
                        x-transition:enter-end="opacity-100 translate-y-0"
                        x-transition:leave="transition ease-in duration-150"
                        x-transition:leave-start="opacity-100 translate-y-0"
                        x-transition:leave-end="opacity-0 translate-y-1"
                        class="absolute z-50 w-64 px-3 py-2 mb-2 text-sm text-white transform -translate-x-1/2 bg-gray-900 rounded-lg shadow-lg bottom-full left-1/2 dark:bg-gray-800"
                    >
                        <div class="relative">
                            <p>You must register for the Seminar with verified payment before you can register for the Poster Competition.</p>
                            <div class="absolute w-2 h-2 transform rotate-45 -translate-x-1/2 bg-gray-900 left-1/2 -bottom-1 dark:bg-gray-800"></div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
