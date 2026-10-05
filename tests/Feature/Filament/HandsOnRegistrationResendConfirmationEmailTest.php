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

it('sends the resend email to the linked seminar registration email', function () {
    Mail::fake();

    $seminarRegistration = SeminarRegistration::factory()->create([
        'email' => 'seminar-owner@example.com',
        'language' => 'id',
    ]);

    $registration = HandsOnRegistration::factory()->create([
        'seminar_registration_id' => $seminarRegistration->id,
        'hands_on_id' => HandsOn::first()->id,
        'email' => null,
        'language' => '',
    ]);

    livewire(ListHandsOnRegistrations::class)
        ->callTableAction('resendEmailConfirmation', $registration)
        ->assertHasNoTableActionErrors();

    Mail::assertSent(HandsOnRegistrationConfirmation::class, function ($mail) {
        return $mail->hasTo('seminar-owner@example.com');
    });
});

it('hides the resend action when no recipient email is available', function () {
    $seminarRegistration = SeminarRegistration::factory()->create(['email' => '']);

    $registration = HandsOnRegistration::factory()->create([
        'seminar_registration_id' => $seminarRegistration->id,
        'hands_on_id' => HandsOn::first()->id,
        'email' => '',
    ]);

    livewire(ListHandsOnRegistrations::class)
        ->assertTableActionHidden('resendEmailConfirmation', $registration);
});
