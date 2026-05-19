<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        foreach (Setting::defined() as $key => $definition) {
            Setting::updateOrCreate(
                ['key' => $key],
                [
                    'label' => $definition['label'],
                    'value' => $definition['default'] ?? '',
                    'type' => $definition['type'],
                    'description' => $definition['description'] ?? '',
                ]
            );
        }
    }
}
