<?php

use App\Livewire\AttendanceVerify;
use App\Models\Attendance;
use App\Models\HandsOn;
use App\Models\HandsOnRegistration;
use App\Models\SeminarRegistration;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Livewire\livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
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
        ->call('checkInSeminar')
        ->assertSee(__('seminar.attendance_check_in_success'))
        ->assertSee(__('seminar.already_checked_in'));

    $this->assertDatabaseHas('attendances', [
        'seminar_registration_id' => $this->registration->id,
        'activity_type' => 'seminar',
        'checked_in_by' => $this->user->id,
    ]);
});

test('staff cannot check in seminar if payment not verified', function () {
    $pendingRegistration = SeminarRegistration::factory()->pending()->create();
    $this->actingAs($this->user);

    livewire(AttendanceVerify::class, ['token' => $pendingRegistration->qr_token])
        ->call('checkInSeminar')
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
        ->assertSee(__('seminar.already_checked_in'))
        ->call('checkInSeminar');

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
        ->assertSee('QR Code Expired')
        ->assertDontSee($expiredRegistration->name_license);
});

test('verification page shows invalid message with invalid token', function () {
    $this->actingAs($this->user);

    livewire(AttendanceVerify::class, ['token' => 'invalid-token-123'])
        ->assertSee('Invalid QR Code');
});
