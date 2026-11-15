<?php

use App\Enums\HandsOnStatus;
use App\Filament\Resources\HandsOnRegistrations\Actions\CopyParticipantDataAction;
use App\Filament\Resources\HandsOnRegistrations\HandsOnRegistrationResource;
use App\Filament\Resources\HandsOnRegistrations\Pages\ViewHandsOnRegistration;
use App\Models\Country;
use App\Models\HandsOn;
use App\Models\HandsOnRegistration;
use App\Models\Seminar;
use App\Models\SeminarRegistration;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    Role::create(['name' => 'Super Admin', 'guard_name' => 'web']);

    Setting::create(['key' => 'max_participants', 'value' => '1000', 'type' => 'integer']);

    $this->user = User::factory()->create();
    $this->user->assignRole('Super Admin');
    $this->actingAs($this->user);

    $this->country = Country::factory()->indonesia()->create();

    $this->seminar = Seminar::create([
        'name' => 'Main Seminar',
        'code' => 'MS',
        'original_price' => 1500000,
        'currency' => 'IDR',
        'is_active' => true,
    ]);

    $this->handsOn = HandsOn::create([
        'name' => 'Test Hands On Day 1',
        'ho_code' => 'HO-001',
        'doctor_name' => 'Dr. Test',
        'description' => 'Test description',
        'event_date' => '2026-11-13',
        'max_seats' => 10,
        'price' => 500000,
        'original_price' => 500000,
        'currency' => 'IDR',
        'is_active' => true,
        'status' => HandsOnStatus::PUBLISHED,
    ]);
});

it('renders the copy participant data action on the view page', function () {
    $registration = HandsOnRegistration::factory()->create([
        'hands_on_id' => $this->handsOn->id,
        'seminar_registration_id' => null,
    ]);

    $this->get(ViewHandsOnRegistration::getUrl(['record' => $registration]))
        ->assertSuccessful()
        ->assertSee('Copy Participant Data');
});

it('renders the hands-on registrations listing with the action', function () {
    HandsOnRegistration::factory()->create(['hands_on_id' => $this->handsOn->id]);

    $this->get(HandsOnRegistrationResource::getUrl('index'))
        ->assertSuccessful();
});

it('copies the participant data from the linked seminar registration', function () {
    $seminarRegistration = SeminarRegistration::factory()->create([
        'country_id' => $this->country->id,
        'seminar_id' => $this->seminar->id,
        'registration_code' => 'JADE-SEM-2026-000123',
        'name_license' => 'Dr. Jane Doe',
        'email' => 'jane@example.com',
        'phone' => '081234567890',
        'nik' => '1234567890123456',
        'pdgi_branch' => 'Jakarta',
        'kompetensi' => 'Dokter Gigi Umum',
        'status' => 'Dentist',
    ]);

    $registration = HandsOnRegistration::factory()->create([
        'hands_on_id' => $this->handsOn->id,
        'seminar_registration_id' => $seminarRegistration->id,
        'registration_code' => 'JADE-HO-2026-000001',
        'name_license' => null,
        'email' => null,
        'phone' => null,
        'nik' => null,
        'pdgi_branch' => null,
        'kompetensi' => null,
    ]);

    $action = CopyParticipantDataAction::make();
    $action->record($registration);

    $handler = $action->getCustomAlpineClickHandler();

    expect($handler)->not->toBeNull()
        ->and($handler)->toContain('navigator.clipboard.writeText')
        ->and($handler)->toContain('JADE-HO-2026-000001')
        ->and($handler)->toContain('JADE-SEM-2026-000123')
        ->and($handler)->toContain('Dr. Jane Doe')
        ->and($handler)->toContain('jane@example.com')
        ->and($handler)->toContain('081234567890')
        ->and($handler)->toContain('1234567890123456')
        ->and($handler)->toContain('Jakarta')
        ->and($handler)->toContain('Test Hands On Day 1');
});

it('falls back to the hands-on row when no seminar registration is linked', function () {
    $registration = HandsOnRegistration::factory()->create([
        'hands_on_id' => $this->handsOn->id,
        'seminar_registration_id' => null,
        'registration_code' => 'JADE-HO-2026-000002',
        'name_license' => 'Dr. Standalone',
        'email' => 'standalone@example.com',
        'phone' => '089999999999',
        'nik' => '9999999999999999',
        'pdgi_branch' => 'Bandung',
        'kompetensi' => 'Dokter Gigi Umum',
        'status' => 'Dentist',
    ]);

    $text = CopyParticipantDataAction::buildParticipantText($registration);

    expect($text)->toContain('Dr. Standalone')
        ->and($text)->toContain('standalone@example.com')
        ->and($text)->toContain('089999999999')
        ->and($text)->toContain('Bandung')
        ->and($text)->not->toContain('Seminar Registration');
});
