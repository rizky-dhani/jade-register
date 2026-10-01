<?php

use App\Filament\Resources\SeminarRegistrations\Pages\ViewSeminarRegistration;
use App\Mail\SeminarRegistrationConfirmation;
use App\Models\Country;
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

    Country::factory()->indonesia()->create();

    $this->user = User::factory()->create();
    $this->user->assignRole('Super Admin');
    $this->actingAs($this->user);
});

it('renders the send confirmation email action on the view page', function () {
    $registration = SeminarRegistration::factory()->create();

    $this->get(ViewSeminarRegistration::getUrl(['record' => $registration]))
        ->assertSuccessful()
        ->assertSee('Resend Email Confirmation');
});

it('sends the confirmation email when the action is triggered', function () {
    Mail::fake();

    $registration = SeminarRegistration::factory()->create([
        'email' => 'participant@example.com',
        'language' => 'en',
    ]);

    livewire(ViewSeminarRegistration::class, ['record' => $registration->getKey()])
        ->callAction('sendConfirmationEmail')
        ->assertHasNoActionErrors();

    Mail::assertSent(SeminarRegistrationConfirmation::class, function ($mail) use ($registration) {
        return $mail->registration->is($registration)
            && $mail->hasTo($registration->email);
    });
});
