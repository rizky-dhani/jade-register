<?php

use App\Filament\Resources\Settings\Pages\CreateSetting;
use App\Filament\Resources\Settings\SettingResource;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;

use function Pest\Laravel\actingAs;
use function Pest\Livewire\livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    Role::create(['name' => 'Super Admin', 'guard_name' => 'web']);
    Role::create(['name' => 'Admin', 'guard_name' => 'web']);
});

it('lets a Super Admin open the create setting page', function () {
    $user = User::factory()->create();
    $user->assignRole('Super Admin');
    actingAs($user);

    $this->get(SettingResource::getUrl('create'))->assertOk();

    livewire(CreateSetting::class)->assertOk();
});

it('creates a setting that is not defined in config', function () {
    $user = User::factory()->create();
    $user->assignRole('Super Admin');
    actingAs($user);

    livewire(CreateSetting::class)
        ->fillForm([
            'label' => 'Seminar Registration Close At',
            'key' => 'seminar_registration_close_at',
            'type' => 'datetime',
            'value_datetime' => '2026-11-10 23:59',
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $setting = Setting::where('key', 'seminar_registration_close_at')->first();

    expect($setting)->not->toBeNull();
    expect($setting->value)->toBe('2026-11-10 23:59:00');
});

it('denies the create setting page to an Admin', function () {
    $user = User::factory()->create();
    $user->assignRole('Admin');
    actingAs($user);

    $this->get(SettingResource::getUrl('create'))->assertForbidden();

    livewire(CreateSetting::class)->assertForbidden();
});
