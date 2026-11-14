<?php

use App\Filament\Resources\SeminarRegistrations\Pages\ListSeminarRegistrations;
use App\Models\Addon;
use App\Models\AddonRegistration;
use App\Models\SeminarRegistration;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\actingAs;
use function Pest\Livewire\livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);

    $user = User::factory()->create();
    $user->assignRole('Super Admin');
    actingAs($user);
});

function registrationWithAddon(string $addonCode): SeminarRegistration
{
    $registration = SeminarRegistration::factory()->create();

    $addon = Addon::create([
        'name' => 'Filter Add-on '.$addonCode,
        'code' => $addonCode,
        'price' => 150000,
        'currency' => 'IDR',
        'is_active' => true,
    ]);

    AddonRegistration::create([
        'seminar_registration_id' => $registration->id,
        'addon_id' => $addon->id,
        'amount' => $addon->price,
        'currency' => $addon->currency,
        'payment_proof_path' => 'proof.jpg',
        'payment_status' => 'verified',
    ]);

    return $registration;
}

it('filters seminar registrations that bought the lunch add-on', function () {
    $withLunch = registrationWithAddon('LUNCH_ADDON');
    $withOtherAddon = registrationWithAddon('OTHER_ADDON');
    $withoutAnyAddon = SeminarRegistration::factory()->create();

    livewire(ListSeminarRegistrations::class)
        ->filterTable('has_lunch_addon', true)
        ->assertCanSeeTableRecords([$withLunch])
        ->assertCanNotSeeTableRecords([$withOtherAddon, $withoutAnyAddon]);
});

it('filters seminar registrations without the lunch add-on', function () {
    $withLunch = registrationWithAddon('LUNCH_ADDON');
    $withOtherAddon = registrationWithAddon('OTHER_ADDON');
    $withoutAnyAddon = SeminarRegistration::factory()->create();

    livewire(ListSeminarRegistrations::class)
        ->filterTable('has_lunch_addon', false)
        ->assertCanSeeTableRecords([$withOtherAddon, $withoutAnyAddon])
        ->assertCanNotSeeTableRecords([$withLunch]);
});

it('shows every seminar registration when the lunch add-on filter is cleared', function () {
    $withLunch = registrationWithAddon('LUNCH_ADDON');
    $withoutAnyAddon = SeminarRegistration::factory()->create();

    livewire(ListSeminarRegistrations::class)
        ->assertCanSeeTableRecords([$withLunch, $withoutAnyAddon]);
});
