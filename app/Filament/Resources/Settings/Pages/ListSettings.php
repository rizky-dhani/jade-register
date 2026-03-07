<?php

namespace App\Filament\Resources\Settings\Pages;

use App\Filament\Resources\Settings\SettingResource;
use Filament\Resources\Pages\ListRecords;

class ListSettings extends ListRecords
{
    protected static string $resource = SettingResource::class;

    public function mount(): void
    {
        $setting = \App\Models\Setting::first();

        if (! $setting) {
            $setting = \App\Models\Setting::create([]);
        }

        $this->redirect(SettingResource::getUrl('edit', ['record' => $setting->id]));
    }
}
