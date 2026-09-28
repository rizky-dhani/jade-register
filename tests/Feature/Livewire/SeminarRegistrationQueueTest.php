<?php

use App\Jobs\CompleteSeminarRegistration;
use App\Livewire\SeminarRegistration as SeminarRegistrationComponent;
use App\Models\Country;
use App\Models\Seminar;
use App\Models\SeminarRegistration;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;

use function Pest\Livewire\livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    Country::factory()->indonesia()->create(['id' => 1]);

    Seminar::create([
        'code' => 'snack-only',
        'name' => 'Seminar Snack Only',
        'currency' => 'IDR',
        'amount' => 600000,
        'original_price' => 900000,
        'applies_to' => 'local',
        'is_active' => true,
    ]);
});

it('redirects to success and queues QR/email completion after registration commit', function () {
    Bus::fake();

    livewire(SeminarRegistrationComponent::class)
        ->set('is_already_registered', 'no')
        ->set('country_id', 1)
        ->set('is_local', true)
        ->set('email', 'registrant@example.com')
        ->set('name_license', 'Test Registrant')
        ->set('nik', '1234567890123456')
        ->set('pdgi_branch', 'Jakarta Pusat')
        ->set('kompetensi', 'Dokter Gigi Umum')
        ->set('phone', '081234567890')
        ->set('selected_seminar', 'snack-only')
        ->set('payment_proof_uploaded', true)
        ->set('payment_proof_path', 'payment-proofs/test.jpg')
        ->call('submit')
        ->assertRedirect();

    $registration = SeminarRegistration::query()->where('email', 'registrant@example.com')->firstOrFail();

    Bus::assertDispatched(CompleteSeminarRegistration::class, fn (CompleteSeminarRegistration $job): bool => $job->registration->is($registration));
});
