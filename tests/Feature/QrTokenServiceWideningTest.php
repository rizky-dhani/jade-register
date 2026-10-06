<?php

use App\Models\DigitalWorkshop;
use App\Models\DigitalWorkshopRegistration;
use App\Models\HandsOn;
use App\Models\HandsOnRegistration;
use App\Models\SeminarRegistration;
use App\Services\QrTokenService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('still resolves a seminar token after the widening', function () {
    $r = SeminarRegistration::factory()->create(['qr_token' => null]);
    app(QrTokenService::class)->generate($r);
    expect(app(QrTokenService::class)->validate($r->refresh()->qr_token))
        ->toBeInstanceOf(SeminarRegistration::class);
});

test('still resolves a hands-on token after the widening', function () {
    $r = HandsOnRegistration::factory()->create([
        'hands_on_id' => HandsOn::factory()->create()->id,
        'qr_token' => null,
    ]);
    app(QrTokenService::class)->generateForHandsOn($r);
    expect(app(QrTokenService::class)->validate($r->refresh()->qr_token))
        ->toBeInstanceOf(HandsOnRegistration::class);
});

test('resolves a digital workshop token', function () {
    $r = DigitalWorkshopRegistration::factory()->create([
        'digital_workshop_id' => DigitalWorkshop::factory()->create()->id,
        'qr_token' => null,
    ]);
    app(QrTokenService::class)->generateForDigitalWorkshop($r);
    expect(app(QrTokenService::class)->validate($r->refresh()->qr_token))
        ->toBeInstanceOf(DigitalWorkshopRegistration::class);
});

test('returns null for an unknown token', function () {
    expect(app(QrTokenService::class)->validate('does-not-exist'))->toBeNull();
});

test('dw tokens do not collide with seminar tokens', function () {
    $s = SeminarRegistration::factory()->create(['qr_token' => null]);
    app(QrTokenService::class)->generate($s);
    expect(HandsOnRegistration::where('qr_token', $s->qr_token)->exists())->toBeFalse();
    expect(DigitalWorkshopRegistration::where('qr_token', $s->qr_token)->exists())->toBeFalse();
});
