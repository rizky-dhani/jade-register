<?php

use App\Enums\HandsOnStatus;
use App\Livewire\HandsOnRegistration;
use App\Models\Country;
use App\Models\HandsOn;
use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\assertDatabaseCount;
use function Pest\Livewire\livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    Country::create([
        'id' => 1,
        'name' => 'Indonesia',
        'code' => 'ID',
        'is_indonesia' => true,
        'phone_code' => '62',
    ]);

    Setting::create([
        'key' => 'hands_on_registration_open',
        'value' => true,
        'type' => 'boolean',
    ]);

    HandsOn::create([
        'name' => 'Test Hands On',
        'ho_code' => 'HO-001',
        'doctor_name' => 'Dr. Test',
        'description' => 'Test description',
        'event_date' => '2026-11-13',
        'max_seats' => 1,
        'price' => 500000,
        'original_price' => 500000,
        'currency' => 'IDR',
        'is_active' => true,
        'status' => HandsOnStatus::PUBLISHED,
    ]);
});

it('prevents overselling the last seat with sequential submissions', function () {
    // NOTE: This test is sequential (PHPUnit single-threaded) so it doesn't
    // validate the lockForUpdate() concurrency mechanism directly.
    // However, it provides regression coverage: the first submission takes
    // the last seat, and the second correctly gets redirected with an error.
    // True concurrency testing would require separate DB connections.
    $handsOn = HandsOn::first();

    $component1 = livewire(HandsOnRegistration::class);
    $component2 = livewire(HandsOnRegistration::class);

    $component1
        ->set('country_id', 1)
        ->set('name_license', 'Dr. First')
        ->set('email', 'first@test.com')
        ->set('phone', '081111111111')
        ->set('nik', '1234567890123456')
        ->set('pdgi_branch', 'Jakarta')
        ->set('kompetensi', 'Dokter Gigi Umum')
        ->set('selectedHandsOn', ['2026-11-13' => $handsOn->id])
        ->set('payment_method', 'bank_transfer')
        ->set('payment_proof_uploaded', true)
        ->set('payment_proof_path', 'payment-proofs/test.pdf')
        ->call('submit')
        ->assertRedirect();

    $component2
        ->set('country_id', 1)
        ->set('name_license', 'Dr. Second')
        ->set('email', 'second@test.com')
        ->set('phone', '082222222222')
        ->set('nik', '6543210987654321')
        ->set('pdgi_branch', 'Bandung')
        ->set('kompetensi', 'Sp.KG')
        ->set('selectedHandsOn', ['2026-11-13' => $handsOn->id])
        ->set('payment_method', 'bank_transfer')
        ->set('payment_proof_uploaded', true)
        ->set('payment_proof_path', 'payment-proofs/test2.pdf')
        ->call('submit');

    assertDatabaseCount('seminar_registrations', 1);
    assertDatabaseCount('hands_on_registrations', 1);
})->group('hands-on', 'registration');
