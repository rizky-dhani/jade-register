<?php

use App\Jobs\CompleteDigitalWorkshopRegistration;
use App\Livewire\DigitalWorkshopRegistration;
use App\Models\Country;
use App\Models\DigitalWorkshop;
use App\Models\DigitalWorkshopRegistration as RegistrationModel;
use App\Models\SeminarRegistration;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;

use function Pest\Livewire\livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    Storage::fake('public');
    Queue::fake();

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
        'is_local' => true,
        'email' => 'attendee@example.com',
        'phone' => '081234567890',
        'name_license' => 'Dr. Attendee',
        'nik' => '1234567890123456',
        'pdgi_branch' => 'Jakarta',
        'kompetensi' => 'Dokter Gigi Umum',
        'country_id' => 1,
        'payment_method' => 'bank_transfer',
        'payment_proof' => UploadedFile::fake()->image('proof.jpg'),
    ], $overrides);
});

test('renders the registration form', function () {
    livewire(DigitalWorkshopRegistration::class)->assertOk();
});

test('renders translated labels instead of raw keys', function () {
    // A key missing from lang/ renders as the literal "seminar.phone" string.
    livewire(DigitalWorkshopRegistration::class)
        ->assertOk()
        ->assertSee(trans('seminar.phone', [], 'id'))
        ->assertDontSee('seminar.phone');
});

test('places the bundle option ahead of the total and drops the total to the bundle price', function () {
    // Amounts chosen so a leaked standalone figure cannot be mistaken for the
    // bundle total, and so the two numbers cannot collide by coincidence.
    $this->workshop->update(['price' => 1375000, 'bundle_price' => 812000]);

    livewire(DigitalWorkshopRegistration::class)
        ->set('wantsBundle', true)
        ->assertSeeHtmlInOrder([
            trans('seminar.digital_workshop_wants_bundle', [], 'id'),
            trans('seminar.total_amount', [], 'id'),
        ])
        ->assertSee('IDR 812.000')
        ->assertDontSee('IDR 1.375.000');
});

test('keeps the standalone amount in the total while the bundle option is unticked', function () {
    $this->workshop->update(['price' => 1375000, 'bundle_price' => 812000]);

    livewire(DigitalWorkshopRegistration::class)
        ->assertSee('IDR 1.375.000')
        ->assertDontSee('IDR 812.000');
});

test('does not print prices inside the bundle option description', function () {
    $this->workshop->update(['price' => 1375000, 'bundle_price' => 812000]);

    $html = livewire(DigitalWorkshopRegistration::class)->html();

    preg_match('/<label class="flex items-start gap-3 cursor-pointer">(.*?)<\/label>/s', $html, $matches);

    expect($matches[1] ?? '')->not->toBe('')
        ->and($matches[1])->toContain(trans('seminar.digital_workshop_wants_bundle', [], 'id'))
        ->and($matches[1])->not->toContain('IDR');
});
test('registers a local attendee at the standalone price', function () {
    livewire(DigitalWorkshopRegistration::class)
        ->set(($this->payload)())
        ->call('submit')
        ->assertHasNoErrors();

    $registration = RegistrationModel::first();

    expect($registration)->not->toBeNull();
    expect($registration->amount)->toBe(1199000);
    expect($registration->registration_type)->toBe('standalone');
    expect($registration->payment_status)->toBe('pending');
    expect($registration->seminar_registration_id)->toBeNull();
});

test('registers at the bundle price with a verified seminar registration', function () {
    $seminar = SeminarRegistration::factory()->create([
        'email' => 'attendee@example.com',
        'payment_status' => 'verified',
    ]);

    livewire(DigitalWorkshopRegistration::class)
        ->set(($this->payload)())
        ->call('submit')
        ->assertHasNoErrors();

    $registration = RegistrationModel::first();

    expect($registration->amount)->toBe(999000);
    expect($registration->registration_type)->toBe('bundled');
    expect($registration->seminar_registration_id)->toBe($seminar->id);
});

test('does not give the bundle price for a pending seminar registration', function () {
    SeminarRegistration::factory()->create([
        'email' => 'attendee@example.com',
        'payment_status' => 'pending',
    ]);

    livewire(DigitalWorkshopRegistration::class)
        ->set(($this->payload)())
        ->call('submit')
        ->assertHasNoErrors();

    expect(RegistrationModel::first()->amount)->toBe(1199000);
});

test('requires an email', function () {
    livewire(DigitalWorkshopRegistration::class)
        ->set(($this->payload)(['email' => '']))
        ->call('submit')
        ->assertHasErrors(['email']);
});

test('requires a payment proof', function () {
    $payload = ($this->payload)();
    unset($payload['payment_proof']);

    livewire(DigitalWorkshopRegistration::class)
        ->set($payload)
        ->call('submit')
        ->assertHasErrors(['payment_proof']);
});

test('rejects a non-permitted proof file type', function () {
    livewire(DigitalWorkshopRegistration::class)
        ->set(($this->payload)(['payment_proof' => UploadedFile::fake()->create('proof.txt', 10)]))
        ->call('submit')
        ->assertHasErrors(['payment_proof']);
});

test('rejects a second registration for the same email and workshop', function () {
    RegistrationModel::factory()->create([
        'digital_workshop_id' => $this->workshop->id,
        'email' => 'attendee@example.com',
        'payment_status' => 'pending',
    ]);

    livewire(DigitalWorkshopRegistration::class)
        ->set(($this->payload)())
        ->call('submit')
        ->assertHasErrors(['email']);

    expect(RegistrationModel::count())->toBe(1);
});

test('refuses registration when the workshop is full', function () {
    $this->workshop->update(['max_seats' => 0]);

    livewire(DigitalWorkshopRegistration::class)
        ->set(($this->payload)())
        ->call('submit')
        ->assertHasErrors();

    expect(RegistrationModel::count())->toBe(0);
});

test('stores the proof under payment-proofs on the public disk', function () {
    livewire(DigitalWorkshopRegistration::class)
        ->set(($this->payload)())
        ->call('submit')
        ->assertHasNoErrors();

    $path = RegistrationModel::first()->payment_proof_path;

    expect($path)->toStartWith('payment-proofs/');
    Storage::disk('public')->assertExists($path);
});

test('sets language from the locale property', function () {
    livewire(DigitalWorkshopRegistration::class)
        ->set(($this->payload)())
        ->set('locale', 'en')
        ->call('submit')
        ->assertHasNoErrors();

    expect(RegistrationModel::first()->language)->toBe('en');
});

test('never creates a seminar registration', function () {
    $before = SeminarRegistration::count();

    livewire(DigitalWorkshopRegistration::class)
        ->set(($this->payload)())
        ->call('submit')
        ->assertHasNoErrors();

    expect(SeminarRegistration::count())->toBe($before);
});

test('dispatches the completion job', function () {
    livewire(DigitalWorkshopRegistration::class)
        ->set(($this->payload)())
        ->call('submit')
        ->assertHasNoErrors();

    Queue::assertPushed(CompleteDigitalWorkshopRegistration::class);
});

test('assigns a registration code with the JADE-DW prefix', function () {
    livewire(DigitalWorkshopRegistration::class)
        ->set(($this->payload)())
        ->call('submit')
        ->assertHasNoErrors();

    expect(RegistrationModel::first()->registration_code)->toStartWith('JADE-DW-2026-');
});

test('404s when no published workshop exists', function () {
    $this->workshop->update(['status' => 'draft']);

    livewire(DigitalWorkshopRegistration::class)->assertStatus(404);
});
