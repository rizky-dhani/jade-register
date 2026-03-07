<?php

namespace App\Filament\Resources\Settings\Pages;

use App\Filament\Resources\Settings\SettingResource;
use App\Models\Setting;
use Filament\Resources\Pages\EditRecord;

class EditSetting extends EditRecord
{
    protected static string $resource = SettingResource::class;

    protected function getRecord(): Setting
    {
        return Setting::first() ?? new Setting;
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $setting = $this->getRecord();

        if (! $setting->exists) {
            $setting->save();
        }

        return $data;
    }
}
