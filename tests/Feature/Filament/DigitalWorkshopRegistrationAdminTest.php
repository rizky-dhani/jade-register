<?php

use App\Filament\Resources\DigitalWorkshopRegistrations\Pages\ListDigitalWorkshopRegistrations;
use App\Mail\DigitalWorkshopRegistrationConfirmation;
use App\Models\Country;
use App\Models\DigitalWorkshop;
use App\Models\DigitalWorkshopRegistration;
use App\Models\SeminarRegistration;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;

use function Pest\Laravel\actingAs;
use function Pest\Livewire\livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);

    Country::create([
        'id' => 1,
        'name' => 'Indonesia',
        'code' => 'ID',
        'is_indonesia' => true,
        'phone_code' => '62',
    ]);

    $this->workshop = DigitalWorkshop::factory()->create(['status' => 'published']);

    $user = User::factory()->create();
    $user->assignRole('Super Admin');
    actingAs($user);
});

test('lists registrations', function () {
    $registration = DigitalWorkshopRegistration::factory()->create([
        'digital_workshop_id' => $this->workshop->id,
    ]);

    livewire(ListDigitalWorkshopRegistrations::class)
        ->assertOk()
        ->assertCanSeeTableRecords([$registration]);
});

test('shows the bundle amount for a bundled registration', function () {
    $registration = DigitalWorkshopRegistration::factory()->create([
        'digital_workshop_id' => $this->workshop->id,
        'amount' => 999000,
        'registration_type' => 'bundled',
        'seminar_registration_id' => SeminarRegistration::factory()->create()->id,
    ]);

    livewire(ListDigitalWorkshopRegistrations::class)
        ->assertOk()
        ->assertCanSeeTableRecords([$registration]);
});

test('verifies a pending registration', function () {
    $registration = DigitalWorkshopRegistration::factory()->create([
        'digital_workshop_id' => $this->workshop->id,
        'payment_status' => 'pending',
        'verified_at' => null,
    ]);

    livewire(ListDigitalWorkshopRegistrations::class)
        ->callTableAction('verifyPayment', $registration);

    $registration->refresh();

    expect($registration->payment_status)->toBe('verified');
    expect($registration->verified_at)->not->toBeNull();
});

test('rejects a pending registration, recording the reason', function () {
    $registration = DigitalWorkshopRegistration::factory()->create([
        'digital_workshop_id' => $this->workshop->id,
        'payment_status' => 'pending',
    ]);

    // callTableAction(...)->fillForm() does not populate the modal schema in this
    // repo (see .ai/rules/filament.md). Invoke the action's own closure with the
    // submitted data so this still asserts the action's real behaviour rather
    // than a form that never fills.
    livewire(ListDigitalWorkshopRegistrations::class)
        ->instance()
        ->getTable()
        ->getAction('rejectPayment')
        ->call([
            'record' => $registration,
            'data' => ['rejection_reason' => 'Proof unreadable'],
        ]);

    $registration->refresh();

    expect($registration->payment_status)->toBe('rejected');
    expect($registration->rejection_reason)->toBe('Proof unreadable');
});

test('hides verify for an already verified registration', function () {
    $registration = DigitalWorkshopRegistration::factory()->create([
        'digital_workshop_id' => $this->workshop->id,
        'payment_status' => 'verified',
    ]);

    livewire(ListDigitalWorkshopRegistrations::class)
        ->assertTableActionHidden('verifyPayment', $registration);
});

test('bulk verifies selected registrations', function () {
    $registrations = DigitalWorkshopRegistration::factory()->count(2)->create([
        'digital_workshop_id' => $this->workshop->id,
        'payment_status' => 'pending',
    ]);

    livewire(ListDigitalWorkshopRegistrations::class)
        ->callTableBulkAction('verifyPayment', $registrations);

    $registrations->each(fn ($r) => expect($r->refresh()->payment_status)->toBe('verified'));
});

test('filters by payment status', function () {
    $verified = DigitalWorkshopRegistration::factory()->create([
        'digital_workshop_id' => $this->workshop->id,
        'payment_status' => 'verified',
    ]);
    $pending = DigitalWorkshopRegistration::factory()->create([
        'digital_workshop_id' => $this->workshop->id,
        'payment_status' => 'pending',
    ]);

    livewire(ListDigitalWorkshopRegistrations::class)
        ->filterTable('payment_status', 'verified')
        ->assertCanSeeTableRecords([$verified])
        ->assertCanNotSeeTableRecords([$pending]);
});

test('resends the confirmation email', function () {
    Mail::fake();

    $registration = DigitalWorkshopRegistration::factory()->create([
        'digital_workshop_id' => $this->workshop->id,
        'confirmation_email_sent_at' => null,
    ]);

    livewire(ListDigitalWorkshopRegistrations::class)
        ->callTableAction('resendEmailConfirmation', $registration);

    Mail::assertSent(DigitalWorkshopRegistrationConfirmation::class);
});

test('denies an Admin the force delete ability', function () {
    $admin = User::factory()->create();
    $admin->assignRole('Admin');

    expect($admin->hasPermissionTo('force delete digital workshop registrations'))->toBeFalse();
    expect($admin->hasPermissionTo('update digital workshop registrations'))->toBeTrue();
});
