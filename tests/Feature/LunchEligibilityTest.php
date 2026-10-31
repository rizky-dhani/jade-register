<?php

use App\Models\Addon;
use App\Models\AddonRegistration;
use App\Models\Seminar;
use App\Models\SeminarRegistration;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function makeSeminarForLunchEligibility(bool $includesLunch): Seminar
{
    return Seminar::create([
        'name' => 'Eligibility Seminar',
        'code' => 'eligibility-'.uniqid(),
        'applies_to' => 'all',
        'currency' => 'IDR',
        'includes_lunch' => $includesLunch,
    ]);
}

function makeAddonForLunchEligibility(string $code): Addon
{
    return Addon::create([
        'name' => 'Eligibility Add-on',
        'code' => $code,
        'price' => 5000,
        'currency' => 'IDR',
        'is_active' => true,
    ]);
}

test('lunch package grants lunch eligibility', function () {
    $registration = SeminarRegistration::factory()->create([
        'seminar_id' => makeSeminarForLunchEligibility(true)->id,
    ]);

    expect($registration->isEligibleForLunch())->toBeTrue();
});

test('package without lunch and without lunch add-on is ineligible', function () {
    $registration = SeminarRegistration::factory()->create([
        'seminar_id' => makeSeminarForLunchEligibility(false)->id,
    ]);

    expect($registration->isEligibleForLunch())->toBeFalse();
});

test('lunch add-on grants eligibility when package excludes lunch', function () {
    $registration = SeminarRegistration::factory()->create([
        'seminar_id' => makeSeminarForLunchEligibility(false)->id,
    ]);
    $addon = makeAddonForLunchEligibility('LUNCH_ADDON');

    AddonRegistration::create([
        'seminar_registration_id' => $registration->id,
        'addon_id' => $addon->id,
        'amount' => $addon->price,
        'currency' => $addon->currency,
        'payment_proof_path' => 'proof.jpg',
        'payment_status' => 'pending',
    ]);

    expect($registration->isEligibleForLunch())->toBeTrue();
});

test('unrelated add-on does not grant lunch eligibility', function () {
    $registration = SeminarRegistration::factory()->create([
        'seminar_id' => makeSeminarForLunchEligibility(false)->id,
    ]);
    $addon = makeAddonForLunchEligibility('OTHER_ADDON');

    AddonRegistration::create([
        'seminar_registration_id' => $registration->id,
        'addon_id' => $addon->id,
        'amount' => $addon->price,
        'currency' => $addon->currency,
        'payment_proof_path' => 'proof.jpg',
        'payment_status' => 'verified',
    ]);

    expect($registration->isEligibleForLunch())->toBeFalse();
});
