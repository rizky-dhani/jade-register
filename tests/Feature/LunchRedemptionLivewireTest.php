<?php

use App\Livewire\AttendanceVerify;
use App\Models\Addon;
use App\Models\AddonRegistration;
use App\Models\Seminar;
use App\Models\SeminarRegistration;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

use function Pest\Livewire\livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    Role::create(['name' => 'Admin', 'guard_name' => 'web']);
    Role::create(['name' => 'Super Admin', 'guard_name' => 'web']);
    Role::create(['name' => 'Participant', 'guard_name' => 'web']);

    // Desk check-in is a permission, not a role name: roles created without
    // permissions legitimately hold none (see AttendancePolicy::checkIn).
    foreach (['check in participants', 'check out participants', 'view attendances'] as $permission) {
        Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
    }
    Role::findByName('Admin', 'web')->givePermissionTo(['check in participants', 'check out participants', 'view attendances']);
    Setting::create([
        'key' => 'seminar_event_date',
        'label' => 'Seminar Event Date',
        'value' => today()->toDateString(),
        'type' => 'date',
    ]);
});

function lunchEligibleRegistrationForLivewire(bool $includesLunch = true): SeminarRegistration
{
    $seminar = Seminar::create([
        'name' => 'Lunch Seminar',
        'code' => 'lw-lunch-'.uniqid(),
        'applies_to' => 'all',
        'currency' => 'IDR',
        'includes_lunch' => $includesLunch,
    ]);

    return SeminarRegistration::factory()->verified()->create(['seminar_id' => $seminar->id]);
}

function addLunchEntitlement(SeminarRegistration $registration): void
{
    $addon = Addon::create([
        'name' => 'Lunch Add-on',
        'code' => 'LUNCH_ADDON',
        'price' => 10000,
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
}

test('admin can redeem lunch separately from seminar check-in', function () {
    $staff = User::factory()->create(['name' => 'Admin Lunch Staff']);
    $staff->assignRole('Admin');
    $this->actingAs($staff);
    $registration = lunchEligibleRegistrationForLivewire();

    livewire(AttendanceVerify::class, ['token' => $registration->qr_token])
        ->call('redeemLunch')
        ->assertDontSee(__('seminar.attendance_check_in_success'))
        ->assertSee(__('lunch-coupon.redeemed'))
        ->assertSet('lunchRedemption.redeemed_by_name', 'Admin Lunch Staff');

    $this->assertDatabaseHas('lunch_redemptions', [
        'seminar_registration_id' => $registration->id,
        'redeemed_by' => $staff->id,
        'redeemed_by_name' => 'Admin Lunch Staff',
    ]);
    $this->assertDatabaseMissing('attendances', [
        'seminar_registration_id' => $registration->id,
        'activity_type' => 'seminar',
    ]);
});

test('super admin can redeem lunch and a participant cannot', function () {
    $superAdmin = User::factory()->create(['name' => 'Super Admin Staff']);
    $superAdmin->assignRole('Super Admin');
    $this->actingAs($superAdmin);
    $registration = lunchEligibleRegistrationForLivewire();

    livewire(AttendanceVerify::class, ['token' => $registration->qr_token])
        ->call('redeemLunch')
        ->assertSet('lunchRedemption.redeemed_by_name', 'Super Admin Staff');

    $participant = User::factory()->create();
    $participant->assignRole('Participant');
    $this->actingAs($participant);
    $anotherRegistration = lunchEligibleRegistrationForLivewire();

    livewire(AttendanceVerify::class, ['token' => $anotherRegistration->qr_token])
        ->call('redeemLunch')
        ->assertHasErrors('lunch');

    $this->assertDatabaseMissing('lunch_redemptions', [
        'seminar_registration_id' => $anotherRegistration->id,
    ]);
});

test('ordinary signed-in user cannot redeem lunch through a direct livewire call', function () {
    $this->actingAs(User::factory()->create());
    $registration = lunchEligibleRegistrationForLivewire();

    livewire(AttendanceVerify::class, ['token' => $registration->qr_token])
        ->call('redeemLunch')
        ->assertHasErrors('lunch');

    $this->assertDatabaseCount('lunch_redemptions', 0);
});

test('ineligible seminar registration has no lunch coupon', function () {
    $staff = User::factory()->create();
    $staff->assignRole('Admin');
    $this->actingAs($staff);
    $registration = lunchEligibleRegistrationForLivewire(false);

    livewire(AttendanceVerify::class, ['token' => $registration->qr_token])
        ->call('redeemLunch');

    $this->assertDatabaseCount('lunch_redemptions', 0);
});

test('lunch add-on grants eligibility and existing redemption remains visible after add-on removal', function () {
    $staff = User::factory()->create(['name' => 'Add-on Desk Staff']);
    $staff->assignRole('Admin');
    $this->actingAs($staff);
    $registration = lunchEligibleRegistrationForLivewire(false);
    addLunchEntitlement($registration);

    livewire(AttendanceVerify::class, ['token' => $registration->qr_token])
        ->assertSee(__('lunch-coupon.redeem'))
        ->call('redeemLunch')
        ->assertSet('lunchRedemption.redeemed_by_name', 'Add-on Desk Staff');

    $registration->addonRegistrations()->delete();

    livewire(AttendanceVerify::class, ['token' => $registration->qr_token])
        ->assertSee(__('lunch-coupon.redeemed'))
        ->assertSee('Add-on Desk Staff')
        ->assertDontSee(__('lunch-coupon.redeem'));
});

test('event date must be configured to today before redemption', function () {
    $staff = User::factory()->create();
    $staff->assignRole('Admin');
    $this->actingAs($staff);
    $registration = lunchEligibleRegistrationForLivewire();

    Setting::where('key', 'seminar_event_date')->update(['value' => today()->subDay()->toDateString()]);

    livewire(AttendanceVerify::class, ['token' => $registration->qr_token])
        ->assertDontSee(__('lunch-coupon.redeem'))
        ->call('redeemLunch')
        ->assertHasErrors('lunch');

    $this->assertDatabaseCount('lunch_redemptions', 0);
});

test('seminar check-in continues to work without redeeming lunch', function () {
    $staff = User::factory()->create();
    $staff->assignRole('Admin');
    $this->actingAs($staff);
    $registration = lunchEligibleRegistrationForLivewire();

    livewire(AttendanceVerify::class, ['token' => $registration->qr_token])
        ->call('checkInSeminarDay', 1);

    $this->assertDatabaseHas('attendances', [
        'seminar_registration_id' => $registration->id,
        'activity_type' => 'seminar',
    ]);
    $this->assertDatabaseCount('lunch_redemptions', 0);
});
