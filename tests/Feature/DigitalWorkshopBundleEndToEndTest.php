<?php

use App\Jobs\CompleteDigitalWorkshopRegistration;
use App\Jobs\FulfillDigitalWorkshopIntent;
use App\Livewire\DigitalWorkshopRegistration as DigitalWorkshopRegistrationForm;
use App\Livewire\SeminarRegistration as SeminarRegistrationForm;
use App\Models\Country;
use App\Models\DigitalWorkshop;
use App\Models\DigitalWorkshopRegistration;
use App\Models\DigitalWorkshopRegistrationIntent;
use App\Models\Seminar;
use App\Models\SeminarRegistration;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;

use function Pest\Livewire\livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    Storage::fake('public');

    Country::factory()->indonesia()->create(['id' => 1]);

    Seminar::create([
        'code' => 'snack-only',
        'name' => 'Seminar Snack Only',
        'currency' => 'IDR',
        'original_price' => 900000,
        'applies_to' => 'local',
        'is_active' => true,
    ]);

    $this->workshop = DigitalWorkshop::factory()->create([
        'status' => 'published',
        'price' => 1199000,
        'bundle_price' => 999000,
        'max_seats' => null,
    ]);
});

/**
 * Step 1-2: the bundle buyer submits the Digital Workshop form and is handed off.
 */
function submitBundleForm(DigitalWorkshop $workshop): DigitalWorkshopRegistrationIntent
{
    livewire(DigitalWorkshopRegistrationForm::class)
        ->set('name_license', 'Dr. Journey')
        ->set('email', 'journey@example.com')
        ->set('phone', '081234567890')
        ->set('nik', '1234567890123456')
        ->set('pdgi_branch', 'Jakarta')
        ->set('kompetensi', 'Dokter Gigi Umum')
        ->set('country_id', 1)
        ->set('payment_method', 'bank_transfer')
        ->set('payment_proof', UploadedFile::fake()->image('proof.jpg'))
        ->set('wantsBundle', true)
        ->call('submit')
        ->assertRedirect();

    return DigitalWorkshopRegistrationIntent::firstOrFail();
}

test('carries a bundle buyer from the digital workshop form to a fulfilled registration', function () {
    Queue::fake();

    // 1-2. Bundle form submits, intent created awaiting, redirected to the seminar form.
    $intent = submitBundleForm($this->workshop);

    expect($intent->isAwaiting())->toBeTrue();
    expect($intent->seminar_registration_id)->toBeNull();
    expect(DigitalWorkshopRegistration::count())->toBe(0);
    expect(SeminarRegistration::count())->toBe(0);

    // 3. The seminar form is reached through the handoff and is prefilled.
    livewire(SeminarRegistrationForm::class, ['dw_intent' => $intent->id])
        ->assertSet('email', 'journey@example.com')
        ->assertSet('name_license', 'Dr. Journey')
        ->assertSet('nik', '1234567890123456')
        ->assertSet('pdgi_branch', 'Jakarta')
        ->set('is_already_registered', 'no')
        ->set('selected_seminar', 'snack-only')
        ->set('payment_proof_uploaded', true)
        ->set('payment_proof_path', 'payment-proofs/seminar.jpg')
        ->call('submit');

    // 4. The seminar registration is pending; still no Digital Workshop registration.
    $registration = SeminarRegistration::query()->where('email', 'journey@example.com')->firstOrFail();

    expect($registration->payment_status)->toBe('pending');
    expect(DigitalWorkshopRegistration::count())->toBe(0);

    // 5. Staff verify the seminar payment.
    $registration->update(['payment_status' => 'verified']);

    // 6. The intent is claimed and the job dispatched (queued, not inline).
    $intent->refresh();
    expect($intent->seminar_registration_id)->toBe($registration->id);

    // Run the queued fulfilment, as the worker would.
    (new FulfillDigitalWorkshopIntent($intent))->handle();

    // 7. The Digital Workshop registration exists at the bundle price, bundled.
    $dwRegistration = DigitalWorkshopRegistration::firstOrFail();

    expect($dwRegistration->amount)->toBe(999000);
    expect($dwRegistration->registration_type)->toBe('bundled');
    expect($dwRegistration->seminar_registration_id)->toBe($registration->id);
    expect($dwRegistration->payment_status)->toBe('pending');
    expect($dwRegistration->email)->toBe('journey@example.com');

    // 8. The intent is consumed and the completion job was queued.
    expect($intent->refresh()->isFulfilled())->toBeTrue();
    Queue::assertPushed(CompleteDigitalWorkshopRegistration::class);
});

