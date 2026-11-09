<?php

use App\Filament\Resources\SeminarRegistrations\Pages\ListSeminarRegistrations;
use App\Models\Seminar;
use App\Models\SeminarRegistration;
use App\Models\Setting;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\actingAs;
use function Pest\Livewire\livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);

    Setting::create([
        'key' => 'max_participants',
        'value' => '1000',
        'type' => 'integer',
    ]);

    $this->user = User::factory()->create();
    $this->user->assignRole('Super Admin');
    actingAs($this->user);
});

/**
 * The stored package names carry a lunch qualifier that duplicates the price
 * label. The desk reads the column as "Seminar" and "Seminar + Lunch".
 */
function registrationForPackage(Seminar $seminar): SeminarRegistration
{
    return SeminarRegistration::factory()->verified()->create([
        'seminar_id' => $seminar->id,
        'selected_seminar' => $seminar->name,
    ]);
}

it('shows the snack-only package as Seminar', function () {
    $seminar = Seminar::create([
        'name' => 'Seminar',
        'code' => 'pkg-snack-'.uniqid(),
        'applies_to' => 'all',
        'currency' => 'IDR',
        'original_price' => 900000,
    ]);

    registrationForPackage($seminar);

    livewire(ListSeminarRegistrations::class)
        ->assertOk()
        ->assertSee(__('seminar.package_name_snack_only'));
});

it('simplifies the lunch package name to Seminar + Lunch', function () {
    $seminar = Seminar::create([
        'name' => 'Seminar (+ Lunch)',
        'code' => 'pkg-lunch-'.uniqid(),
        'applies_to' => 'all',
        'currency' => 'IDR',
        'original_price' => 1200000,
        'includes_lunch' => true,
    ]);

    registrationForPackage($seminar);

    // The identity-copy action still carries the stored name in its clipboard
    // payload, so only the rendered package cell is asserted here.
    livewire(ListSeminarRegistrations::class)
        ->assertOk()
        ->assertSee(__('seminar.package_name_with_lunch'))
        ->assertSee('(Rp 1.200.000)');
});

it('leaves a differently titled package name alone', function () {
    $seminar = Seminar::create([
        'name' => 'Seminar International',
        'code' => 'pkg-intl-'.uniqid(),
        'applies_to' => 'international',
        'currency' => 'USD',
        'original_price' => 175,
    ]);

    registrationForPackage($seminar);

    livewire(ListSeminarRegistrations::class)
        ->assertOk()
        ->assertSee('Seminar International');
});
