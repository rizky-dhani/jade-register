<?php

use App\Filament\Resources\SeminarRegistrations\Pages\ListSeminarRegistrations;
use App\Models\Seminar;
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

function seminarPackage(string $name): Seminar
{
    return Seminar::create([
        'name' => $name,
        'code' => 'filter-'.uniqid(),
        'applies_to' => 'all',
        'currency' => 'IDR',
        'original_price' => 900000,
        'includes_lunch' => false,
    ]);
}

it('filters seminar registrations by the selected package', function () {
    $basicPackage = seminarPackage('Basic Seminar');
    $proPackage = seminarPackage('Pro Seminar');

    $basicRegistration = SeminarRegistration::factory()->create(['seminar_id' => $basicPackage->id]);
    $proRegistration = SeminarRegistration::factory()->create(['seminar_id' => $proPackage->id]);

    livewire(ListSeminarRegistrations::class)
        ->filterTable('seminar_id', $proPackage->id)
        ->assertCanSeeTableRecords([$proRegistration])
        ->assertCanNotSeeTableRecords([$basicRegistration]);
});

it('shows every seminar registration when the package filter is cleared', function () {
    $basicPackage = seminarPackage('Basic Seminar');
    $proPackage = seminarPackage('Pro Seminar');

    $basicRegistration = SeminarRegistration::factory()->create(['seminar_id' => $basicPackage->id]);
    $proRegistration = SeminarRegistration::factory()->create(['seminar_id' => $proPackage->id]);

    livewire(ListSeminarRegistrations::class)
        ->assertCanSeeTableRecords([$basicRegistration, $proRegistration]);
});
