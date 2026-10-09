<?php

use App\Livewire\DigitalWorkshopLanding;
use App\Models\DigitalWorkshop;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Livewire\livewire;

uses(RefreshDatabase::class);

/**
 * A published workshop is the precondition for every assertion about selling
 * the workshop, so it gets a named helper rather than a closure on $this.
 */
function publishedWorkshop(array $attributes = []): DigitalWorkshop
{
    return DigitalWorkshop::factory()->create(
        array_merge(['status' => 'published'], $attributes)
    );
}

test('renders the route', function () {
    publishedWorkshop();

    $this->get(route('digital-workshop'))->assertOk();
});

test('shows the indonesian heading and subtitle by default', function () {
    publishedWorkshop();

    $response = $this->get(route('digital-workshop'));

    $response->assertSee(trans('seminar.digital_workshop_expect_heading', [], 'id'), false);
    $response->assertSee(trans('seminar.digital_workshop_subtitle', [], 'id'), false);
});

test('switches copy to english', function () {
    publishedWorkshop();

    $response = $this->get(route('digital-workshop', ['lang' => 'en']));

    $response->assertSee(trans('seminar.digital_workshop_expect_heading', [], 'en'), false);
    $response->assertDontSee(trans('seminar.digital_workshop_expect_heading', [], 'id'), false);
});

test('shows all four benefits', function () {
    publishedWorkshop();

    $response = $this->get(route('digital-workshop', ['lang' => 'en']));

    foreach ([
        'digital_workshop_benefit_followers',
        'digital_workshop_benefit_algorithm',
        'digital_workshop_benefit_depth',
        'digital_workshop_benefit_followup',
    ] as $key) {
        $response->assertSee(trans('seminar.'.$key, [], 'en'), false);
    }
});

test('shows the closing line', function () {
    publishedWorkshop();

    $this->get(route('digital-workshop', ['lang' => 'en']))
        ->assertSee(trans('seminar.digital_workshop_closing', [], 'en'), false);
});

test('shows both prices when a published workshop exists', function () {
    // Deliberately unusual amounts: if the page ever hardcodes 1199000/999000
    // instead of reading the row, these assertions cannot pass by coincidence.
    publishedWorkshop(['price' => 1375000, 'bundle_price' => 812000]);

    $this->get(route('digital-workshop'))
        ->assertSee('1.375.000', false)
        ->assertSee('812.000', false);
});

test('shows a cta link to the registration route', function () {
    publishedWorkshop();

    $this->get(route('digital-workshop'))
        ->assertSee(route('register.digital-workshop'), false);
});

test('hides the cta when no published workshop exists', function () {
    DigitalWorkshop::factory()->create(['status' => 'draft']);

    $response = $this->get(route('digital-workshop'));

    $response->assertDontSee(route('register.digital-workshop'), false);
    $response->assertSee(trans('seminar.digital_workshop_opens_soon', [], 'id'), false);
});

test('renders no cta anchor at all when no published workshop exists', function () {
    DigitalWorkshop::factory()->create(['status' => 'draft']);

    $html = $this->get(route('digital-workshop'))->getContent();

    expect($html)->not->toContain('register.digital-workshop')
        ->and($html)->not->toContain(trans('seminar.digital_workshop_cta', [], 'id'));
});

test('omits the bundle price when bundle_price is null', function () {
    publishedWorkshop(['price' => 1375000, 'bundle_price' => null]);

    $response = $this->get(route('digital-workshop'));

    $response->assertDontSee(trans('seminar.digital_workshop_price_bundle', [], 'id'), false);
    $response->assertSee('1.375.000', false);
    // The null bundle must not surface as a zero price anywhere on the page.
    $response->assertDontSee('IDR 0', false);
});

test('omits the bundle price line when no published workshop exists', function () {
    DigitalWorkshop::factory()->create(['status' => 'draft', 'price' => 1375000, 'bundle_price' => 812000]);

    $this->get(route('digital-workshop'))
        ->assertDontSee('812.000', false)
        ->assertDontSee('1.375.000', false);
});

test('shows the lower sort order price when two published workshops exist', function () {
    publishedWorkshop(['sort_order' => 20, 'price' => 1375000]);
    publishedWorkshop(['sort_order' => 10, 'price' => 812000]);

    $this->get(route('digital-workshop'))
        ->assertSee('812.000', false)
        ->assertDontSee('1.375.000', false);
});

test('labels the cta Daftar Workshop without the Digital prefix', function () {
    publishedWorkshop();

    $this->get(route('digital-workshop', ['lang' => 'id']))
        ->assertSee('Daftar Workshop', false)
        ->assertDontSee('Daftar Digital Workshop', false);
});

test('keeps the locale in the url', function () {
    publishedWorkshop();

    $this->get(route('digital-workshop', ['lang' => 'en']))
        ->assertSee(trans('seminar.digital_workshop_cta', [], 'en'), false)
        ->assertDontSee(trans('seminar.digital_workshop_cta', [], 'id'), false);
});

test('renders the page in the locale kept by setLocale', function () {
    publishedWorkshop();

    livewire(DigitalWorkshopLanding::class)
        ->call('setLocale', 'en')
        ->assertSee(trans('seminar.digital_workshop_cta', [], 'en'));
});
