<?php

use App\Livewire\VisitorRegistration;
use App\Livewire\VisitorRegistrationSuccess;
use App\Models\Visitor;
use App\Services\RegistrationService;
use App\Services\VisitorQrTokenService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;

use function Pest\Livewire\livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    Mail::fake();
});

it('redirects to the success page after a visitor registers', function () {
    livewire(VisitorRegistration::class)
        ->set('name', 'Rina Anggraini')
        ->set('email', 'rina@example.test')
        ->set('phone', '081290000001')
        ->set('affiliation', 'Universitas Indonesia')
        ->call('submit')
        ->assertRedirect(route('register.visitor.success', [
            'id' => Visitor::where('email', 'rina@example.test')->first()->id,
            'lang' => 'id',
        ]));

    $this->assertDatabaseHas('visitors', [
        'email' => 'rina@example.test',
        'name' => 'Rina Anggraini',
    ]);
});

it('generates a QR code and sends a confirmation email for new visitors', function () {
    $qr = Mockery::mock(VisitorQrTokenService::class);
    $qr->shouldReceive('generate')->once();
    app()->instance(VisitorQrTokenService::class, $qr);

    $registration = Mockery::mock(RegistrationService::class);
    $registration->shouldReceive('sendVisitorConfirmation')->once();
    app()->instance(RegistrationService::class, $registration);

    livewire(VisitorRegistration::class)
        ->set('name', 'Budi')
        ->set('email', 'budi@example.test')
        ->set('phone', '081290000002')
        ->call('submit')
        ->assertRedirect();
});

it('does not regenerate the QR code for an existing visitor', function () {
    Visitor::create([
        'name' => 'Sari',
        'email' => 'sari@example.test',
        'phone' => '081290000003',
    ]);

    $qr = Mockery::mock(VisitorQrTokenService::class);
    $qr->shouldNotReceive('generate');
    app()->instance(VisitorQrTokenService::class, $qr);

    livewire(VisitorRegistration::class)
        ->set('name', 'Sari')
        ->set('email', 'sari@example.test')
        ->set('phone', '081290000003')
        ->call('submit')
        ->assertRedirect();
});

it('renders the success page for a visitor', function () {
    $visitor = Visitor::create([
        'name' => 'Rina Anggraini',
        'email' => 'rina@example.test',
        'phone' => '081290000001',
    ]);

    livewire(VisitorRegistrationSuccess::class, ['id' => $visitor->id])
        ->assertSet('visitor.id', $visitor->id)
        ->assertSee('Rina Anggraini');
});

it('returns 404 for an unknown visitor on the success page', function () {
    $this->get(route('register.visitor.success', ['id' => 99999]))
        ->assertNotFound();
});
