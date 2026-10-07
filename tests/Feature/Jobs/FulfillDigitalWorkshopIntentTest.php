<?php

use App\Jobs\CompleteDigitalWorkshopRegistration;
use App\Jobs\FulfillDigitalWorkshopIntent;
use App\Models\Country;
use App\Models\DigitalWorkshop;
use App\Models\DigitalWorkshopRegistration;
use App\Models\DigitalWorkshopRegistrationIntent;
use App\Models\HandsOnRegistration;
use App\Models\SeminarRegistration;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;

uses(RefreshDatabase::class);

beforeEach(function () {
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
});

function intentFor(SeminarRegistration $seminar, DigitalWorkshop $workshop, array $overrides = []): DigitalWorkshopRegistrationIntent
{
    return DigitalWorkshopRegistrationIntent::factory()->create(array_merge([
        'digital_workshop_id' => $workshop->id,
        'email' => $seminar->email,
        'seminar_registration_id' => $seminar->id,
        'name_license' => 'Dr. Bundle',
        'nik' => '1234567890123456',
        'pdgi_branch' => 'Jakarta',
        'kompetensi' => 'Dokter Gigi Umum',
        'phone' => '081234567890',
        'country_id' => 1,
        'payment_proof_path' => 'payment-proofs/intent-proof.jpg',
    ], $overrides));
}

test('creates a bundled digital workshop registration when the intent is fulfilled', function () {
    Queue::fake();
    $seminar = SeminarRegistration::factory()->create(['payment_status' => 'verified', 'email' => 'bundle@example.com', 'country_id' => 1]);
    $intent = intentFor($seminar, $this->workshop);

    (new FulfillDigitalWorkshopIntent($intent))->handle();

    $registration = DigitalWorkshopRegistration::first();

    expect($registration)->not->toBeNull();
    expect($registration->amount)->toBe(999000);
    expect($registration->registration_type)->toBe('bundled');
    expect($registration->seminar_registration_id)->toBe($seminar->id);
    expect($registration->payment_status)->toBe('pending');
    expect($registration->email)->toBe('bundle@example.com');
    expect($registration->payment_proof_path)->toBe('payment-proofs/intent-proof.jpg');
});

test('marks the intent fulfilled and links the registration', function () {
    Queue::fake();
    $seminar = SeminarRegistration::factory()->create(['payment_status' => 'verified', 'email' => 'bundle@example.com', 'country_id' => 1]);
    $intent = intentFor($seminar, $this->workshop);

    (new FulfillDigitalWorkshopIntent($intent))->handle();

    $intent->refresh();

    expect($intent->isFulfilled())->toBeTrue();
    expect($intent->fulfilled_at)->not->toBeNull();
    expect($intent->digital_workshop_registration_id)->toBe(DigitalWorkshopRegistration::first()->id);
});

test('dispatches the completion job for the new registration', function () {
    Queue::fake();
    $seminar = SeminarRegistration::factory()->create(['payment_status' => 'verified', 'email' => 'bundle@example.com', 'country_id' => 1]);
    $intent = intentFor($seminar, $this->workshop);

    (new FulfillDigitalWorkshopIntent($intent))->handle();

    Queue::assertPushed(CompleteDigitalWorkshopRegistration::class);
});

test('does not fulfil twice for the same intent', function () {
    // Review Focus 5: a re-verification or two concurrent dispatches must still
    // produce exactly one registration.
    Queue::fake();
    $seminar = SeminarRegistration::factory()->create(['payment_status' => 'verified', 'email' => 'bundle@example.com', 'country_id' => 1]);
    $intent = intentFor($seminar, $this->workshop);

    (new FulfillDigitalWorkshopIntent($intent))->handle();
    (new FulfillDigitalWorkshopIntent($intent->fresh()))->handle();
    (new FulfillDigitalWorkshopIntent($intent->fresh()))->handle();

    expect(DigitalWorkshopRegistration::count())->toBe(1);
});

test('does not fulfil when the intent has no seminar registration', function () {
    Queue::fake();
    $intent = DigitalWorkshopRegistrationIntent::factory()->create([
        'digital_workshop_id' => $this->workshop->id,
        'seminar_registration_id' => null,
    ]);

    (new FulfillDigitalWorkshopIntent($intent))->handle();

    expect(DigitalWorkshopRegistration::count())->toBe(0);
    expect($intent->refresh()->isAwaiting())->toBeTrue();
});

test('returns early when the intent has been deleted', function () {
    Queue::fake();
    $seminar = SeminarRegistration::factory()->create(['payment_status' => 'verified', 'email' => 'bundle@example.com', 'country_id' => 1]);
    $intent = intentFor($seminar, $this->workshop);

    // Simulate a queued job whose intent was removed before it ran: the job
    // holds a model instance whose row no longer exists.
    $intent->delete();

    (new FulfillDigitalWorkshopIntent($intent))->handle();

    expect(DigitalWorkshopRegistration::count())->toBe(0);
});

