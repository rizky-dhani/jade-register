<?php

use App\Livewire\SeminarRegistrationSuccess;
use App\Models\DigitalWorkshop;
use App\Models\SeminarRegistration;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Livewire\livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->registration = SeminarRegistration::factory()->create([
        'payment_status' => 'pending',
    ]);
});

function openSuccessPage(SeminarRegistration $registration)
{
    return livewire(
        SeminarRegistrationSuccess::class,
        ['id' => $registration->id],
    );
}

test('links to the digital workshop landing page from the seminar success page', function () {
    DigitalWorkshop::factory()->create(['status' => 'published']);

    openSuccessPage($this->registration)
        ->assertSee(route('digital-workshop'));
});

test('hides the link when no published workshop exists', function () {
    DigitalWorkshop::factory()->create(['status' => 'draft']);

    $html = openSuccessPage($this->registration)->html();

    expect($html)->not->toContain(route('digital-workshop'));
});

test('hides the link when no workshop exists at all', function () {
    $html = openSuccessPage($this->registration)->html();

    expect($html)->not->toContain(route('digital-workshop'));
});

test('does not recompute prices on the success page', function () {
    DigitalWorkshop::factory()->create([
        'status' => 'published',
        'price' => 1375000,
        'bundle_price' => 812000,
    ]);

    $html = openSuccessPage($this->registration)->html();

    expect($html)->not->toContain('1.375.000')
        ->and($html)->not->toContain('812.000');
});
