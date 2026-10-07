<?php

use App\Livewire\AttendanceQrCode;
use App\Livewire\AttendanceVerify;
use App\Models\Attendance;
use App\Models\Country;
use App\Models\DigitalWorkshop;
use App\Models\DigitalWorkshopRegistration;
use App\Models\HandsOnRegistration;
use App\Models\SeminarRegistration;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Livewire\livewire;

uses(RefreshDatabase::class);

/**
 * QrTokenService::validate() resolves three model types. Every consumer that
 * type-hints the result must accept all three, and must not apply one model's
 * id to another model's lookup. A Digital Workshop token previously raised a
 * TypeError on the attendance page because the property type was not widened.
 */
beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
    Country::factory()->indonesia()->create(['id' => 1]);

    $this->staff = User::factory()->create();
    $this->staff->assignRole('Super Admin');
    $this->actingAs($this->staff);
});

function digitalWorkshopRegistration(string $token): DigitalWorkshopRegistration
{
    $workshop = DigitalWorkshop::factory()->create(['status' => 'published']);

    return DigitalWorkshopRegistration::create([
        'digital_workshop_id' => $workshop->id,
        'registration_code' => 'JADE-DW-2026-'.substr(md5($token), 0, 6),
        'registration_type' => 'standalone',
        'amount' => 1199000,
        'payment_status' => 'verified',
        'email' => 'dw-'.substr(md5($token), 0, 6).'@example.com',
        'phone' => '081234567890',
        'name_license' => 'Dr. Digital Workshop',
        'country_id' => 1,
        'language' => 'id',
        'qr_token' => $token,
        'qr_expires_at' => now()->addDay(),
    ]);
}

test('a digital workshop token renders the attendance verify page', function () {
    $token = str_repeat('d', 64);
    digitalWorkshopRegistration($token);

    livewire(AttendanceVerify::class, ['token' => $token])
        ->assertOk()
        ->assertSet('isValid', true);
});

test('a digital workshop token renders the attendance qr code page', function () {
    $token = str_repeat('e', 64);
    digitalWorkshopRegistration($token);

    livewire(AttendanceQrCode::class, ['token' => $token])
        ->assertOk();
});

test('a digital workshop token resolves to a digital workshop registration', function () {
    $token = str_repeat('f', 64);
    $dw = digitalWorkshopRegistration($token);

    $component = livewire(AttendanceVerify::class, ['token' => $token]);

    expect($component->get('registration'))->toBeInstanceOf(DigitalWorkshopRegistration::class);
    expect($component->get('registration')->id)->toBe($dw->id);
});

test('a digital workshop token does not pick up another participant attendance', function () {
    // The bug this guards: the DW id was used as a seminar_registration_id, so
    // a DW token could display a seminar attendee's check-in time.
    $seminar = SeminarRegistration::factory()->create([
        'country_id' => 1,
        'payment_status' => 'verified',
    ]);

    Attendance::create([
        'seminar_registration_id' => $seminar->id,
        'activity_type' => 'seminar',
        'checked_in_at' => now(),
        'checked_in_by' => $this->staff->id,
    ]);

    $token = str_repeat('g', 64);
    $dw = digitalWorkshopRegistration($token);

    // Force the collision the original bug depended on.
    if ($dw->id !== $seminar->id) {
        $seminar->update(['id' => $dw->id]);
    }

    $component = livewire(AttendanceVerify::class, ['token' => $token]);

    expect($component->get('seminarCheckedInAt'))->toBeNull();
    expect($component->get('handsOnCheckedIn'))->toBe([]);
});

test('a seminar token still works after the widening', function () {
    $seminar = SeminarRegistration::factory()->create([
        'country_id' => 1,
        'payment_status' => 'verified',
        'qr_token' => str_repeat('s', 64),
        'qr_expires_at' => now()->addDay(),
    ]);

    $component = livewire(AttendanceVerify::class, ['token' => str_repeat('s', 64)]);

    expect($component->get('registration'))->toBeInstanceOf(SeminarRegistration::class);
});

test('a hands-on token still works after the widening', function () {
    $handsOn = HandsOnRegistration::factory()->create([
        'country_id' => 1,
        'payment_status' => 'verified',
        'qr_token' => str_repeat('h', 64),
        'qr_expires_at' => now()->addDay(),
    ]);

    $component = livewire(AttendanceVerify::class, ['token' => str_repeat('h', 64)]);

    expect($component->get('registration'))->toBeInstanceOf(HandsOnRegistration::class);
});

test('checking in a digital workshop token is a no-op rather than an error', function () {
    // Check-in for the Digital Workshop is out of scope; the page must still
    // load and must not create an Attendance row against the wrong entity.
    $token = str_repeat('i', 64);
    digitalWorkshopRegistration($token);

    livewire(AttendanceVerify::class, ['token' => $token])
        ->call('checkInSeminar')
        ->assertOk();

    expect(Attendance::count())->toBe(0);
});