test('does not create or modify a seminar registration', function () {
    // The hard constraint of this plan: Digital Workshop code never writes to
    // seminar_registrations.
    Queue::fake();
    $seminar = SeminarRegistration::factory()->create([
        'payment_status' => 'verified',
        'email' => 'bundle@example.com',
        'amount' => 654321,
        'country_id' => 1,
    ]);
    $amountBefore = $seminar->amount;
    $countBefore = SeminarRegistration::count();
    $intent = intentFor($seminar, $this->workshop);

    (new FulfillDigitalWorkshopIntent($intent))->handle();

    $seminar->refresh();

    expect(SeminarRegistration::count())->toBe($countBefore);
    expect($seminar->amount)->toBe($amountBefore);
    expect($seminar->payment_status)->toBe('verified');
});

test('fulfils only the matching intent when several are awaiting', function () {
    Queue::fake();
    $seminar = SeminarRegistration::factory()->create(['payment_status' => 'verified', 'email' => 'mine@example.com', 'country_id' => 1]);
    $mine = intentFor($seminar, $this->workshop);

    $theirs = DigitalWorkshopRegistrationIntent::factory()->create([
        'digital_workshop_id' => $this->workshop->id,
        'email' => 'theirs@example.com',
    ]);

    (new FulfillDigitalWorkshopIntent($mine))->handle();

    expect($theirs->refresh()->isAwaiting())->toBeTrue();
    expect($theirs->digital_workshop_registration_id)->toBeNull();
});

test('carries the bundled price even when the buyer also has a verified seminar', function () {
    Queue::fake();
    $seminar = SeminarRegistration::factory()->create(['payment_status' => 'verified', 'email' => 'bundle@example.com', 'country_id' => 1]);
    $intent = intentFor($seminar, $this->workshop);

    (new FulfillDigitalWorkshopIntent($intent))->handle();

    expect(DigitalWorkshopRegistration::first()->amount)->toBe(999000);
});

test('falls back to the standalone price when the workshop has no bundle price', function () {
    // A cleared bundle_price must not silently create the registration at zero.
    Queue::fake();
    $this->workshop->update(['bundle_price' => null]);

    $seminar = SeminarRegistration::factory()->create(['payment_status' => 'verified', 'email' => 'bundle@example.com', 'country_id' => 1]);
    $intent = intentFor($seminar, $this->workshop);

    (new FulfillDigitalWorkshopIntent($intent))->handle();

    expect(DigitalWorkshopRegistration::first()->amount)->toBe(1199000);
});

test('the job is queued with retries and backoff', function () {
    $seminar = SeminarRegistration::factory()->create(['payment_status' => 'verified', 'email' => 'bundle@example.com', 'country_id' => 1]);
    $intent = intentFor($seminar, $this->workshop);

    $job = new FulfillDigitalWorkshopIntent($intent);

    expect($job->tries)->toBe(3);
    expect($job->backoff)->toBe([10, 60]);
});

test('does not touch hands-on registrations', function () {
    Queue::fake();
    $seminar = SeminarRegistration::factory()->create(['payment_status' => 'verified', 'email' => 'bundle@example.com', 'country_id' => 1]);
    $handsOn = HandsOnRegistration::factory()->create([
        'seminar_registration_id' => $seminar->id,
        'payment_status' => 'pending',
    ]);
    $intent = intentFor($seminar, $this->workshop);

    (new FulfillDigitalWorkshopIntent($intent))->handle();

    expect($handsOn->refresh()->payment_status)->toBe('pending');
});

test('an already-fulfilled intent is never fulfilled again, even if asked directly', function () {
    // Guards the explicit fulfilment guard rather than only relying on the
    // sequential isAwaiting() re-read. The concurrent race between two dispatch
    // workers is closed by the row lock in the job, which a single-process test
    // cannot reproduce; this proves the guard that ships alongside it.
    Queue::fake();
    $seminar = SeminarRegistration::factory()->create(['payment_status' => 'verified', 'email' => 'bundle@example.com', 'country_id' => 1]);
    $intent = intentFor($seminar, $this->workshop);

    $existing = DigitalWorkshopRegistration::factory()->create([
        'digital_workshop_id' => $this->workshop->id,
        'seminar_registration_id' => $seminar->id,
    ]);

    // The intent looks awaiting but already carries a link.
    $intent->update([
        'status' => 'fulfilled',
        'digital_workshop_registration_id' => $existing->id,
        'fulfilled_at' => now(),
    ]);

    (new FulfillDigitalWorkshopIntent($intent->fresh()))->handle();

    expect(DigitalWorkshopRegistration::count())->toBe(1);
    Queue::assertNotPushed(CompleteDigitalWorkshopRegistration::class);
});

test('a fulfilled intent whose link is present is skipped even when status was reset', function () {
    Queue::fake();
    $seminar = SeminarRegistration::factory()->create(['payment_status' => 'verified', 'email' => 'bundle@example.com', 'country_id' => 1]);
    $intent = intentFor($seminar, $this->workshop);

    $existing = DigitalWorkshopRegistration::factory()->create([
        'digital_workshop_id' => $this->workshop->id,
        'seminar_registration_id' => $seminar->id,
    ]);

    // Status says awaiting, but the link is set: the link is the stronger signal.
    $intent->update([
        'status' => 'awaiting_seminar',
        'digital_workshop_registration_id' => $existing->id,
    ]);

    (new FulfillDigitalWorkshopIntent($intent->fresh()))->handle();

    expect(DigitalWorkshopRegistration::count())->toBe(1);
});
