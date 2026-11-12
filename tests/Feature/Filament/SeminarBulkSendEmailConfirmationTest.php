<?php

use App\Filament\Resources\SeminarRegistrations\Pages\ListSeminarRegistrations;
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

it('queues the confirmation email for every selected registration with an email', function () {
    Mail::fake();

    $first = SeminarRegistration::factory()->create(['email' => 'first@example.com', 'language' => 'en']);
    $second = SeminarRegistration::factory()->create(['email' => 'second@example.com', 'language' => 'id']);

    livewire(ListSeminarRegistrations::class)
        ->callTableBulkAction('bulkSendEmailConfirmation', [$first, $second])
        ->assertHasNoTableBulkActionErrors();

    Mail::assertQueued(SeminarRegistrationConfirmation::class, 2);
    Mail::assertQueued(SeminarRegistrationConfirmation::class, fn ($mail) => $mail->hasTo('first@example.com'));
    Mail::assertQueued(SeminarRegistrationConfirmation::class, fn ($mail) => $mail->hasTo('second@example.com'));
});

it('skips selected registrations without an email address', function () {
    Mail::fake();

    $reachable = SeminarRegistration::factory()->create(['email' => 'reachable@example.com']);
    $unreachable = SeminarRegistration::factory()->create(['email' => '']);

    livewire(ListSeminarRegistrations::class)
        ->callTableBulkAction('bulkSendEmailConfirmation', [$reachable, $unreachable])
        ->assertHasNoTableBulkActionErrors();

    Mail::assertQueued(SeminarRegistrationConfirmation::class, 1);
    Mail::assertQueued(SeminarRegistrationConfirmation::class, fn ($mail) => $mail->hasTo('reachable@example.com'));
});

it('verifies selected pending payments and queues the confirmation email', function () {
    Mail::fake();

    $first = SeminarRegistration::factory()->create(['email' => 'first@example.com', 'payment_status' => 'pending']);
    $second = SeminarRegistration::factory()->create(['email' => 'second@example.com', 'payment_status' => 'pending']);

    livewire(ListSeminarRegistrations::class)
        ->callTableBulkAction('bulkVerifyPayment', [$first, $second])
        ->assertHasNoTableBulkActionErrors();

    expect($first->refresh()->payment_status)->toBe('verified')
        ->and($second->refresh()->payment_status)->toBe('verified');

    Mail::assertQueued(SeminarRegistrationConfirmation::class, 2);
    Mail::assertQueued(SeminarRegistrationConfirmation::class, fn ($mail) => $mail->hasTo('first@example.com'));
    Mail::assertQueued(SeminarRegistrationConfirmation::class, fn ($mail) => $mail->hasTo('second@example.com'));
});

it('does not email registrations whose payment was already verified', function () {
    Mail::fake();

    $alreadyVerified = SeminarRegistration::factory()->create([
        'email' => 'verified@example.com',
        'payment_status' => 'verified',
    ]);

    livewire(ListSeminarRegistrations::class)
        ->callTableBulkAction('bulkVerifyPayment', [$alreadyVerified])
        ->assertHasNoTableBulkActionErrors();

    Mail::assertNotQueued(SeminarRegistrationConfirmation::class);
});
