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

test('settings editor fills and saves date-only values', function () {
    $setting = Setting::create([
        'key' => 'seminar_event_date',
        'label' => 'Seminar Event Date',
        'value' => '2026-11-15',
        'type' => 'date',
    ]);

    livewire(EditSetting::class, ['record' => $setting->getRouteKey()])
        ->assertOk()
        ->assertSchemaStateSet(['value_date' => '2026-11-15'])
        ->set('data.value_date', '2026-11-16')
        ->call('save')
        ->assertHasNoFormErrors();

    expect($setting->fresh()->value)->toBe('2026-11-16');
});

test('settings editor can clear an optional date', function () {
    $setting = Setting::create([
        'key' => 'seminar_event_date',
        'label' => 'Seminar Event Date',
        'value' => '2026-11-15',
        'type' => 'date',
    ]);

    livewire(EditSetting::class, ['record' => $setting->getRouteKey()])
        ->set('data.value_date', null)
        ->call('save')
        ->assertHasNoFormErrors();

    expect($setting->fresh()->value)->toBe('');
});
