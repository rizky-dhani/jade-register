<?php

use App\Enums\HandsOnStatus;
use App\Models\DigitalWorkshop;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\DigitalWorkshopSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('seeds exactly one digital workshop', function () {
    $this->seed(DatabaseSeeder::class);

    expect(DigitalWorkshop::count())->toBe(1);
});

test('seeds the workshop with the standalone price', function () {
    $this->seed(DatabaseSeeder::class);

    expect(DigitalWorkshop::first()->price)->toBe(1199000);
});

test('seeds the workshop as a draft', function () {
    $this->seed(DatabaseSeeder::class);

    expect(DigitalWorkshop::first()->status)->toBe(HandsOnStatus::DRAFT);
});
test('seeding twice does not duplicate the workshop', function () {
    // DatabaseSeeder as a whole is not idempotent (PosterCategoryAndTopicSeeder
    // uses create()). Assert idempotency of this plan's own seeder in isolation.
    $this->seed(DigitalWorkshopSeeder::class);
    $this->seed(DigitalWorkshopSeeder::class);

    expect(DigitalWorkshop::count())->toBe(1);
});
