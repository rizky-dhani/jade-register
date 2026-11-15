<?php

use App\Models\HandsOnRegistration;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('verifies a created registration that carries a payment proof', function () {
    $registration = HandsOnRegistration::factory()->create([
        'payment_status' => 'pending',
        'payment_proof_path' => 'payment-proofs/proof.jpg',
    ]);

    expect($registration->fresh())
        ->payment_status->toBe('verified')
        ->verified_at->not->toBeNull();
});

test('verifies a registration when a payment proof is attached later', function () {
    $registration = HandsOnRegistration::factory()->create([
        'payment_status' => 'pending',
        'payment_proof_path' => null,
    ]);

    expect($registration->fresh()->payment_status)->toBe('pending')
        ->and($registration->fresh()->verified_at)->toBeNull();

    $registration->update(['payment_proof_path' => 'payment-proofs/proof.jpg']);

    expect($registration->fresh())
        ->payment_status->toBe('verified')
        ->verified_at->not->toBeNull();
});

test('leaves a registration without a payment proof untouched', function () {
    $registration = HandsOnRegistration::factory()->create([
        'payment_status' => 'pending',
        'payment_proof_path' => null,
    ]);

    expect($registration->fresh())
        ->payment_status->toBe('pending')
        ->verified_at->toBeNull();
});

test('keeps the original verified_at when the registration is saved again', function () {
    $verifiedAt = now()->subDay();

    $registration = HandsOnRegistration::factory()->create([
        'payment_status' => 'verified',
        'payment_proof_path' => 'payment-proofs/proof.jpg',
        'verified_at' => $verifiedAt,
    ]);

    $registration->update(['name_license' => 'drg. Updated']);

    expect($registration->fresh()->verified_at->timestamp)->toBe($verifiedAt->timestamp);
});

test('keeps a rejected payment rejected even though a proof is attached', function () {
    $registration = HandsOnRegistration::factory()->create([
        'payment_status' => 'rejected',
        'payment_proof_path' => 'payment-proofs/proof.jpg',
    ]);

    expect($registration->fresh()->payment_status)->toBe('rejected');
});
