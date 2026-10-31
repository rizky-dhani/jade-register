<?php

use App\Models\Seminar;
use App\Models\SeminarRegistration;
use App\Models\Setting;
use App\Models\User;
use App\Services\LunchRedemptionService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function eligibleLunchRegistration(): SeminarRegistration
{
    $seminar = Seminar::create([
        'name' => 'Lunch Seminar',
        'code' => 'lunch-'.uniqid(),
        'applies_to' => 'all',
        'currency' => 'IDR',
        'includes_lunch' => true,
    ]);

    return SeminarRegistration::factory()->create(['seminar_id' => $seminar->id]);
}

function setLunchEventDate(?string $date): void
{
    Setting::updateOrCreate(
        ['key' => 'seminar_event_date'],
        ['label' => 'Seminar Event Date', 'type' => 'date', 'value' => $date ?? '']
    );
}

test('redemption is open only on the configured event date', function () {
    test('event date matching uses the configured application timezone', function () {
        config(['app.timezone' => 'Asia/Jakarta']);
        $this->travelTo(Carbon::parse('2026-11-14 18:00:00', 'UTC'));
        setLunchEventDate('2026-11-15');

        expect(app(LunchRedemptionService::class)->isRedemptionOpen())->toBeTrue();
    });
    $service = app(LunchRedemptionService::class);

    setLunchEventDate(null);
    expect($service->isRedemptionOpen())->toBeFalse();

    setLunchEventDate(today()->toDateString());
    expect($service->isRedemptionOpen())->toBeTrue();

    setLunchEventDate(today()->subDay()->toDateString());
    expect($service->isRedemptionOpen())->toBeFalse();

    setLunchEventDate(today()->addDay()->toDateString());
    expect($service->isRedemptionOpen())->toBeFalse();
});

test('eligible registration can be redeemed once with staff identity and time', function () {
    setLunchEventDate(today()->toDateString());
    $registration = eligibleLunchRegistration();
    $staff = User::factory()->create(['name' => 'Lunch Team']);
    $this->travelTo(now()->setTime(12, 34, 0));

    $service = app(LunchRedemptionService::class);
    $redemption = $service->redeem($registration, $staff);
    $again = $service->redeem($registration, User::factory()->create(['name' => 'Other Staff']));

    expect($again->is($redemption))->toBeTrue()
        ->and($again->redeemed_by)->toBe($staff->id)
        ->and($again->redeemed_by_name)->toBe('Lunch Team')
        ->and($again->redeemed_at->toDateTimeString())->toBe(now()->toDateTimeString());

    $this->assertDatabaseCount('lunch_redemptions', 1);
});

test('ineligible registration cannot be redeemed', function () {
    setLunchEventDate(today()->toDateString());
    $seminar = Seminar::create([
        'name' => 'No Lunch Seminar',
        'code' => 'no-lunch-'.uniqid(),
        'applies_to' => 'all',
        'currency' => 'IDR',
        'includes_lunch' => false,
    ]);
    $registration = SeminarRegistration::factory()->create(['seminar_id' => $seminar->id]);

    expect(fn () => app(LunchRedemptionService::class)->redeem($registration, User::factory()->create()))
        ->toThrow(DomainException::class);

    $this->assertDatabaseCount('lunch_redemptions', 0);
});

test('existing redemption remains unchanged if lunch entitlement is removed', function () {
    setLunchEventDate(today()->toDateString());
    $registration = eligibleLunchRegistration();
    $firstStaff = User::factory()->create(['name' => 'First Staff']);
    $redemption = app(LunchRedemptionService::class)->redeem($registration, $firstStaff);
    $registration->seminarPackage->update(['includes_lunch' => false]);

    $again = app(LunchRedemptionService::class)->redeem($registration->fresh(), User::factory()->create());

    expect($again->id)->toBe($redemption->id)
        ->and($again->redeemed_by)->toBe($firstStaff->id)
        ->and($again->redeemed_by_name)->toBe('First Staff');
});
