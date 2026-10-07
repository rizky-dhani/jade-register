<?php

use App\Models\Setting;
use Database\Seeders\SettingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('defines the digital workshop registration settings', function () {
    $this->seed(SettingSeeder::class);

    foreach ([
        'digital_workshop_registration_open',
        'digital_workshop_registration_opens_at',
        'digital_workshop_registration_close_at',
    ] as $key) {
        expect(Setting::where('key', $key)->exists())->toBeTrue();
    }
});

test('defaults registration_open to true as a real boolean', function () {
    $this->seed(SettingSeeder::class);

    // Review Focus 2: a missing row must mean "open", and the type must be bool
    // rather than '1' or 1, because the gate returns this value directly.
    expect(Setting::get('digital_workshop_registration_open'))->toBeTrue();
});

test('defaults the opens_at and close_at windows to null', function () {
    $this->seed(SettingSeeder::class);

    expect(Setting::get('digital_workshop_registration_opens_at'))->toBeNull();
    expect(Setting::get('digital_workshop_registration_close_at'))->toBeNull();
});

test('returns a default when the setting row is missing', function () {
    // No seeder, no rows at all — a fresh database seeded before this key existed.
    expect(Setting::get('digital_workshop_registration_open', true))->toBeTrue();
    expect(Setting::get('digital_workshop_registration_opens_at'))->toBeNull();
    expect(Setting::get('digital_workshop_registration_close_at'))->toBeNull();
});
