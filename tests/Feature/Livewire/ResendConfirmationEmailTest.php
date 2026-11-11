<?php

use App\Livewire\ResendConfirmationEmail;
use App\Mail\HandsOnRegistrationConfirmation;
use App\Mail\SeminarRegistrationConfirmation;
use App\Models\HandsOnRegistration;
use App\Models\SeminarRegistration;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;

use function Pest\Livewire\livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    Mail::fake();
});

it('renders the public resend page', function () {
    $this->get(route('resend.confirmation'))
        ->assertOk()
        ->assertSee(__('seminar.resend_confirmation_title'));
});

it('resends the seminar confirmation and stamps the sent timestamp', function () {
    $registration = SeminarRegistration::factory()->create([
        'email' => 'participant@example.com',
        'name_license' => 'Dr. Participant',
        'confirmation_email_sent_at' => null,
    ]);

    livewire(ResendConfirmationEmail::class)
        ->set('email', 'participant@example.com')
        ->call('submit')
        ->assertSet('isSuccess', true);

    Mail::assertSent(SeminarRegistrationConfirmation::class, function ($mail) use ($registration) {
        return $mail->registration->is($registration);
    });

    expect($registration->fresh()->confirmation_email_sent_at)->not->toBeNull();
});

it('resends hands-on confirmations for the same email', function () {
    $seminar = SeminarRegistration::factory()->create([
        'email' => 'participant@example.com',
        'name_license' => 'Dr. Participant',
    ]);

    $handsOn = HandsOnRegistration::factory()->create([
        'seminar_registration_id' => $seminar->id,
        'email' => null,
        'name_license' => 'Dr. Participant',
    ]);

    livewire(ResendConfirmationEmail::class)
        ->set('email', 'participant@example.com')
        ->call('submit')
        ->assertSet('isSuccess', true);

    Mail::assertSent(HandsOnRegistrationConfirmation::class, function ($mail) use ($handsOn) {
        return $mail->registration->is($handsOn);
    });
});

it('shows the same success state for an unknown email and sends nothing', function () {
    livewire(ResendConfirmationEmail::class)
        ->set('email', 'nobody@example.com')
        ->call('submit')
        ->assertSet('isSuccess', true);

    Mail::assertNothingSent();
});

it('throttles repeated attempts', function () {
    SeminarRegistration::factory()->create([
        'email' => 'participant@example.com',
        'name_license' => 'Dr. Participant',
    ]);

    for ($i = 0; $i < 5; $i++) {
        livewire(ResendConfirmationEmail::class)
            ->set('email', 'participant@example.com')
            ->call('submit');
    }

    livewire(ResendConfirmationEmail::class)
        ->set('email', 'participant@example.com')
        ->call('submit')
        ->assertSet('isSuccess', false)
        ->assertSet('errorMessage', __('seminar.resend_confirmation_throttle_error'));
});
