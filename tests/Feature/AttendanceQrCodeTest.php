<?php

use App\Livewire\AttendanceQrCode;
use App\Models\HandsOn;
use App\Models\HandsOnRegistration;
use App\Models\SeminarRegistration;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Livewire\livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->registration = SeminarRegistration::factory()->verified()->create();
});

test('user can view QR code page with valid token', function () {
    livewire(AttendanceQrCode::class, ['token' => $this->registration->qr_token])
        ->assertSee($this->registration->name)
        ->assertSee($this->registration->email)
        ->assertSee($this->registration->registration_code)
        ->assertSee('Verified');
});

test('user sees expired message with expired token', function () {
    $expiredRegistration = SeminarRegistration::factory()->verified()->expired()->create();

    livewire(AttendanceQrCode::class, ['token' => $expiredRegistration->qr_token])
        ->assertSee('QR Code Expired')
        ->assertDontSee($expiredRegistration->name);
});

test('user sees invalid message with invalid token', function () {
    livewire(AttendanceQrCode::class, ['token' => 'invalid-token-123'])
        ->assertSee('Invalid QR Code')
        ->assertDontSee('registration_code');
});

test('QR code page shows hands-on sessions', function () {
    $handsOn = HandsOn::factory()->create();
    $handsOnReg = HandsOnRegistration::factory()->verified()->create([
        'seminar_registration_id' => $this->registration->id,
        'hands_on_id' => $handsOn->id,
    ]);

    livewire(AttendanceQrCode::class, ['token' => $this->registration->qr_token])
        ->assertSee($handsOn->name);
});

test('QR code page does not show unverified hands-on sessions', function () {
    $handsOn = HandsOn::factory()->create();
    $handsOnReg = HandsOnRegistration::factory()->create([
        'seminar_registration_id' => $this->registration->id,
        'hands_on_id' => $handsOn->id,
        'payment_status' => 'pending',
    ]);

    livewire(AttendanceQrCode::class, ['token' => $this->registration->qr_token])
        ->assertDontSee($handsOn->name);
});

test('QR code page shows payment status correctly', function () {
    $pendingRegistration = SeminarRegistration::factory()->pending()->create();

    livewire(AttendanceQrCode::class, ['token' => $pendingRegistration->qr_token])
        ->assertSee('Pending');

    $rejectedRegistration = SeminarRegistration::factory()->rejected()->create();

    livewire(AttendanceQrCode::class, ['token' => $rejectedRegistration->qr_token])
        ->assertSee('Rejected');
});
