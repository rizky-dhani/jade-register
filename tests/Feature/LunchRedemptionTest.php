<?php

use App\Models\SeminarRegistration;
use App\Models\User;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;

uses(RefreshDatabase::class);

test('a seminar registration can have a single lunch redemption with an auditable staff snapshot', function () {
    $registration = SeminarRegistration::factory()->create();
    $staff = User::factory()->create(['name' => 'Lunch Desk Staff']);

    expect(Schema::hasTable('lunch_redemptions'))->toBeTrue();

    $registration->lunchRedemption()->create([
        'redeemed_at' => now(),
        'redeemed_by' => $staff->id,
        'redeemed_by_name' => $staff->name,
    ]);

    expect($registration->fresh()->lunchRedemption->redeemedBy->is($staff))->toBeTrue();

    $this->assertDatabaseHas('lunch_redemptions', [
        'seminar_registration_id' => $registration->id,
        'redeemed_by' => $staff->id,
        'redeemed_by_name' => 'Lunch Desk Staff',
    ]);

    expect(fn () => $registration->lunchRedemption()->create([
        'redeemed_at' => now(),
        'redeemed_by' => $staff->id,
        'redeemed_by_name' => $staff->name,
    ]))->toThrow(UniqueConstraintViolationException::class);
});

test('deleting redeeming staff preserves redemption and the name snapshot', function () {
    $registration = SeminarRegistration::factory()->create();
    $staff = User::factory()->create(['name' => 'Lunch Desk Staff']);

    $registration->lunchRedemption()->create([
        'redeemed_at' => now(),
        'redeemed_by' => $staff->id,
        'redeemed_by_name' => $staff->name,
    ]);

    $staff->delete();

    $redemption = $registration->fresh()->lunchRedemption;

    expect($redemption->redeemed_by)->toBeNull()
        ->and($redemption->redeemed_by_name)->toBe('Lunch Desk Staff');

    $this->assertDatabaseHas('lunch_redemptions', [
        'seminar_registration_id' => $registration->id,
        'redeemed_by_name' => 'Lunch Desk Staff',
    ]);
});
