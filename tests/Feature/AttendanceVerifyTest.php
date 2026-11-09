<?php

use App\Livewire\AttendanceVerify;
use App\Models\Attendance;
use App\Models\HandsOn;
use App\Models\HandsOnRegistration;
use App\Models\SeminarRegistration;
use App\Models\User;
use App\Services\QrTokenService;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Livewire\livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);

    $this->user = User::factory()->create();
    $this->user->assignRole('Admin');
    $this->registration = SeminarRegistration::factory()->verified()->create();
});

test('staff can view verification page with valid token', function () {
    $this->actingAs($this->user);

    livewire(AttendanceVerify::class, ['token' => $this->registration->qr_token])
        ->assertSee($this->registration->name_license)
        ->assertSee($this->registration->email)
        ->assertSee($this->registration->registration_code);
});

test('staff can check in seminar', function () {
    $this->actingAs($this->user);

    livewire(AttendanceVerify::class, ['token' => $this->registration->qr_token])
        ->call('checkInSeminarDay', 1)
        ->assertSee(__('seminar.attendance_check_in_success'))
        ->assertSee(__('seminar.checked_in'));

    $this->assertDatabaseHas('attendances', [
        'seminar_registration_id' => $this->registration->id,
        'activity_type' => 'seminar',
        'day' => 1,
        'checked_in_by' => $this->user->id,
    ]);
});

test('staff cannot check in seminar if payment not verified', function () {
    $pendingRegistration = SeminarRegistration::factory()->pending()->create();
    $this->actingAs($this->user);

    livewire(AttendanceVerify::class, ['token' => $pendingRegistration->qr_token])
        ->call('checkInSeminarDay', 1)
        ->assertSee(__('seminar.payment_not_verified'));

    $this->assertDatabaseMissing('attendances', [
        'seminar_registration_id' => $pendingRegistration->id,
    ]);
});

test('staff cannot check in seminar twice', function () {
    $this->actingAs($this->user);

    Attendance::factory()->create([
        'seminar_registration_id' => $this->registration->id,
        'activity_type' => 'seminar',
        'checked_in_by' => $this->user->id,
    ]);

    livewire(AttendanceVerify::class, ['token' => $this->registration->qr_token])
        ->assertSee(__('seminar.checked_in'))
        ->call('checkInSeminarDay', 1);

    $this->assertDatabaseCount('attendances', 1);
});

test('staff can check in hands-on session', function () {
    $this->actingAs($this->user);

    $handsOn = HandsOn::factory()->create();
    $handsOnReg = HandsOnRegistration::factory()->verified()->create([
        'seminar_registration_id' => $this->registration->id,
        'hands_on_id' => $handsOn->id,
    ]);

    livewire(AttendanceVerify::class, ['token' => $this->registration->qr_token])
        ->call('checkInHandsOn', $handsOnReg->id)
        ->assertSee(__('seminar.attendance_check_in_success'));

    $this->assertDatabaseHas('attendances', [
        'seminar_registration_id' => $this->registration->id,
        'hands_on_registration_id' => $handsOnReg->id,
        'activity_type' => 'hands_on',
        'checked_in_by' => $this->user->id,
    ]);
});

test('staff cannot check in hands-on if payment not verified', function () {
    $this->actingAs($this->user);

    $handsOn = HandsOn::factory()->create();
    $handsOnReg = HandsOnRegistration::factory()->create([
        'seminar_registration_id' => $this->registration->id,
        'hands_on_id' => $handsOn->id,
        'payment_status' => 'pending',
    ]);

    livewire(AttendanceVerify::class, ['token' => $this->registration->qr_token])
        ->call('checkInHandsOn', $handsOnReg->id)
        ->assertHasErrors(['hands_on_'.$handsOnReg->id]);

    $this->assertDatabaseMissing('attendances', [
        'hands_on_registration_id' => $handsOnReg->id,
    ]);
});

