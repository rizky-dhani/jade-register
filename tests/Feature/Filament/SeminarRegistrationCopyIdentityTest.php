<?php

use App\Filament\Resources\SeminarRegistrations\Actions\CopyIdentityAction;
use App\Filament\Resources\SeminarRegistrations\Pages\ViewSeminarRegistration;
use App\Filament\Resources\SeminarRegistrations\SeminarRegistrationResource;
use App\Models\Country;
use App\Models\Seminar;
use App\Models\SeminarRegistration;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    Role::create(['name' => 'Super Admin', 'guard_name' => 'web']);

    Setting::create([
        'key' => 'max_participants',
        'value' => '1000',
        'type' => 'integer',
    ]);

    $this->user = User::factory()->create();
    $this->user->assignRole('Super Admin');
    $this->actingAs($this->user);
});

it('renders the copy identity action on the view page', function () {
    $registration = SeminarRegistration::factory()->create();

    $this->get(ViewSeminarRegistration::getUrl(['record' => $registration]))
        ->assertSuccessful()
        ->assertSee('Copy Identity');
});

it('renders the copy identity action on the table listing', function () {
    SeminarRegistration::factory()->create();

    $this->get(SeminarRegistrationResource::getUrl('index'))
        ->assertSuccessful();
});

it('builds a structured copy text containing personal information and chosen package', function () {
    $seminar = Seminar::create([
        'name' => 'Main Seminar',
        'code' => 'MS',
        'original_price' => 1500000,
        'currency' => 'IDR',
        'is_active' => true,
    ]);

    $country = Country::factory()->indonesia()->create();

    $registration = SeminarRegistration::factory()->create([
        'country_id' => $country->id,
        'seminar_id' => $seminar->id,
        'registration_code' => 'JADE-SEM-2026-000001',
        'name_license' => 'Dr. Jane Doe',
        'email' => 'jane@example.com',
        'phone' => '081234567890',
        'nik' => '1234567890123456',
        'pdgi_branch' => 'Jakarta',
        'kompetensi' => 'General Dentist',
        'status' => 'Dentist',
        'amount' => 1500000,
        'currency' => 'IDR',
    ]);

    $action = CopyIdentityAction::make('copyIdentity');
    $action->record($registration);

    $handler = $action->getCustomAlpineClickHandler();

    expect($handler)->not->toBeNull()
        ->and($handler)->toContain('navigator.clipboard.writeText');

    // The structured text should be JSON-encoded inside the JS handler
    expect($handler)->toContain('Dr. Jane Doe')
        ->and($handler)->toContain('jane@example.com')
        ->and($handler)->toContain('081234567890')
        ->and($handler)->toContain('1234567890123456')
        ->and($handler)->toContain('Jakarta')
        ->and($handler)->toContain('Main Seminar');
});
