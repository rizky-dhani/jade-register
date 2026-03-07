<?php

namespace App\Filament\Resources\Settings\Pages;

use App\Filament\Resources\Settings\SettingResource;
use App\Models\Setting;
use Filament\Resources\Pages\EditRecord;

class EditSetting extends EditRecord
{
    protected static string $resource = SettingResource::class;

    public function getRecord(): Setting
    {
        $setting = Setting::first();

        if (! $setting) {
            $setting = Setting::create([]);
        }

        return $setting;
    }
}
