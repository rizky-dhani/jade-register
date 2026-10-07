<?php

use App\Livewire\DigitalWorkshopRegistration as DigitalWorkshopRegistrationForm;
use App\Livewire\SeminarRegistration as SeminarRegistrationForm;
use App\Models\Country;
use App\Models\DigitalWorkshop;
use App\Models\DigitalWorkshopRegistration;
use App\Models\DigitalWorkshopRegistrationIntent;
use App\Models\SeminarRegistration;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

use function Pest\Livewire\livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    Storage::fake('public');

    Country::create([
        'id' => 1,
        'name' => 'Indonesia',
        'code' => 'ID',
        'is_indonesia' => true,
        'phone_code' => '62',
    ]);

    $this->workshop = DigitalWorkshop::factory()->create([
        'status' => 'published',
        'price' => 1199000,
        'bundle_price' => 999000,
        'max_seats' => null,
    ]);

    $this->payload = fn (array $overrides = []): array => array_merge([
        'name_license' => 'Dr. Bundle Buyer',
        'email' => 'bundle@example.com',
        'phone' => '081234567890',
        'nik' => '1234567890123456',
        'pdgi_branch' => 'Jakarta',
        'kompetensi' => 'Dokter Gigi Umum',
        'country_id' => 1,
        'payment_method' => 'bank_transfer',
        'payment_proof' => UploadedFile::fake()->image('proof.jpg'),
    ], $overrides);
});

test('creates an awaiting intent when bundle is selected', function () {
    livewire(DigitalWorkshopRegistrationForm::class)
        ->set(($this->payload)())
        ->set('wantsBundle', true)
        ->call('submit')
        ->assertHasNoErrors();

    $intent = DigitalWorkshopRegistrationIntent::first();

    expect($intent)->not->toBeNull();
    expect($intent->status)->toBe('awaiting_seminar');
    expect($intent->email)->toBe('bundle@example.com');
    expect($intent->digital_workshop_id)->toBe($this->workshop->id);
    expect($intent->name_license)->toBe('Dr. Bundle Buyer');
    expect($intent->nik)->toBe('1234567890123456');
    expect($intent->pdgi_branch)->toBe('Jakarta');
    expect($intent->phone)->toBe('081234567890');
    expect($intent->kompetensi)->toBe('Dokter Gigi Umum');
    expect($intent->country_id)->toBe(1);
});

test('redirects to the seminar form with the intent id', function () {
    livewire(DigitalWorkshopRegistrationForm::class)
        ->set(($this->payload)())
        ->set('wantsBundle', true)
        ->call('submit')
        ->assertRedirect(route('register.seminar', ['dw_intent' => DigitalWorkshopRegistrationIntent::first()->id]));
});

test('does not create a digital workshop registration when bundling', function () {
    livewire(DigitalWorkshopRegistrationForm::class)
        ->set(($this->payload)())
        ->set('wantsBundle', true)
        ->call('submit');

    expect(DigitalWorkshopRegistration::count())->toBe(0);
});

test('does not create a seminar registration', function () {
    livewire(DigitalWorkshopRegistrationForm::class)
        ->set(($this->payload)())
        ->set('wantsBundle', true)
        ->call('submit');

    expect(SeminarRegistration::count())->toBe(0);
});

test('registers standalone when bundle is not selected', function () {
    livewire(DigitalWorkshopRegistrationForm::class)
        ->set(($this->payload)())
        ->set('wantsBundle', false)
        ->call('submit')
        ->assertHasNoErrors();

    $registration = DigitalWorkshopRegistration::first();

    expect($registration)->not->toBeNull();
    expect($registration->amount)->toBe(1199000);
    expect($registration->registration_type)->toBe('standalone');
    expect(DigitalWorkshopRegistrationIntent::count())->toBe(0);
});

test('prefills the seminar form from an intent', function () {
    $intent = DigitalWorkshopRegistrationIntent::factory()->create([
        'digital_workshop_id' => $this->workshop->id,
        'email' => 'Prefilled@Example.com',
        'name_license' => 'Dr. Prefilled',
        'nik' => '9999888877776666',
        'pdgi_branch' => 'Bandung',
        'kompetensi' => 'Spesialis Konservasi',
        'phone' => '089876543210',
        'country_id' => 1,
        'language' => 'id',
    ]);

    livewire(SeminarRegistrationForm::class, ['dw_intent' => $intent->id])
        ->assertSet('email', 'prefilled@example.com')
        ->assertSet('name_license', 'Dr. Prefilled')
        ->assertSet('nik', '9999888877776666')
        ->assertSet('pdgi_branch', 'Bandung')
        ->assertSet('kompetensi', 'Spesialis Konservasi')
        ->assertSet('phone', '089876543210')
        ->assertSet('country_id', 1);
});

test('ignores an unknown intent id', function () {
    livewire(SeminarRegistrationForm::class, ['dw_intent' => 999999])
        ->assertOk()
        ->assertHasNoErrors();
});

test('ignores an intent that is no longer awaiting', function () {
    $intent = DigitalWorkshopRegistrationIntent::factory()->create([
        'status' => 'fulfilled',
        'fulfilled_at' => now(),
        'email' => 'fulfilled@example.com',
        'name_license' => 'Should Not Prefill',
    ]);

    // A stale link must not error, and must not prefill from a consumed intent.
    livewire(SeminarRegistrationForm::class, ['dw_intent' => $intent->id])
        ->assertOk()
        ->assertSet('name_license', '');
});

test('updates an existing awaiting intent rather than creating a second one', function () {
    $existing = DigitalWorkshopRegistrationIntent::factory()->create([
        'digital_workshop_id' => $this->workshop->id,
        'email' => 'bundle@example.com',
        'phone' => '080000000000',
    ]);

    livewire(DigitalWorkshopRegistrationForm::class)
        ->set(($this->payload)())
        ->set('wantsBundle', true)
        ->call('submit');

    expect(DigitalWorkshopRegistrationIntent::count())->toBe(1);
    expect(DigitalWorkshopRegistrationIntent::first()->id)->toBe($existing->id);
    expect(DigitalWorkshopRegistrationIntent::first()->phone)->toBe('081234567890');
});
