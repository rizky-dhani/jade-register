<?php

use App\Livewire\SeminarRegistration as SeminarRegistrationComponent;
use App\Models\Country;
use App\Models\DigitalWorkshop;
use App\Models\DigitalWorkshopRegistration;
use App\Models\SeminarRegistration;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Livewire\livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    Country::factory()->indonesia()->create(['id' => 1]);

    $this->workshop = DigitalWorkshop::factory()->create([
        'status' => 'published',
        'price' => 1199000,
        'bundle_price' => 999000,
        'max_seats' => 50,
    ]);

    $this->registration = SeminarRegistration::factory()->verified()->create([
        'email' => 'existing@example.com',
        'country_id' => 1,
    ]);
});

function openExistingRegistration(string $email = 'existing@example.com')
{
    return livewire(SeminarRegistrationComponent::class)
        ->set('verification_email', $email)
        ->call('checkExistingRegistration');
}

it('offers the digital workshop to a verified existing registration', function () {
    openExistingRegistration()
        ->assertSet('showVerificationError', false)
        ->assertSet('alreadyRegisteredDigitalWorkshopId', null)
        ->assertSee(__('seminar.digital_workshop_existing_description'));
});

it('attaches a digital workshop to an existing registration', function () {
    openExistingRegistration()
        ->set('wants_digital_workshop', true)
        ->set('digital_workshop_id', $this->workshop->id)
        ->set('existing_payment_proof_uploaded', true)
        ->set('existing_payment_proof_path', 'payment-proofs/000001.jpg')
        ->call('submitExistingRegistration')
        ->assertHasNoErrors()
        ->assertRedirect();

    $dw = DigitalWorkshopRegistration::first();

    expect($dw)->not->toBeNull()
        ->and($dw->seminar_registration_id)->toBe($this->registration->id)
        ->and($dw->digital_workshop_id)->toBe($this->workshop->id)
        ->and($dw->registration_type)->toBe('bundled')
        ->and($dw->amount)->toBe(999000)
        ->and($dw->payment_status)->toBe('pending')
        ->and($dw->payment_proof_path)->toBe('payment-proofs/000001.jpg');
});

it('rejects a second digital workshop submission for the same registration', function () {
    DigitalWorkshopRegistration::create([
        'registration_code' => 'JADE-DW-2026-000001',
        'qr_token' => (string) Str::uuid(),
        'digital_workshop_id' => $this->workshop->id,
        'seminar_registration_id' => $this->registration->id,
        'registration_type' => 'bundled',
        'amount' => 999000,
        'email' => $this->registration->email,
        'phone' => '081234567890',
        'status' => 'registered',
        'country_id' => 1,
        'payment_status' => 'pending',
    ]);

    openExistingRegistration()
        ->assertSet('alreadyRegisteredDigitalWorkshopId', $this->workshop->id)
        ->set('wants_digital_workshop', true)
        ->set('digital_workshop_id', $this->workshop->id)
        ->set('existing_payment_proof_uploaded', true)
        ->set('existing_payment_proof_path', 'payment-proofs/000001.jpg')
        ->call('submitExistingRegistration')
        ->assertHasErrors('existing');

    expect(DigitalWorkshopRegistration::count())->toBe(1);
});

it('requires a workshop selection when the checkbox is ticked', function () {
    openExistingRegistration()
        ->set('wants_digital_workshop', true)
        ->set('existing_payment_proof_uploaded', true)
        ->set('existing_payment_proof_path', 'payment-proofs/000001.jpg')
        ->call('submitExistingRegistration')
        ->assertHasErrors('digital_workshop_id');

    expect(DigitalWorkshopRegistration::count())->toBe(0);
});

it('does not offer the digital workshop before payment is verified', function () {
    $pending = SeminarRegistration::factory()->pending()->create([
        'email' => 'pending@example.com',
        'country_id' => 1,
    ]);

    openExistingRegistration('pending@example.com')
        ->assertSet('existingRegistration.id', $pending->id)
        ->assertSet('availableDigitalWorkshops', [])
        ->assertDontSee(__('seminar.digital_workshop_existing_description'));
});