test('staff cannot check in hands-on twice', function () {
    $this->actingAs($this->user);

    $handsOn = HandsOn::factory()->create();
    $handsOnReg = HandsOnRegistration::factory()->verified()->create([
        'seminar_registration_id' => $this->registration->id,
        'hands_on_id' => $handsOn->id,
    ]);

    Attendance::factory()->handsOn()->create([
        'seminar_registration_id' => $this->registration->id,
        'hands_on_registration_id' => $handsOnReg->id,
        'checked_in_by' => $this->user->id,
    ]);

    livewire(AttendanceVerify::class, ['token' => $this->registration->qr_token])
        ->assertSee(__('seminar.checked_in'))
        ->call('checkInHandsOn', $handsOnReg->id);

    $this->assertDatabaseCount('attendances', 1);
});

test('verification page shows warning for unverified payment', function () {
    $this->actingAs($this->user);

    $pendingRegistration = SeminarRegistration::factory()->pending()->create();

    livewire(AttendanceVerify::class, ['token' => $pendingRegistration->qr_token])
        ->assertSee(__('seminar.payment_not_verified_checkin_disabled'));
});

test('verification page shows expired message with expired token', function () {
    $this->actingAs($this->user);

    $expiredRegistration = SeminarRegistration::factory()->verified()->expired()->create();

    livewire(AttendanceVerify::class, ['token' => $expiredRegistration->qr_token])
        ->assertSee(__('seminar.qr_code_expired_title'))
        ->assertDontSee($expiredRegistration->name_license);
});

test('verification page shows invalid message with invalid token', function () {
    $this->actingAs($this->user);

    livewire(AttendanceVerify::class, ['token' => 'invalid-token-123'])
        ->assertSee(__('seminar.invalid_qr_code_title'));
});

test('staff can check in hands-on from a hands-on token that is linked to a seminar registration', function () {
    $this->actingAs($this->user);

    $handsOn = HandsOn::factory()->create();
    $handsOnReg = HandsOnRegistration::factory()->verified()->create([
        'seminar_registration_id' => $this->registration->id,
        'hands_on_id' => $handsOn->id,
    ]);
    app(QrTokenService::class)->generateForHandsOn($handsOnReg);

    livewire(AttendanceVerify::class, ['token' => $handsOnReg->qr_token])
        ->call('checkInHandsOn', $handsOnReg->id);

    $this->assertDatabaseHas('attendances', [
        'seminar_registration_id' => $this->registration->id,
        'hands_on_registration_id' => $handsOnReg->id,
        'activity_type' => 'hands_on',
        'checked_in_by' => $this->user->id,
    ]);
});

test('staff can check in a standalone hands-on registration that has no seminar registration', function () {
    $this->actingAs($this->user);

    $handsOn = HandsOn::factory()->create();
    $handsOnReg = HandsOnRegistration::factory()->verified()->create([
        'seminar_registration_id' => null,
        'hands_on_id' => $handsOn->id,
        'registration_type' => 'hands_on',
    ]);
    app(QrTokenService::class)->generateForHandsOn($handsOnReg);

    livewire(AttendanceVerify::class, ['token' => $handsOnReg->qr_token])
        ->call('checkInHandsOn', $handsOnReg->id);

    $this->assertDatabaseHas('attendances', [
        'seminar_registration_id' => null,
        'hands_on_registration_id' => $handsOnReg->id,
        'activity_type' => 'hands_on',
        'checked_in_by' => $this->user->id,
    ]);
});

