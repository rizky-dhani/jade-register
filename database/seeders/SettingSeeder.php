<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        Setting::create([
            'key' => 'max_participants',
            'value' => '500',
            'type' => 'integer',
            'description' => 'Maximum number of seminar participants across all packages',
        ]);
    }
}
