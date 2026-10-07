<?php

use App\Livewire\DigitalWorkshopRegistration as DigitalWorkshopRegistrationForm;
use App\Models\Country;
use App\Models\DigitalWorkshop;
use App\Models\DigitalWorkshopRegistration;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;

use function Pest\Livewire\livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    Storage::fake('public');

    // Roles only — the bypass checks hasRole(), it does not need permissions.
    Role::create(['name' => 'Super Admin', 'guard_name' => 'web']);
    Role::create(['name' => 'Admin', 'guard_name' => 'web']);

    Country::create([
        'id' => 1,
        'name' => 'Indonesia',
        'code' => 'ID',
        'is_indonesia' => true,
        'phone_code' => '62',
    ]);

    Country::create([
        'id' => 2,
        'name' => 'Singapore',
        'code' => 'SG',
        'is_indonesia' => false,
        'phone_code' => '65',
    ]);

    $this->workshop = DigitalWorkshop::factory()->create([
        'status' => 'published',
        'max_seats' => null,
    ]);

    $this->payload = fn (array $overrides = []): array => array_merge([
        'name_license' => 'Dr. Affordance',
        'email' => 'affordance@example.com',
        'phone' => '081234567890',
        'nik' => '1234567890123456',
        'pdgi_branch' => 'Jakarta',
        'kompetensi' => 'Dokter Gigi Umum',
        'country_id' => 1,
        'payment_proof' => UploadedFile::fake()->image('proof.jpg'),
    ], $overrides);
});

function setSetting(string $key, mixed $value): void
{
    $type = str_contains($key, '_at') ? 'datetime' : 'boolean';

    Setting::updateOrCreate(
        ['key' => $key],
        ['value' => $value, 'type' => $type, 'label' => $key],
    );
}

test('is open when no settings restrict it', function () {
    expect(DigitalWorkshopRegistrationForm::isRegistrationOpen())->toBeTrue();
});

test('is closed before opens_at', function () {
    setSetting('digital_workshop_registration_opens_at', now()->addDay()->toDateTimeString());

    expect(DigitalWorkshopRegistrationForm::isRegistrationOpen())->toBeFalse();
});

test('is open after opens_at has passed', function () {
    setSetting('digital_workshop_registration_opens_at', now()->subDay()->toDateTimeString());

    expect(DigitalWorkshopRegistrationForm::isRegistrationOpen())->toBeTrue();
});

test('is closed at or after close_at', function () {
    setSetting('digital_workshop_registration_close_at', now()->subDay()->toDateTimeString());

    expect(DigitalWorkshopRegistrationForm::isRegistrationOpen())->toBeFalse();
});

test('is closed when the toggle is off', function () {
    setSetting('digital_workshop_registration_open', false);

    expect(DigitalWorkshopRegistrationForm::isRegistrationOpen())->toBeFalse();
});

test('bypasses the gate for Super Admin and Admin', function () {
    setSetting('digital_workshop_registration_open', false);

    expect(DigitalWorkshopRegistrationForm::isRegistrationOpen())->toBeFalse();

    foreach (['Super Admin', 'Admin'] as $role) {
        $user = User::factory()->create();
        $user->assignRole($role);

        auth()->login($user);
        expect(DigitalWorkshopRegistrationForm::isRegistrationOpen())->toBeTrue();
        auth()->logout();
    }

    // A participant with no bypass role is still refused.
    $participant = User::factory()->create();
    auth()->login($participant);
    expect(DigitalWorkshopRegistrationForm::isRegistrationOpen())->toBeFalse();
    auth()->logout();
});

test('refuses submission when registration is closed', function () {
    // Review Focus 1: a stale tab left open past the close time must be refused
    // server-side, not merely hidden by the UI.
    setSetting('digital_workshop_registration_close_at', now()->subHour()->toDateTimeString());

    livewire(DigitalWorkshopRegistrationForm::class)
        ->set(($this->payload)())
        ->call('submit');

    expect(DigitalWorkshopRegistration::count())->toBe(0);
});

test('accepts submission when registration is open', function () {
    livewire(DigitalWorkshopRegistrationForm::class)
        ->set(($this->payload)())
        ->call('submit')
        ->assertHasNoErrors();

    expect(DigitalWorkshopRegistration::count())->toBe(1);
});

test('flips is_local when the country changes', function () {
    // Review Focus 4
    livewire(DigitalWorkshopRegistrationForm::class)
        ->set('country_id', 2)
        ->assertSet('is_local', false)
        ->set('country_id', 1)
        ->assertSet('is_local', true);
});

test('deletes the previous proof file from disk when the proof is reset', function () {
    // Review Focus 3: orphaned proofs must not accumulate on disk. The proof is
    // written to the public disk during submit(); resetPaymentProof() is what
    // removes it when the attendee replaces or clears their upload.
    $component = livewire(DigitalWorkshopRegistrationForm::class);

    Storage::disk('public')->put('payment-proofs/previous.jpg', 'stored proof');
    expect(Storage::disk('public')->exists('payment-proofs/previous.jpg'))->toBeTrue();

    $component->set('payment_proof_path', 'payment-proofs/previous.jpg');
    $component->call('resetPaymentProof');

    expect(Storage::disk('public')->exists('payment-proofs/previous.jpg'))->toBeFalse();
    expect($component->get('payment_proof'))->toBeNull();
    expect($component->get('payment_proof_path'))->toBeNull();
    expect($component->get('payment_proof_uploaded'))->toBeFalse();
});

test('resets the payment proof state', function () {
    $component = livewire(DigitalWorkshopRegistrationForm::class);

    Storage::disk('public')->put('payment-proofs/reset-me.jpg', 'x');
    $component->set('payment_proof_path', 'payment-proofs/reset-me.jpg');
    $component->set('payment_proof_uploaded', true);

    $component->call('resetPaymentProof');

    expect(Storage::disk('public')->exists('payment-proofs/reset-me.jpg'))->toBeFalse();
    expect($component->get('payment_proof_path'))->toBeNull();
    expect($component->get('payment_proof_uploaded'))->toBeFalse();
});