test('checking in the same hands-on registration twice records one attendance', function () {
    $this->actingAs($this->user);

    $handsOn = HandsOn::factory()->create();
    $handsOnReg = HandsOnRegistration::factory()->verified()->create([
        'seminar_registration_id' => null,
        'hands_on_id' => $handsOn->id,
        'registration_type' => 'hands_on',
    ]);
    app(QrTokenService::class)->generateForHandsOn($handsOnReg);

    $component = livewire(AttendanceVerify::class, ['token' => $handsOnReg->qr_token]);
    $component->call('checkInHandsOn', $handsOnReg->id);

    // A second scan arrives as a fresh request against the same token.
    livewire(AttendanceVerify::class, ['token' => $handsOnReg->qr_token])
        ->call('checkInHandsOn', $handsOnReg->id);

    $this->assertDatabaseCount('attendances', 1);
});

/**
 * Participants can self-register at /admin/register, and the verify URL is
 * printed in their confirmation email — so the check-in buttons and the actions
 * behind them must be closed to anyone without a desk role.
 */
test('a participant cannot check in the seminar', function () {
    $participant = User::factory()->create();
    $participant->assignRole('Participant');
    $this->actingAs($participant);

    livewire(AttendanceVerify::class, ['token' => $this->registration->qr_token])
        ->call('checkInSeminarDay', 1)
        ->assertHasErrors(['auth'])
        ->assertDontSee(__('seminar.check_in_seminar_button'));

    $this->assertDatabaseCount('attendances', 0);
});

test('a participant cannot check in a hands-on session', function () {
    $participant = User::factory()->create();
    $participant->assignRole('Participant');
    $this->actingAs($participant);

    $handsOn = HandsOn::factory()->create();
    $handsOnReg = HandsOnRegistration::factory()->verified()->create([
        'seminar_registration_id' => $this->registration->id,
        'hands_on_id' => $handsOn->id,
    ]);

    livewire(AttendanceVerify::class, ['token' => $this->registration->qr_token])
        ->call('checkInHandsOn', $handsOnReg->id)
        ->assertHasErrors(['auth']);

    $this->assertDatabaseMissing('attendances', [
        'hands_on_registration_id' => $handsOnReg->id,
    ]);
});

test('a guest is not offered the check-in buttons', function () {
    $this->actingAs(User::factory()->create());

    livewire(AttendanceVerify::class, ['token' => $this->registration->qr_token])
        ->assertSee(__('seminar.admin_only_action'))
        ->assertDontSee(__('seminar.check_in_day_button', ['day' => 1]));
});

test('a super admin can check in the seminar', function () {
    $superAdmin = User::factory()->create();
    $superAdmin->assignRole('Super Admin');
    $this->actingAs($superAdmin);

    livewire(AttendanceVerify::class, ['token' => $this->registration->qr_token])
        ->assertSee(__('seminar.check_in_day_button', ['day' => 1]))
        ->call('checkInSeminarDay', 1)
        ->assertHasNoErrors();

    $this->assertDatabaseCount('attendances', 1);
});

test('two simultaneous seminar scans leave a single attendance row', function () {
    $this->actingAs($this->user);

    // Two desks hit the same participant at once: clear the in-memory guard the
    // way a second, concurrent mount would have it, so only the database
    // uniqueness check protects the row. The unique index includes the nullable
    // hands_on_registration_id and therefore cannot block this insert — the
    // firstOrCreate lookup is what has to hold.
    $component = livewire(AttendanceVerify::class, ['token' => $this->registration->qr_token]);
    $component->call('checkInSeminarDay', 1);
    $component->set('seminarDayCheckedIn', [])->call('checkInSeminarDay', 1);

    $this->assertDatabaseCount('attendances', 1);
});

test('staff can check out a seminar participant', function () {
    $this->actingAs($this->user);

    $attendance = Attendance::factory()->create([
        'seminar_registration_id' => $this->registration->id,
        'activity_type' => 'seminar',
        'checked_in_by' => $this->user->id,
    ]);

    livewire(AttendanceVerify::class, ['token' => $this->registration->qr_token])
        ->call('checkOutSeminar')
        ->assertSee(__('seminar.attendance_check_out_success'))
        ->assertSee(__('seminar.checked_out'));

    $attendance->refresh();

    expect($attendance->checked_out_at)->not->toBeNull()
        ->and($attendance->checked_out_by)->toBe($this->user->id);
});

