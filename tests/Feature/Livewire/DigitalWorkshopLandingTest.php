<?php

use App\Livewire\DigitalWorkshopLanding;
use App\Models\DigitalWorkshop;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Livewire\livewire;

uses(RefreshDatabase::class);

test('selects the first published workshop by sort order', function () {
    DigitalWorkshop::factory()->create(['status' => 'published', 'sort_order' => 20]);
    $first = DigitalWorkshop::factory()->create(['status' => 'published', 'sort_order' => 10]);

    livewire(DigitalWorkshopLanding::class)
        ->assertSet('workshop.id', $first->id);
});

test('ignores a draft workshop', function () {
    DigitalWorkshop::factory()->create(['status' => 'draft']);

    livewire(DigitalWorkshopLanding::class)
        ->assertSet('workshop', null);
});

test('leaves workshop null when none exist', function () {
    livewire(DigitalWorkshopLanding::class)
        ->assertSet('workshop', null);
});

test('breaks a sort order tie consistently by id', function () {
    $first = DigitalWorkshop::factory()->create(['status' => 'published', 'sort_order' => 10]);
    DigitalWorkshop::factory()->create(['status' => 'published', 'sort_order' => 10]);

    livewire(DigitalWorkshopLanding::class)
        ->assertSet('workshop.id', $first->id);
});

test('formats the standalone price with separators', function () {
    DigitalWorkshop::factory()->create([
        'status' => 'published',
        'price' => 1199000,
    ]);

    livewire(DigitalWorkshopLanding::class)
        ->assertSet('formattedStandalonePrice', '1.199.000');
});

test('formats the bundle price with separators', function () {
    DigitalWorkshop::factory()->create([
        'status' => 'published',
        'bundle_price' => 999000,
    ]);

    livewire(DigitalWorkshopLanding::class)
        ->assertSet('formattedBundlePrice', '999.000');
});

test('returns null for a null bundle price', function () {
    DigitalWorkshop::factory()->create([
        'status' => 'published',
        'bundle_price' => null,
    ]);

    livewire(DigitalWorkshopLanding::class)
        ->assertSet('formattedBundlePrice', null)
        ->assertSet('formattedStandalonePrice', '1.199.000');
});

test('returns null prices when no published workshop exists', function () {
    DigitalWorkshop::factory()->create(['status' => 'draft']);

    livewire(DigitalWorkshopLanding::class)
        ->assertSet('formattedStandalonePrice', null)
        ->assertSet('formattedBundlePrice', null);
});

test('defaults the locale to id', function () {
    livewire(DigitalWorkshopLanding::class)
        ->assertSet('locale', 'id');
});

test('accepts a valid locale and rejects an invalid one', function () {
    livewire(DigitalWorkshopLanding::class)
        ->call('setLocale', 'en')
        ->assertSet('locale', 'en')
        ->call('setLocale', 'fr')
        ->assertSet('locale', 'en');
});
