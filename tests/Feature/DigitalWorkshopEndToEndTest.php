<?php

use App\Jobs\CompleteDigitalWorkshopRegistration;
use App\Livewire\DigitalWorkshopRegistration as DigitalWorkshopRegistrationForm;
use App\Mail\DigitalWorkshopRegistrationConfirmation;
use App\Models\Country;
use App\Models\DigitalWorkshop;
use App\Models\DigitalWorkshopRegistration;
use App\Models\SeminarRegistration;
use App\Services\QrTokenService;
use App\Services\RegistrationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

use function Pest\Livewire\livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    Storage::fake('public');
    Mail::fake();

    Country::create([
        'id' => 1,
        'name' => 'Indonesia',
        'code' => 'ID',
        'is_indonesia' => true,
        'phone_code' => '62',
    ]);

    $this->workshop = DigitalWorkshop::factory()->create([
        'status' => 'published',
        'price' => 1199000,
        'bundle_price' => 999000,
        'max_seats' => null,
    ]);

    $this->payload = fn (array $overrides = []): array => array_merge([
        'is_local' => true,
        'email' => 'endtoend@example.com',
        'phone' => '081234567890',
        'name_license' => 'Dr. End To End',
        'nik' => '1234567890123456',
        'pdgi_branch' => 'Jakarta',
        'kompetensi' => 'Dokter Gigi Umum',
        'country_id' => 1,
        'payment_method' => 'bank_transfer',
        'payment_proof' => UploadedFile::fake()->image('proof.jpg'),
    ], $overrides);
});

test('registers standalone, generates a qr token, and emails a confirmation', function () {
    livewire(DigitalWorkshopRegistrationForm::class)
        ->set(($this->payload)())
        ->call('submit')
        ->assertHasNoErrors();

    $registration = DigitalWorkshopRegistration::first();

    expect($registration)->not->toBeNull();
    expect($registration->amount)->toBe(1199000);
    expect($registration->payment_status)->toBe('pending');
    expect($registration->registration_code)->toStartWith('JADE-DW-2026-');

    // Run the queued completion job synchronously to prove the full path.
    (new CompleteDigitalWorkshopRegistration($registration))
        ->handle(app(QrTokenService::class), app(RegistrationService::class));

    $registration->refresh();

    expect($registration->qr_token)->not->toBeNull();
    expect($registration->confirmation_email_sent_at)->not->toBeNull();
    Mail::assertSent(DigitalWorkshopRegistrationConfirmation::class);
});

test('registers bundled against a verified seminar registration without touching seminar_registrations', function () {
    $seminar = SeminarRegistration::factory()->create([
        'email' => 'endtoend@example.com',
        'payment_status' => 'verified',
    ]);
    $seminarCountBefore = SeminarRegistration::count();

    livewire(DigitalWorkshopRegistrationForm::class)
        ->set(($this->payload)())
        ->call('submit')
        ->assertHasNoErrors();

    $registration = DigitalWorkshopRegistration::first();

    expect($registration->amount)->toBe(999000);
    expect($registration->registration_type)->toBe('bundled');
    expect($registration->seminar_registration_id)->toBe($seminar->id);
    expect(SeminarRegistration::count())->toBe($seminarCountBefore);
});

test('leaves the seminar registration amount untouched when bundling', function () {
    $seminar = SeminarRegistration::factory()->create([
        'email' => 'endtoend@example.com',
        'payment_status' => 'verified',
        'amount' => 1234567,
    ]);
    $amountBefore = $seminar->amount;
    $verifiedBefore = $seminar->verified_at;

    livewire(DigitalWorkshopRegistrationForm::class)
        ->set(($this->payload)())
        ->call('submit')
        ->assertHasNoErrors();

    $seminar->refresh();

    expect($seminar->amount)->toBe($amountBefore);
    expect($seminar->verified_at?->toDateTimeString())->toBe($verifiedBefore?->toDateTimeString());
});

test('a standalone submission leaves the seminar table completely empty', function () {
    livewire(DigitalWorkshopRegistrationForm::class)
        ->set(($this->payload)())
        ->call('submit')
        ->assertHasNoErrors();

    expect(SeminarRegistration::count())->toBe(0);
});

test('the confirmation email recipient is the digital workshop registration own email', function () {
    livewire(DigitalWorkshopRegistrationForm::class)
        ->set(($this->payload)(['email' => 'own@example.com']))
        ->call('submit')
        ->assertHasNoErrors();

    $registration = DigitalWorkshopRegistration::first();

    (new CompleteDigitalWorkshopRegistration($registration))
        ->handle(app(QrTokenService::class), app(RegistrationService::class));

    Mail::assertSent(
        DigitalWorkshopRegistrationConfirmation::class,
        fn ($mail) => $mail->hasTo('own@example.com'),
    );
});
