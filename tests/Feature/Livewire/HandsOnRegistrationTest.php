<?php

use App\Enums\HandsOnStatus;
use App\Livewire\HandsOnRegistration;
use App\Livewire\HandsOnRegistrationSuccess;
use App\Models\Country;
use App\Models\HandsOn;
use App\Models\HandsOnRegistration as HandsOnRegistrationModel;
use App\Models\SeminarRegistration as SeminarRegistrationModel;
use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\assertDatabaseHas;
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

/**
 * @return array<string, mixed>
 */
function standalonePayload(HandsOn $handsOn, array $overrides = []): array
{
    return array_merge([
        'country_id' => 1,
        'name_license' => 'Dr. First',
        'email' => 'first@test.com',
        'phone' => '081111111111',
        'nik' => '1234567890123456',
        'pdgi_branch' => 'Jakarta',
        'kompetensi' => 'Dokter Gigi Umum',
        'selectedHandsOn' => [$handsOn->event_date->format('Y-m-d') => $handsOn->id],
        'payment_method' => 'bank_transfer',
        'payment_proof_uploaded' => true,
        'payment_proof_path' => 'payment-proofs/test.pdf',
    ], $overrides);
}

it('creates only a hands-on registration, never a phantom seminar registration', function () {
    $handsOn = HandsOn::first();

    livewire(HandsOnRegistration::class)
        ->set(standalonePayload($handsOn))
        ->call('submit')
        ->assertRedirect();

    assertDatabaseCount('hands_on_registrations', 1);
    assertDatabaseCount('seminar_registrations', 0);

    assertDatabaseHas('hands_on_registrations', [
        'hands_on_id' => $handsOn->id,
        'registration_type' => 'hands_on',
        'seminar_registration_id' => null,
        'email' => 'first@test.com',
        'payment_status' => 'pending',
    ]);

    $registration = HandsOnRegistrationModel::first();

    expect($registration->seminar_registration_id)->toBeNull()
        ->and($registration->registration_code)->toStartWith('JADE-HO-2026-');
});

it('prevents overselling the last seat with sequential submissions', function () {
    // NOTE: This test is sequential (PHPUnit single-threaded) so it doesn't
    // validate the lockForUpdate() concurrency mechanism directly.
    // However, it provides regression coverage: the first submission takes
    // the last seat, and the second correctly gets redirected with an error.
    // True concurrency testing would require separate DB connections.
    $handsOn = HandsOn::first();

    livewire(HandsOnRegistration::class)
        ->set(standalonePayload($handsOn))
        ->call('submit')
        ->assertRedirect();

    livewire(HandsOnRegistration::class)
        ->set(standalonePayload($handsOn, [
            'name_license' => 'Dr. Second',
            'email' => 'second@test.com',
            'phone' => '082222222222',
            'nik' => '6543210987654321',
            'pdgi_branch' => 'Bandung',
            'kompetensi' => 'Sp.KG',
            'payment_proof_path' => 'payment-proofs/test2.pdf',
        ]))
        ->call('submit');

    assertDatabaseCount('seminar_registrations', 0);
    assertDatabaseCount('hands_on_registrations', 1);
})->group('hands-on', 'registration');

it('rejects a second registration for the same session and email', function () {
    $handsOn = HandsOn::create([
        'name' => 'Second Hands On',
        'ho_code' => 'HO-002',
        'doctor_name' => 'Dr. Second',
        'description' => 'Test description',
        'event_date' => '2026-11-14',
        'max_seats' => 10,
        'price' => 500000,
        'original_price' => 500000,
        'currency' => 'IDR',
        'is_active' => true,
        'status' => HandsOnStatus::PUBLISHED,
    ]);

    livewire(HandsOnRegistration::class)
        ->set(standalonePayload($handsOn))
        ->call('submit')
        ->assertRedirect();

    livewire(HandsOnRegistration::class)
        ->set(standalonePayload($handsOn))
        ->call('submit')
        ->assertHasErrors('email');

    assertDatabaseCount('hands_on_registrations', 1);
});

