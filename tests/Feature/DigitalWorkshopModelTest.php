<?php

use App\Enums\HandsOnStatus;
use App\Models\DigitalWorkshop;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('casts dates, prices and status correctly', function () {
    $workshop = DigitalWorkshop::factory()->create([
        'event_date' => '2026-11-20',
        'price' => 1199000,
        'bundle_price' => 999000,
        'status' => HandsOnStatus::PUBLISHED,
    ]);

    expect($workshop->event_date)->toBeInstanceOf(DateTimeInterface::class);
    expect($workshop->price)->toBeInt();
    expect($workshop->bundle_price)->toBeInt();
    expect($workshop->max_seats)->toBeInt();
    expect($workshop->sort_order)->toBeInt();
    expect($workshop->status)->toBe(HandsOnStatus::PUBLISHED);
});

test('formats the standalone price as rupiah', function () {
    $workshop = DigitalWorkshop::factory()->create(['price' => 1199000]);

    expect($workshop->formatted_price)->toBe('Rp 1.199.000');
});

test('formats the bundle price as rupiah', function () {
    $workshop = DigitalWorkshop::factory()->create(['bundle_price' => 999000]);

    expect($workshop->formatted_bundle_price)->toBe('Rp 999.000');
});

test('treats zero max seats as full', function () {
    $workshop = DigitalWorkshop::factory()->create(['max_seats' => 0]);

    expect($workshop->isFull())->toBeTrue();
    expect($workshop->getAvailableSeats())->toBe(0);
});

test('treats null max seats as unlimited', function () {
    $workshop = DigitalWorkshop::factory()->create(['max_seats' => null]);

    expect($workshop->getAvailableSeats())->toBe(PHP_INT_MAX);
    expect($workshop->isFull())->toBeFalse();
});

test('accepts a zero price', function () {
    $workshop = DigitalWorkshop::factory()->create(['price' => 0]);

    $workshop->refresh();

    expect($workshop->price)->toBe(0);
});

test('scopes to published workshops', function () {
    DigitalWorkshop::factory()->create(['status' => HandsOnStatus::DRAFT]);
    $published = DigitalWorkshop::factory()->create(['status' => HandsOnStatus::PUBLISHED]);

    $results = DigitalWorkshop::published()->get();

    expect($results)->toHaveCount(1);
    expect($results->first()->id)->toBe($published->id);
});

test('stores event time and end time as time values', function () {
    $workshop = DigitalWorkshop::factory()->create([
        'event_time' => '09:00:00',
        'event_end_time' => '12:00:00',
    ]);

    $row = DigitalWorkshop::query()->find($workshop->id);

    expect($row->event_time)->not->toBeNull();
    expect($row->event_end_time)->not->toBeNull();
    expect($row->getRawOriginal('event_time'))->toBe('09:00:00');
    expect($row->getRawOriginal('event_end_time'))->toBe('12:00:00');
});
