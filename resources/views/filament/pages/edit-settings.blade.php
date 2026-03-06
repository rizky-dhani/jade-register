@php
    /** @var \App\Filament\Resources\SettingResource\Pages\EditSetting $livewire */
@endphp

<x-filament-panels::page>
    {{ $this->form }}

    {{ $this->getFormActions() }}
</x-filament-panels::page>