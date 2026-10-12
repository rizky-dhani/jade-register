<?php

use App\Livewire\RegistrationPortal;

use function Pest\Livewire\livewire;

it('renders the registration portal on the home route', function () {
    $this->get(route('home'))
        ->assertOk()
        ->assertSeeLivewire(RegistrationPortal::class)
        ->assertSee(route('register.seminar', ['lang' => 'id']), false)
        ->assertSee(route('register.hands-on', ['lang' => 'id']), false)
        ->assertSee(route('register.visitor', ['lang' => 'id']), false)
        ->assertSee(route('register.digital-workshop', ['lang' => 'id']), false);
});

it('renders links in english when locale is en', function () {
    livewire(RegistrationPortal::class, ['locale' => 'en'])
        ->assertSee('Choose what you would like to register for')
        ->assertSee('Seminar')
        ->assertSee('Exhibition Visitor');
});

it('renders links in indonesian by default', function () {
    livewire(RegistrationPortal::class)
        ->assertSee('Pilih jenis pendaftaran yang Anda inginkan');
});

it('can switch locale', function () {
    livewire(RegistrationPortal::class)
        ->call('setLocale', 'en')
        ->assertSee('Choose what you would like to register for')
        ->call('setLocale', 'id')
        ->assertSee('Pilih jenis pendaftaran yang Anda inginkan');
});

it('ignores invalid locale', function () {
    livewire(RegistrationPortal::class, ['locale' => 'fr'])
        ->assertSee('Pilih jenis pendaftaran yang Anda inginkan');
});
