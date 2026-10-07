<?php

use App\Jobs\FulfillDigitalWorkshopIntent;
use App\Models\DigitalWorkshopRegistrationIntent;
use App\Models\HandsOnRegistration;
use App\Models\SeminarRegistration;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;

uses(RefreshDatabase::class);

/**
 * Characterization coverage for SeminarRegistrationObserver's EXISTING behavior:
 * verifying a seminar payment auto-verifies its pending hands-on registrations.
 *
 * This behavior already ships and had no tests. These must pass against the
 * current observer before it is extended for the Digital Workshop bundle.
 */
test('auto-verifies pending hands-on registrations when a seminar payment is verified', function () {
    $seminar = SeminarRegistration::factory()->create(['payment_status' => 'pending']);
    $handsOn = HandsOnRegistration::factory()->create([
        'seminar_registration_id' => $seminar->id,
        'payment_status' => 'pending',
        'verified_at' => null,
    ]);

    $seminar->update(['payment_status' => 'verified']);

    $handsOn->refresh();

    expect($handsOn->payment_status)->toBe('verified');
    expect($handsOn->verified_at)->not->toBeNull();
});

test('leaves hands-on registrations alone when the seminar is not verified', function () {
    $seminar = SeminarRegistration::factory()->create(['payment_status' => 'pending']);
    $handsOn = HandsOnRegistration::factory()->create([
        'seminar_registration_id' => $seminar->id,
        'payment_status' => 'pending',
    ]);

    $seminar->update(['payment_status' => 'rejected']);

    expect($handsOn->refresh()->payment_status)->toBe('pending');
});

test('does not re-run when payment_status was already verified', function () {
    $seminar = SeminarRegistration::factory()->create(['payment_status' => 'verified']);
    $handsOn = HandsOnRegistration::factory()->create([
        'seminar_registration_id' => $seminar->id,
        'payment_status' => 'pending',
        'verified_at' => null,
    ]);

    // Already verified -> wasChanged('payment_status') is false, so no cascade.
    $seminar->update(['name_license' => 'Somebody Else']);

    expect($handsOn->refresh()->payment_status)->toBe('pending');
    expect($handsOn->verified_at)->toBeNull();
});

test('ignores unrelated field updates', function () {
    $seminar = SeminarRegistration::factory()->create(['payment_status' => 'pending']);
    $handsOn = HandsOnRegistration::factory()->create([
        'seminar_registration_id' => $seminar->id,
        'payment_status' => 'pending',
    ]);

    $seminar->update(['name_license' => 'Renamed Person']);

    expect($handsOn->refresh()->payment_status)->toBe('pending');
});

test('leaves already-verified hands-on registrations untouched when the seminar verifies', function () {
    $seminar = SeminarRegistration::factory()->create(['payment_status' => 'pending']);
    $handsOn = HandsOnRegistration::factory()->create([
        'seminar_registration_id' => $seminar->id,
        'payment_status' => 'verified',
        'verified_at' => now()->subDay(),
    ]);
    $originalVerifiedAt = $handsOn->verified_at;

    $seminar->update(['payment_status' => 'verified']);

    $handsOn->refresh();

    expect($handsOn->payment_status)->toBe('verified');
    // The query only touches pending rows, so an already-verified row keeps its
    // original timestamp rather than being rewritten.
    expect($handsOn->verified_at->toDateTimeString())->toBe($originalVerifiedAt->toDateTimeString());
});

test('does not sign in or otherwise require an authenticated user', function () {
    // The observer runs inside an admin request or an artisan command. It must
    // not assume an actor.
    $seminar = SeminarRegistration::factory()->create(['payment_status' => 'pending']);
    $handsOn = HandsOnRegistration::factory()->create([
        'seminar_registration_id' => $seminar->id,
        'payment_status' => 'pending',
    ]);

    expect(auth()->check())->toBeFalse();

    $seminar->update(['payment_status' => 'verified']);

    expect($handsOn->refresh()->payment_status)->toBe('verified');
});

/*
|--------------------------------------------------------------------------
| Digital Workshop bundle intents (added with the bundle handoff)
|--------------------------------------------------------------------------
*/

test('dispatches fulfilment when a seminar payment verifies with an awaiting intent', function () {
    Queue::fake();

    $seminar = SeminarRegistration::factory()->create([
        'payment_status' => 'pending',
        'email' => 'bundle@example.com',
    ]);

    $intent = DigitalWorkshopRegistrationIntent::factory()->create([
        'email' => 'BUNDLE@example.com', // different case on purpose
        'seminar_registration_id' => null,
    ]);

    $seminar->update(['payment_status' => 'verified']);

    Queue::assertPushed(FulfillDigitalWorkshopIntent::class);
    expect($intent->refresh()->seminar_registration_id)->toBe($seminar->id);
});

test('does not dispatch fulfilment when the payment is rejected', function () {
    // Review Focus 6: a rejected payment leaves the intent resolvable.
    Queue::fake();

    $seminar = SeminarRegistration::factory()->create([
        'payment_status' => 'pending',
        'email' => 'bundle@example.com',
    ]);

    $intent = DigitalWorkshopRegistrationIntent::factory()->create([
        'email' => 'bundle@example.com',
        'seminar_registration_id' => null,
    ]);

    $seminar->update(['payment_status' => 'rejected']);

    Queue::assertNotPushed(FulfillDigitalWorkshopIntent::class);
    expect($intent->refresh()->isAwaiting())->toBeTrue();
});

test('leaves intents for other emails alone', function () {
    Queue::fake();

    $seminar = SeminarRegistration::factory()->create([
        'payment_status' => 'pending',
        'email' => 'mine@example.com',
    ]);

    $theirs = DigitalWorkshopRegistrationIntent::factory()->create([
        'email' => 'theirs@example.com',
        'seminar_registration_id' => null,
    ]);

    $seminar->update(['payment_status' => 'verified']);

    expect($theirs->refresh()->seminar_registration_id)->toBeNull();
    expect($theirs->refresh()->isAwaiting())->toBeTrue();
});

test('does not re-dispatch for an already fulfilled intent', function () {
    Queue::fake();

    $seminar = SeminarRegistration::factory()->create([
        'payment_status' => 'pending',
        'email' => 'bundle@example.com',
    ]);

    DigitalWorkshopRegistrationIntent::factory()->create([
        'email' => 'bundle@example.com',
        'status' => 'fulfilled',
        'fulfilled_at' => now(),
    ]);

    $seminar->update(['payment_status' => 'verified']);

    Queue::assertNotPushed(FulfillDigitalWorkshopIntent::class);
});

test('does not write to seminar_registrations when dispatching an intent', function () {
    Queue::fake();

    $seminar = SeminarRegistration::factory()->create([
        'payment_status' => 'pending',
        'email' => 'bundle@example.com',
        'amount' => 777777,
    ]);
    $amountBefore = $seminar->amount;
    $countBefore = SeminarRegistration::count();

    DigitalWorkshopRegistrationIntent::factory()->create([
        'email' => 'bundle@example.com',
        'seminar_registration_id' => null,
    ]);

    $seminar->update(['payment_status' => 'verified']);

    expect(SeminarRegistration::count())->toBe($countBefore);
    expect($seminar->refresh()->amount)->toBe($amountBefore);
});
