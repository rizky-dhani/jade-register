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

            <x-filament::button
                :href="route('poster.submit')"
                tag="a"
                :disabled="! $this->hasVerifiedSeminarRegistration()"
                color="success"
                icon="heroicon-o-photo"
            >
                Poster Registration
            </x-filament::button>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
