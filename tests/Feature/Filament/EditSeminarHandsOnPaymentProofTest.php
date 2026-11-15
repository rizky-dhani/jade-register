<?php

use App\Enums\HandsOnStatus;
use App\Filament\Resources\SeminarRegistrations\Pages\EditSeminarRegistration;
use App\Models\Country;
use App\Models\HandsOn;
use App\Models\HandsOnRegistration;
use App\Models\Seminar;
use App\Models\SeminarRegistration;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;

use function Pest\Laravel\actingAs;
use function Pest\Livewire\livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    Role::create(['name' => 'Super Admin', 'guard_name' => 'web']);

    $this->user = User::factory()->create();
    $this->user->assignRole('Super Admin');
    actingAs($this->user);

    Country::create(['id' => 1, 'name' => 'Indonesia', 'code' => 'ID', 'is_indonesia' => true, 'phone_code' => '62']);
    Setting::create(['key' => 'max_participants', 'value' => 100, 'type' => 'integer']);

    $this->seminar = Seminar::create([
        'name' => 'Test Seminar',
        'code' => 'TEST-SEM',
        'applies_to' => 'local',
        'original_price' => 1000000,
        'max_seats' => 100,
        'currency' => 'IDR',
        'is_active' => true,
        'sort_order' => 1,
    ]);

    $this->handsOn = HandsOn::create([
        'name' => 'Test Hands On Day 1',
        'ho_code' => 'HO-001',
        'doctor_name' => 'Dr. Test',
        'description' => 'Test description',
        'event_date' => '2026-11-13',
        'max_seats' => 10,
        'price' => 500000,
        'original_price' => 500000,
        'currency' => 'IDR',
        'is_active' => true,
        'status' => HandsOnStatus::PUBLISHED,
    ]);

    $this->registration = SeminarRegistration::factory()->create([
        'registration_code' => 'JADE-SEM-2026-000123',
        'country_id' => 1,
        'wants_hands_on' => true,
        'kompetensi' => 'Dokter Gigi Umum',
        'seminar_id' => $this->seminar->id,
        'selected_seminar' => $this->seminar->name,
    ]);

    $this->handsOnRegistration = HandsOnRegistration::create([
        'seminar_registration_id' => $this->registration->id,
        'hands_on_id' => $this->handsOn->id,
        'registration_type' => 'combined',
        'payment_status' => 'pending',
    ]);
});

it('prefills the hands-on payment proof from the linked hands-on registration', function () {
    Storage::fake('public');
    Storage::disk('public')->put('payment-proofs/000123-hands-on.jpg', 'proof');

    $this->handsOnRegistration->update(['payment_proof_path' => 'payment-proofs/000123-hands-on.jpg']);

    $state = livewire(EditSeminarRegistration::class, ['record' => $this->registration->getRouteKey()])
        ->assertOk()
        ->get('data.hands_on_payment_proof_path');

    expect(array_values($state))->toBe(['payment-proofs/000123-hands-on.jpg']);
});

it('writes an uploaded hands-on payment proof onto the hands-on registration and verifies it', function () {
    Storage::fake('public');
    Storage::disk('public')->put('payment-proofs/hands-on/upload.jpg', 'proof');

    $component = livewire(EditSeminarRegistration::class, ['record' => $this->registration->getRouteKey()]);
    $component->set('data.hands_on_payment_proof_path', ['payment-proofs/hands-on/upload.jpg']);
    $component->call('save')->assertHasNoFormErrors();

    $handsOnRegistration = $this->handsOnRegistration->fresh();

    expect($handsOnRegistration->payment_proof_path)->toBe('payment-proofs/000123-hands-on.jpg')
        ->and($handsOnRegistration->payment_status)->toBe('verified')
        ->and($handsOnRegistration->verified_at)->not->toBeNull();
});

it('does not expose the hands-on payment proof field when the registration has no hands-on sessions', function () {
    $plain = SeminarRegistration::factory()->create([
        'country_id' => 1,
        'wants_hands_on' => false,
        'kompetensi' => 'Dokter Gigi Umum',
        'seminar_id' => $this->seminar->id,
        'selected_seminar' => $this->seminar->name,
    ]);

    livewire(EditSeminarRegistration::class, ['record' => $plain->getRouteKey()])
        ->assertOk()
        ->assertFormFieldHidden('hands_on_payment_proof_path');
});
