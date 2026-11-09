<?php

use App\Filament\Resources\Attendances\AttendanceResource;
use App\Filament\Resources\Attendances\Pages\ListLunchAttendances;
use App\Models\Addon;
use App\Models\LunchRedemption;
use App\Models\Seminar;
use App\Models\SeminarRegistration;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;
use function Pest\Livewire\livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);

    $this->user = User::factory()->create(['name' => 'Desk Admin']);
    $this->user->assignRole('Admin');
    actingAs($this->user);
});

/**
 * A verified registration for a seminar package that includes lunch.
 */
function lunchSeminarRegistration(array $attributes = []): SeminarRegistration
{
    $package = Seminar::create([
        'name' => 'Seminar (+ Lunch)',
        'code' => 'lunch-'.uniqid(),
        'applies_to' => 'all',
        'currency' => 'IDR',
        'includes_lunch' => true,
    ]);

    return SeminarRegistration::factory()->verified()->create([
        'seminar_id' => $package->id,
        'selected_seminar' => $package->name,
        ...$attributes,
    ]);
}

it('lists lunch redemptions with the participant and the serving staff', function () {
    $registration = lunchSeminarRegistration(['name_license' => 'Ayu Kartika']);
    LunchRedemption::factory()->create([
        'seminar_registration_id' => $registration->id,
        'redeemed_by' => $this->user->id,
        'redeemed_by_name' => 'Desk Admin',
    ]);

    livewire(ListLunchAttendances::class)
        ->assertOk()
        ->assertCanSeeTableRecords(LunchRedemption::all())
        ->assertSee('Ayu Kartika')
        ->assertSee($registration->registration_code)
        ->assertSee('Desk Admin');
});

it('does not list participants who have not redeemed lunch', function () {
    $redeemed = lunchSeminarRegistration(['name_license' => 'Ayu Kartika']);
    LunchRedemption::factory()->create(['seminar_registration_id' => $redeemed->id]);

    lunchSeminarRegistration(['name_license' => 'Budi Santoso']);

    livewire(ListLunchAttendances::class)
        ->assertOk()
        ->assertCanSeeTableRecords(LunchRedemption::all())
        ->assertSee('Ayu Kartika')
        ->assertDontSee('Budi Santoso');
});

it('searches redemptions by participant name and registration code', function () {
    $ayu = lunchSeminarRegistration(['name_license' => 'Ayu Kartika']);
    $budi = lunchSeminarRegistration(['name_license' => 'Budi Santoso']);

    $ayuRedemption = LunchRedemption::factory()->create(['seminar_registration_id' => $ayu->id]);
    $budiRedemption = LunchRedemption::factory()->create(['seminar_registration_id' => $budi->id]);

    livewire(ListLunchAttendances::class)
        ->searchTable('Ayu')
        ->assertCanSeeTableRecords([$ayuRedemption])
        ->assertCanNotSeeTableRecords([$budiRedemption]);

    livewire(ListLunchAttendances::class)
        ->searchTable($budi->registration_code)
        ->assertCanSeeTableRecords([$budiRedemption])
        ->assertCanNotSeeTableRecords([$ayuRedemption]);
});

it('reports whether lunch came from the package or from the add-on', function () {
    $packageRegistration = lunchSeminarRegistration(['name_license' => 'Ayu Kartika']);
    $addonRegistration = SeminarRegistration::factory()->verified()->create([
        'name_license' => 'Citra Dewi',
    ]);
    $addon = Addon::create([
        'name' => 'Lunch Add-on',
        'code' => 'LUNCH_ADDON',
        'price' => 100000,
        'currency' => 'IDR',
        'is_active' => true,
    ]);
    $addonRegistration->addonRegistrations()->create([
        'addon_id' => $addon->id,
        'amount' => $addon->price,
        'currency' => $addon->currency,
        'payment_proof_path' => 'payment-proofs/lunch-addon.jpg',
        'payment_status' => 'verified',
    ]);

    LunchRedemption::factory()->create(['seminar_registration_id' => $packageRegistration->id]);
    LunchRedemption::factory()->create(['seminar_registration_id' => $addonRegistration->id]);

    livewire(ListLunchAttendances::class)
        ->assertOk()
        ->assertSee(__('filament.lunch_attendance.entitlement_package'))
        ->assertSee(__('filament.lunch_attendance.entitlement_addon'));
});

it('is reachable from the attendance resource and the navigation', function () {
    get(AttendanceResource::getUrl('lunch'))->assertOk();

    expect(AttendanceResource::getUrl('lunch'))
        ->toContain('/attendances/lunch');

    livewire(ListLunchAttendances::class)->assertOk();
});

it('denies the lunch log to roles without attendance access', function () {
    foreach (['Participant', 'Judge'] as $role) {
        $user = User::factory()->create();
        $user->assignRole($role);
        actingAs($user);

        expect($user->can('viewAny', LunchRedemption::class))->toBeFalse();

        get(AttendanceResource::getUrl('lunch'))->assertForbidden();
    }
});