test('a seminar participant cannot be checked out before checking in', function () {
    $this->actingAs($this->user);

    livewire(AttendanceVerify::class, ['token' => $this->registration->qr_token])
        ->call('checkOutSeminar')
        ->assertHasErrors(['check_out']);

    $this->assertDatabaseCount('attendances', 0);
});

test('checking out a seminar participant twice keeps the first check-out', function () {
    $this->actingAs($this->user);

    $attendance = Attendance::factory()->create([
        'seminar_registration_id' => $this->registration->id,
        'activity_type' => 'seminar',
        'checked_in_by' => $this->user->id,
    ]);

    livewire(AttendanceVerify::class, ['token' => $this->registration->qr_token])
        ->call('checkOutSeminar')
        ->assertHasNoErrors();

    $attendance->refresh();
    $firstCheckOutAt = $attendance->checked_out_at;
    $firstCheckedOutBy = $attendance->checked_out_by;

    $otherStaff = User::factory()->create();
    $otherStaff->assignRole('Admin');
    $this->actingAs($otherStaff);

    livewire(AttendanceVerify::class, ['token' => $this->registration->qr_token])
        ->call('checkOutSeminar')
        ->assertHasNoErrors();

    $attendance->refresh();

    expect($attendance->checked_out_at->equalTo($firstCheckOutAt))->toBeTrue()
        ->and($attendance->checked_out_by)->toBe($firstCheckedOutBy);
});

test('staff can check out a hands-on session', function () {
    $this->actingAs($this->user);

    $handsOn = HandsOn::factory()->create();
    $handsOnReg = HandsOnRegistration::factory()->verified()->create([
        'seminar_registration_id' => $this->registration->id,
        'hands_on_id' => $handsOn->id,
    ]);

    $attendance = Attendance::factory()->handsOn()->create([
        'seminar_registration_id' => $this->registration->id,
        'hands_on_registration_id' => $handsOnReg->id,
        'checked_in_by' => $this->user->id,
    ]);

    livewire(AttendanceVerify::class, ['token' => $this->registration->qr_token])
        ->call('checkOutHandsOn', $handsOnReg->id)
        ->assertSee(__('seminar.attendance_check_out_success'))
        ->assertSee(__('seminar.checked_out'));

    $attendance->refresh();

    expect($attendance->checked_out_at)->not->toBeNull()
        ->and($attendance->checked_out_by)->toBe($this->user->id);
});

test('a hands-on session cannot be checked out before checking in', function () {
    $this->actingAs($this->user);

    $handsOn = HandsOn::factory()->create();
    $handsOnReg = HandsOnRegistration::factory()->verified()->create([
        'seminar_registration_id' => $this->registration->id,
        'hands_on_id' => $handsOn->id,
    ]);

    livewire(AttendanceVerify::class, ['token' => $this->registration->qr_token])
        ->call('checkOutHandsOn', $handsOnReg->id)
        ->assertHasErrors(['hands_on_check_out_'.$handsOnReg->id]);

    $this->assertDatabaseCount('attendances', 0);
});

test('a participant cannot check out the seminar', function () {
    $participant = User::factory()->create();
    $participant->assignRole('Participant');
    $this->actingAs($participant);

    $attendance = Attendance::factory()->create([
        'seminar_registration_id' => $this->registration->id,
        'activity_type' => 'seminar',
        'checked_in_by' => $participant->id,
    ]);

    livewire(AttendanceVerify::class, ['token' => $this->registration->qr_token])
        ->call('checkOutSeminar')
        ->assertHasErrors(['auth'])
        ->assertDontSee(__('seminar.check_out_seminar_button'));

    expect($attendance->refresh()->checked_out_at)->toBeNull();
});
