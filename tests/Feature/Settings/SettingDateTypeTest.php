<?php

use App\Models\Setting;
use Database\Seeders\SettingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('seminar event date setting is defined and defaults to null', function () {
    $this->seed(SettingSeeder::class);

    $setting = Setting::where('key', 'seminar_event_date')->firstOrFail();

    expect($setting->type)->toBe('date')
        ->and(Setting::get('seminar_event_date'))->toBeNull();
});

test('date settings return a date-only Carbon value and blank becomes null', function () {
    Setting::create([
        'key' => 'seminar_event_date',
        'label' => 'Seminar Event Date',
        'value' => '2026-11-15',
        'type' => 'date',
    ]);

    expect(Setting::get('seminar_event_date')->toDateString())->toBe('2026-11-15');

    $setting = Setting::where('key', 'seminar_event_date')->firstOrFail();
    $setting->update(['value' => '']);

    expect(Setting::get('seminar_event_date'))->toBeNull();
});
