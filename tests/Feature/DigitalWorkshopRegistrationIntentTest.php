<?php

use App\Models\DigitalWorkshop;
use App\Models\DigitalWorkshopRegistrationIntent;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('defaults to awaiting status', function () {
    $intent = DigitalWorkshopRegistrationIntent::factory()->create();

    expect($intent->status)->toBe('awaiting_seminar');
});

test('lowercases the email on write', function () {
    $intent = DigitalWorkshopRegistrationIntent::factory()->create([
        'email' => 'Budi@Example.com',
    ]);

    expect($intent->email)->toBe('budi@example.com');
    expect($intent->fresh()->email)->toBe('budi@example.com');
});

test('reports awaiting, fulfilled and expired correctly', function () {
    $awaiting = DigitalWorkshopRegistrationIntent::factory()->create();

    expect($awaiting->isAwaiting())->toBeTrue();
    expect($awaiting->isFulfilled())->toBeFalse();
    expect($awaiting->isExpired())->toBeFalse();

    $fulfilled = DigitalWorkshopRegistrationIntent::factory()->create([
        'status' => 'fulfilled',
        'fulfilled_at' => now(),
    ]);

    expect($fulfilled->isFulfilled())->toBeTrue();
    expect($fulfilled->isAwaiting())->toBeFalse();

    $expired = DigitalWorkshopRegistrationIntent::factory()->create([
        'status' => 'expired',
        'expired_at' => now(),
    ]);

    expect($expired->isExpired())->toBeTrue();
    expect($expired->isAwaiting())->toBeFalse();
});

test('scopes to awaiting intents for a lowercased email', function () {
    $match = DigitalWorkshopRegistrationIntent::factory()->create([
        'email' => 'Match@Example.com',
    ]);

    $otherEmail = DigitalWorkshopRegistrationIntent::factory()->create(['email' => 'other@example.com']);
    $alreadyFulfilled = DigitalWorkshopRegistrationIntent::factory()->create([
        'email' => 'match@example.com',
        'status' => 'fulfilled',
        'fulfilled_at' => now(),
    ]);

    $found = DigitalWorkshopRegistrationIntent::awaitingForEmail('MATCH@example.com')->pluck('id');

    expect($found)->toContain($match->id);
    expect($found)->not->toContain($otherEmail->id);
    expect($found)->not->toContain($alreadyFulfilled->id);
});

test('resolves the participant name from name_license, then name', function () {
    $local = DigitalWorkshopRegistrationIntent::factory()->create([
        'name_license' => 'Dr. Local Name',
        'name' => null,
    ]);

    expect($local->participantName())->toBe('Dr. Local Name');

    $international = DigitalWorkshopRegistrationIntent::factory()->create([
        'name_license' => null,
        'name' => 'International Name',
    ]);

    expect($international->participantName())->toBe('International Name');

    // name_license wins when both are present.
    $both = DigitalWorkshopRegistrationIntent::factory()->create([
        'name_license' => 'Preferred',
        'name' => 'Fallback',
    ]);

    expect($both->participantName())->toBe('Preferred');
});

test('resolves its workshop relation', function () {
    $workshop = DigitalWorkshop::factory()->create();
    $intent = DigitalWorkshopRegistrationIntent::factory()->create([
        'digital_workshop_id' => $workshop->id,
    ]);

    expect($intent->digitalWorkshop->id)->toBe($workshop->id);
});

test('does not require a seminar registration to exist yet', function () {
    $intent = DigitalWorkshopRegistrationIntent::factory()->create();

    expect($intent->seminar_registration_id)->toBeNull();
    expect($intent->digital_workshop_registration_id)->toBeNull();
    expect($intent->fulfilled_at)->toBeNull();
});
