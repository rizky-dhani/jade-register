<?php

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

    livewire(\App\Livewire\AttendanceVerify::class, ['token' => $this->registration->qr_token])
        ->assertSee($this->registration->name)
        ->assertSee($this->registration->email)
        ->assertSee($this->registration->registration_code);
});

test('staff can check in seminar', function () {
    $this->actingAs($this->user);

    livewire(\App\Livewire\AttendanceVerify::class, ['token' => $this->registration->qr_token])
        ->call('checkInSeminar')
        ->assertSee('Check-in successful!')
        ->assertSee('Already checked in');

    $this->assertDatabaseHas('attendances', [
        'seminar_registration_id' => $this->registration->id,
        'activity_type' => 'seminar',
        'checked_in_by' => $this->user->id,
    ]);
});

test('staff cannot check in seminar if payment not verified', function () {
    $pendingRegistration = SeminarRegistration::factory()->pending()->create();
    $this->actingAs($this->user);

    livewire(\App\Livewire\AttendanceVerify::class, ['token' => $pendingRegistration->qr_token])
        ->call('checkInSeminar')
        ->assertSee('Cannot check in - payment is not verified.');

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

    livewire(\App\Livewire\AttendanceVerify::class, ['token' => $this->registration->qr_token])
        ->assertSee('Already checked in')
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

    livewire(\App\Livewire\AttendanceVerify::class, ['token' => $this->registration->qr_token])
        ->call('checkInHandsOn', $handsOnReg->id)
        ->assertSee('Check-in successful!');

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

    livewire(\App\Livewire\AttendanceVerify::class, ['token' => $this->registration->qr_token])
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

    livewire(\App\Livewire\AttendanceVerify::class, ['token' => $this->registration->qr_token])
        ->assertSee('Checked in')
        ->call('checkInHandsOn', $handsOnReg->id);

    $this->assertDatabaseCount('attendances', 1);
});

test('verification page shows warning for unverified payment', function () {
    $this->actingAs($this->user);

    $pendingRegistration = SeminarRegistration::factory()->pending()->create();

    livewire(\App\Livewire\AttendanceVerify::class, ['token' => $pendingRegistration->qr_token])
        ->assertSee('Payment is not verified - check-in is disabled');
});

test('verification page shows expired message with expired token', function () {
    $this->actingAs($this->user);

    $expiredRegistration = SeminarRegistration::factory()->verified()->expired()->create();

    livewire(\App\Livewire\AttendanceVerify::class, ['token' => $expiredRegistration->qr_token])
        ->assertSee('QR Code Expired')
        ->assertDontSee($expiredRegistration->name);
});

test('verification page shows invalid message with invalid token', function () {
    $this->actingAs($this->user);

    livewire(\App\Livewire\AttendanceVerify::class, ['token' => 'invalid-token-123'])
        ->assertSee('Invalid QR Code');
});
