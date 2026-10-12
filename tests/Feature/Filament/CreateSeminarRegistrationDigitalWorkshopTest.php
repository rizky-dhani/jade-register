<?php

use App\Filament\Resources\SeminarRegistrations\Pages\CreateSeminarRegistration;
use App\Models\Country;
use App\Models\DigitalWorkshop;
use App\Models\Seminar;
use App\Models\SeminarRegistration;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\actingAs;
use function Pest\Livewire\livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);

    Country::factory()->indonesia()->create(['id' => 1]);
    Seminar::create([
        'code' => 'test-seminar',
        'name' => 'Test Seminar',
        'currency' => 'IDR',
        'original_price' => 500000,
        'applies_to' => 'local',
        'is_active' => true,
    ]);

    $user = User::factory()->create();
    $user->assignRole('Super Admin');
    actingAs($user);
});

it('creates a bundled digital workshop registration when the checkbox is set', function () {
    $workshop = DigitalWorkshop::factory()->create(['status' => 'published']);

    livewire(CreateSeminarRegistration::class)
        ->fillForm([
            'country_id' => 1,
            'seminar_id' => 1,
            'name_license' => 'Dr. Test',
            'email' => 'test@example.com',
            'phone' => '081200000000',
            'nik' => '3200000000000001',
            'pdgi_branch' => 'PDGI Jakarta',
            'kompetensi' => 'Dokter Gigi Umum',
            'registration_type' => 'regular',
            'payment_method' => 'bank_transfer',
            'amount' => 500000,
            'wants_digital_workshop' => true,
            'digital_workshop_id' => $workshop->id,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $registration = SeminarRegistration::where('email', 'test@example.com')->firstOrFail();

    expect($registration->digitalWorkshopRegistrations)->toHaveCount(1);

    $dw = $registration->digitalWorkshopRegistrations->first();
    expect($dw->digital_workshop_id)->toBe($workshop->id)
        ->and($dw->registration_type)->toBe('bundled')
        ->and($dw->payment_status)->toBe('pending');
});

it('does not create a digital workshop registration without the checkbox', function () {
    $workshop = DigitalWorkshop::factory()->create(['status' => 'published']);

    livewire(CreateSeminarRegistration::class)
        ->fillForm([
            'country_id' => 1,
            'seminar_id' => 1,
            'name_license' => 'Dr. Test',
            'email' => 'test2@example.com',
            'phone' => '081200000001',
            'nik' => '3200000000000002',
            'pdgi_branch' => 'PDGI Jakarta',
            'kompetensi' => 'Dokter Gigi Umum',
            'registration_type' => 'regular',
            'payment_method' => 'bank_transfer',
            'amount' => 500000,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $registration = SeminarRegistration::where('email', 'test2@example.com')->firstOrFail();

    expect($registration->digitalWorkshopRegistrations)->toHaveCount(0);
});
