<?php

use App\Filament\Resources\HandsOnRegistrations\Pages\ListHandsOnRegistrations;
use App\Mail\HandsOnRegistrationConfirmation;
use App\Models\Country;
use App\Models\HandsOn;
use App\Models\HandsOnRegistration;
use App\Models\SeminarRegistration;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Spatie\Permission\Models\Role;

use function Pest\Livewire\livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    Role::create(['name' => 'Super Admin', 'guard_name' => 'web']);

    Setting::create([
        'key' => 'max_participants',
        'value' => '1000',
        'type' => 'integer',
    ]);

    Country::create([
        'id' => 1,
        'name' => 'Indonesia',
        'code' => 'ID',
        'is_indonesia' => true,
        'phone_code' => '62',
    ]);

    HandsOn::create([
        'name' => 'Test Hands On',
        'ho_code' => 'HO-001',
        'doctor_name' => 'Dr. Test',
        'description' => 'Test description',
        'event_date' => '2026-11-13',
        'max_seats' => 10,
        'price' => 500000,
        'original_price' => 500000,
        'currency' => 'IDR',
        'is_active' => true,
    ]);

    $this->user = User::factory()->create();
    $this->user->assignRole('Super Admin');
    $this->actingAs($this->user);
});

it('queues the confirmation email to each selected record', function () {
    Mail::fake();

    $seminarRegistration = SeminarRegistration::factory()->create([
        'email' => 'seminar-owner@example.com',
        'language' => 'id',
    ]);

    $ownEmail = HandsOnRegistration::factory()->create([
        'seminar_registration_id' => null,
        'hands_on_id' => HandsOn::first()->id,
        'email' => 'own@example.com',
        'language' => 'en',
    ]);

    $inheritsEmail = HandsOnRegistration::factory()->create([
        'seminar_registration_id' => $seminarRegistration->id,
        'hands_on_id' => HandsOn::first()->id,
        'email' => null,
        'language' => '',
    ]);

    livewire(ListHandsOnRegistrations::class)
        ->callTableBulkAction('bulkSendEmailConfirmation', [$ownEmail, $inheritsEmail])
        ->assertHasNoTableBulkActionErrors();

    Mail::assertQueued(HandsOnRegistrationConfirmation::class, 2);
    Mail::assertQueued(HandsOnRegistrationConfirmation::class, fn ($mail) => $mail->hasTo('own@example.com'));
    Mail::assertQueued(HandsOnRegistrationConfirmation::class, fn ($mail) => $mail->hasTo('seminar-owner@example.com'));
});

it('skips selected records with no email anywhere', function () {
    Mail::fake();

    $seminarRegistration = SeminarRegistration::factory()->create(['email' => '']);

    $unreachable = HandsOnRegistration::factory()->create([
        'seminar_registration_id' => $seminarRegistration->id,
        'hands_on_id' => HandsOn::first()->id,
        'email' => '',
    ]);

    livewire(ListHandsOnRegistrations::class)
        ->callTableBulkAction('bulkSendEmailConfirmation', [$unreachable])
        ->assertHasNoTableBulkActionErrors();

    Mail::assertNotQueued(HandsOnRegistrationConfirmation::class);
});
