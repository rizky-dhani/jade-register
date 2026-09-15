<?php

use App\Filament\Resources\Settings\Pages\EditSetting;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;

use function Pest\Laravel\actingAs;
use function Pest\Livewire\livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    Role::create(['name' => 'Super Admin', 'guard_name' => 'web']);

    $user = User::factory()->create();
    $user->assignRole('Super Admin');
    actingAs($user);
});

it('fills the current value for a string setting', function () {
    $setting = Setting::create([
        'key' => 'bank_name',
        'label' => 'Bank Name',
        'value' => 'Bank BNI',
        'type' => 'string',
    ]);

    livewire(EditSetting::class, ['record' => $setting->getRouteKey()])
        ->assertOk()
        ->assertSchemaStateSet(['value_string' => 'Bank BNI']);
});

it('fills the current value for an integer setting', function () {
    $setting = Setting::create([
        'key' => 'max_participants',
        'label' => 'Max Participants',
        'value' => '1000',
        'type' => 'integer',
    ]);

    livewire(EditSetting::class, ['record' => $setting->getRouteKey()])
        ->assertOk()
        ->assertSchemaStateSet(['value_integer' => '1000']);
});

it('fills the current value for a boolean setting', function () {
    $setting = Setting::create([
        'key' => 'registration_open',
        'label' => 'Registration Open',
        'value' => '1',
        'type' => 'boolean',
    ]);

    livewire(EditSetting::class, ['record' => $setting->getRouteKey()])
        ->assertOk()
        ->assertSchemaStateSet(['value_boolean' => true]);
});

it('fills the current value for a datetime setting', function () {
    $setting = Setting::create([
        'key' => 'seminar_registration_close_at',
        'label' => 'Close At',
        'value' => '2026-06-01 00:00',
        'type' => 'datetime',
    ]);

    livewire(EditSetting::class, ['record' => $setting->getRouteKey()])
        ->assertOk()
        ->assertSchemaStateSet(['value_datetime' => '2026-06-01 00:00']);
});

it('saves a boolean setting back to the value column', function () {
    $setting = Setting::create([
        'key' => 'registration_open',
        'label' => 'Registration Open',
        'value' => '1',
        'type' => 'boolean',
    ]);

    livewire(EditSetting::class, ['record' => $setting->getRouteKey()])
        ->fillForm(['value_boolean' => false])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($setting->fresh()->value)->toBe('0');
});

it('saves a string setting back to the value column', function () {
    $setting = Setting::create([
        'key' => 'bank_name',
        'label' => 'Bank Name',
        'value' => 'Bank BNI',
        'type' => 'string',
    ]);

    livewire(EditSetting::class, ['record' => $setting->getRouteKey()])
        ->fillForm(['value_string' => 'Bank Mandiri'])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($setting->fresh()->value)->toBe('Bank Mandiri');
});
