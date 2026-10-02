<?php

use App\Models\Setting;
use Database\Seeders\SettingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('defines a whatsapp group url setting with the active group link as default', function () {
    $definition = config('settings.whatsapp_group_url');

    expect($definition)->toBeArray()
        ->and($definition['type'])->toBe('string')
        ->and($definition['default'])->toBe('https://chat.whatsapp.com/FOUtwzgjBodABp1TdsEQcH?s=cl&p=a&mlu=4&ilr=4');
});

it('seeds the whatsapp group url setting and reads it back as a string', function () {
    $this->seed(SettingSeeder::class);

    $setting = Setting::where('key', 'whatsapp_group_url')->first();

    expect($setting)->not->toBeNull()
        ->and($setting->type)->toBe('string')
        ->and(Setting::get('whatsapp_group_url'))
        ->toBe('https://chat.whatsapp.com/FOUtwzgjBodABp1TdsEQcH?s=cl&p=a&mlu=4&ilr=4');
});

it('returns the default when the whatsapp group url setting is absent', function () {
    expect(Setting::get('whatsapp_group_url', 'fallback'))->toBe('fallback');
});