test('never creates a seminar registration from digital workshop code', function () {
    // The standing constraint, re-pinned at the journey level.
    Queue::fake();

    $intent = submitBundleForm($this->workshop);

    expect(SeminarRegistration::count())->toBe(0);

    $seminar = SeminarRegistration::factory()->create([
        'country_id' => 1,
        'email' => 'journey@example.com',
        'payment_status' => 'verified',
    ]);

    $intent->update(['seminar_registration_id' => $seminar->id]);
    (new FulfillDigitalWorkshopIntent($intent->refresh()))->handle();

    // The bundle path created a Digital Workshop registration, and exactly the
    // one seminar registration that already existed — nothing added by us.
    expect(DigitalWorkshopRegistration::count())->toBe(1);
    expect(SeminarRegistration::count())->toBe(1);
});

test('leaves the seminar registration amount unchanged through the bundle journey', function () {
    Queue::fake();

    $intent = submitBundleForm($this->workshop);

    $seminar = SeminarRegistration::factory()->create([
        'country_id' => 1,
        'email' => 'journey@example.com',
        'payment_status' => 'verified',
        'amount' => 900000,
    ]);
    $amountBefore = $seminar->amount;

    // Re-verifying must not touch the seminar row's own money fields.
    $seminar->update(['payment_status' => 'pending']);
    $seminar->update(['payment_status' => 'verified']);

    expect($seminar->refresh()->amount)->toBe($amountBefore);

    (new FulfillDigitalWorkshopIntent($intent->refresh()))->handle();

    expect($seminar->refresh()->amount)->toBe($amountBefore);
    expect(DigitalWorkshopRegistration::first()->amount)->toBe(999000);
});

test('a fulfilled bundle does not double-create when the payment is verified again', function () {
    Queue::fake();

    $intent = submitBundleForm($this->workshop);

    $seminar = SeminarRegistration::factory()->create([
        'country_id' => 1,
        'email' => 'journey@example.com',
        'payment_status' => 'verified',
    ]);

    $intent->update(['seminar_registration_id' => $seminar->id]);
    (new FulfillDigitalWorkshopIntent($intent->refresh()))->handle();

    // Simulate an admin toggling the payment status again.
    $seminar->update(['payment_status' => 'pending']);
    $seminar->update(['payment_status' => 'verified']);

    (new FulfillDigitalWorkshopIntent($intent->refresh()))->handle();

    expect(DigitalWorkshopRegistration::count())->toBe(1);
});

test('the standalone path is untouched by the bundle machinery', function () {
    Bus::fake();

    livewire(DigitalWorkshopRegistrationForm::class)
        ->set('name_license', 'Dr. Standalone')
        ->set('email', 'standalone@example.com')
        ->set('phone', '081234567890')
        ->set('nik', '1234567890123456')
        ->set('pdgi_branch', 'Jakarta')
        ->set('kompetensi', 'Dokter Gigi Umum')
        ->set('country_id', 1)
        ->set('payment_method', 'bank_transfer')
        ->set('payment_proof', UploadedFile::fake()->image('proof.jpg'))
        ->set('wantsBundle', false)
        ->call('submit')
        ->assertHasNoErrors();

    $registration = DigitalWorkshopRegistration::firstOrFail();

    expect($registration->amount)->toBe(1199000);
    expect($registration->registration_type)->toBe('standalone');
    expect(DigitalWorkshopRegistrationIntent::count())->toBe(0);
    expect(SeminarRegistration::count())->toBe(0);
});