it('renders the success page for a standalone hands-on registration', function () {
    $handsOn = HandsOn::first();

    livewire(HandsOnRegistration::class)
        ->set(standalonePayload($handsOn))
        ->call('submit')
        ->assertRedirect();

    $registration = HandsOnRegistrationModel::first();

    livewire(HandsOnRegistrationSuccess::class, ['id' => $registration->id])
        ->assertOk()
        ->assertSee($registration->registration_code)
        ->assertSee($handsOn->name);
});

it('resolves a combined hands-on success page by type, not id collision', function () {
    $handsOn = HandsOn::first();

    // Hands-on ids and seminar ids are independent sequences, so a seminar
    // registration can share the id of an unrelated hands-on registration.
    $seminarRegistration = SeminarRegistrationModel::create([
        'country_id' => 1,
        'name_license' => 'Dr. Combined',
        'email' => 'combined@test.com',
        'phone' => '081234567890',
        'nik' => '1234567890123456',
        'pdgi_branch' => 'Jakarta',
        'kompetensi' => 'Dokter Gigi Umum',
        'registration_code' => SeminarRegistrationModel::generateRegistrationCode(),
        'language' => 'id',
        'registration_type' => 'online',
        'wants_hands_on' => true,
        'wants_poster_competition' => false,
        'amount' => 0,
        'currency' => 'IDR',
        'payment_status' => 'verified',
        'hands_on_total_amount' => 500000,
    ]);

    HandsOnRegistrationModel::create([
        'registration_code' => 'JADE-HO-2026-900001',
        'seminar_registration_id' => $seminarRegistration->id,
        'hands_on_id' => $handsOn->id,
        'registration_type' => 'combined',
        'payment_status' => 'pending',
        'email' => $seminarRegistration->email,
    ]);

    livewire(HandsOnRegistrationSuccess::class, [
        'id' => $seminarRegistration->id,
        'type' => 'seminar',
    ])
        ->assertOk()
        ->assertSee($seminarRegistration->registration_code)
        ->assertSee('500.000');
});

it('lets a standalone hands-on registrant add another session', function () {
    $firstSession = HandsOn::first();

    livewire(HandsOnRegistration::class)
        ->set(standalonePayload($firstSession))
        ->call('submit')
        ->assertRedirect();

    // Payment must be verified before extra sessions can be added.
    HandsOnRegistrationModel::query()->update(['payment_status' => 'verified']);

    $secondSession = HandsOn::create([
        'name' => 'Third Hands On',
        'ho_code' => 'HO-003',
        'doctor_name' => 'Dr. Third',
        'description' => 'Test description',
        'event_date' => '2026-11-15',
        'max_seats' => 10,
        'price' => 500000,
        'original_price' => 500000,
        'currency' => 'IDR',
        'is_active' => true,
        'status' => HandsOnStatus::PUBLISHED,
    ]);

    livewire(HandsOnRegistration::class)
        ->set('verification_email', 'first@test.com')
        ->call('checkExistingRegistration')
        ->assertSet('showVerificationError', false)
        ->assertSet('existingRegistration', null)
        ->assertSet('alreadyRegisteredHandsOnIds', [$firstSession->id]);

    livewire(HandsOnRegistration::class)
        ->set('verification_email', 'first@test.com')
        ->call('checkExistingRegistration')
        ->set('selectedHandsOn', [
            $firstSession->event_date->format('Y-m-d') => $firstSession->id,
            $secondSession->event_date->format('Y-m-d') => $secondSession->id,
        ])
        ->set('payment_proof_uploaded', true)
        ->set('payment_proof_path', 'payment-proofs/extra.pdf')
        ->call('submit')
        ->assertRedirect();

    assertDatabaseCount('seminar_registrations', 0);
    assertDatabaseCount('hands_on_registrations', 2);

    assertDatabaseHas('hands_on_registrations', [
        'hands_on_id' => $secondSession->id,
        'email' => 'first@test.com',
        'seminar_registration_id' => null,
        'registration_type' => 'hands_on',
    ]);
});
