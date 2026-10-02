<?php

use App\Livewire\SeminarRegistration as SeminarRegistrationComponent;
use App\Models\Country;
use App\Models\Seminar;
use App\Models\SeminarRegistration;
use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Features\SupportTesting\Testable;

use function Pest\Livewire\livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    Country::factory()->indonesia()->create(['id' => 1]);

    Seminar::create([
        'code' => 'snack-only',
        'name' => 'Seminar Snack Only',
        'currency' => 'IDR',
        'original_price' => 900000,
        'applies_to' => 'local',
        'is_active' => true,
    ]);
});

function makeFullRegistrationForm(): Testable
{
    return livewire(SeminarRegistrationComponent::class)
        ->set('is_already_registered', 'no')
        ->set('country_id', 1)
        ->set('is_local', true)
        ->set('email', 'late@example.com')
        ->set('name_license', 'Late Registrant')
        ->set('nik', '1234567890123456')
        ->set('pdgi_branch', 'Jakarta Pusat')
        ->set('kompetensi', 'Dokter Gigi Umum')
        ->set('phone', '081234567890')
        ->set('selected_seminar', 'snack-only')
        ->set('payment_proof_uploaded', true)
        ->set('payment_proof_path', 'payment-proofs/test.jpg');
}

it('shows the inline full state instead of redirecting when capacity is exhausted', function () {
    Setting::create(['key' => 'max_participants', 'value' => '0', 'type' => 'integer']);

    makeFullRegistrationForm()
        ->call('submit')
        ->assertSet('seminarJustFilled', true)
        ->assertNoRedirect();

    expect(SeminarRegistration::where('email', 'late@example.com')->exists())->toBeFalse();
});

it('does not set the inline full state when registration succeeds', function () {
    Setting::create(['key' => 'max_participants', 'value' => '100', 'type' => 'integer']);

    makeFullRegistrationForm()
        ->call('submit')
        ->assertSet('seminarJustFilled', false)
        ->assertRedirect();

    expect(SeminarRegistration::where('email', 'late@example.com')->exists())->toBeTrue();
});

it('renders the full notice when the component is in the just-filled state', function () {
    // Rendered directly: when capacity is hit at submit time isSeminarFull() is
    // usually also true, so the existing full banner wins. This branch is the
    // fallback for the cases where it is not (e.g. missing max_participants row).
    livewire(SeminarRegistrationComponent::class)
        ->set('seminarJustFilled', true)
        ->assertSee(__('seminar.seminar_just_filled'))
        ->assertDontSee(__('seminar.already_registered'));
});

it('shows a full notice inline rather than redirecting once capacity is hit', function () {
    Setting::create(['key' => 'max_participants', 'value' => '0', 'type' => 'integer']);

    // Either the is-full banner or the just-filled banner is acceptable; the
    // requirement is that the user is told, not bounced with a flash message.
    makeFullRegistrationForm()
        ->call('submit')
        ->assertNoRedirect()
        ->assertSee(__('seminar.seminar_is_full'));
});

it('still allows exactly max_participants registrations', function () {
    Setting::create(['key' => 'max_participants', 'value' => '2', 'type' => 'integer']);

    expect(SeminarRegistration::isSeminarFull())->toBeFalse();

    SeminarRegistration::factory()->create(['payment_status' => 'pending']);
    expect(SeminarRegistration::isSeminarFull())->toBeFalse();

    SeminarRegistration::factory()->create(['payment_status' => 'verified']);
    expect(SeminarRegistration::isSeminarFull())->toBeTrue();
});
