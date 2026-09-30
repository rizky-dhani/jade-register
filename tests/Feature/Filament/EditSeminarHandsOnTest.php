<?php

use App\Enums\HandsOnStatus;
use App\Filament\Resources\SeminarRegistrations\Pages\EditSeminarRegistration;
use App\Models\Country;
use App\Models\HandsOn;
use App\Models\HandsOnRegistration;
use App\Models\Seminar;
use App\Models\SeminarRegistration;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;

use function Pest\Laravel\actingAs;
use function Pest\Livewire\livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    Role::create(['name' => 'Super Admin', 'guard_name' => 'web']);

    $this->user = User::factory()->create();
    $this->user->assignRole('Super Admin');
    actingAs($this->user);

    Country::create(['id' => 1, 'name' => 'Indonesia', 'code' => 'ID', 'is_indonesia' => true, 'phone_code' => '62']);
    Setting::create(['key' => 'max_participants', 'value' => 100, 'type' => 'integer']);

    $this->seminar = Seminar::create([
        'name' => 'Test Seminar',
        'code' => 'TEST-SEM',
        'applies_to' => 'local',
        'original_price' => 1000000,
        'max_seats' => 100,
        'currency' => 'IDR',
        'is_active' => true,
        'sort_order' => 1,
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

it('prefills wants_hands_on when the registration has hands-on', function () {
    $registration = SeminarRegistration::factory()->create([
        'country_id' => 1,
        'wants_hands_on' => true,
        'kompetensi' => 'Dokter Gigi Umum',
        'seminar_id' => $this->seminar->id,
        'selected_seminar' => $this->seminar->name,
    ]);

    HandsOnRegistration::create([
        'seminar_registration_id' => $registration->id,
        'hands_on_id' => $this->handsOn->id,
        'registration_type' => 'combined',
        'payment_status' => 'pending',
    ]);

    livewire(EditSeminarRegistration::class, ['record' => $registration->getRouteKey()])
        ->assertOk()
        ->assertSchemaStateSet([
            'wants_hands_on' => true,
            'hands_on_sessions' => [$this->handsOn->id],
        ]);
});

it('persists hands-on selection changes on save', function () {
    $secondHandsOn = HandsOn::create([
        'name' => 'Test Hands On Day 2',
        'ho_code' => 'HO-002',
        'doctor_name' => 'Dr. Test',
        'description' => 'Test description',
        'event_date' => '2026-11-14',
        'max_seats' => 10,
        'price' => 400000,
        'original_price' => 400000,
        'currency' => 'IDR',
        'is_active' => true,
        'status' => HandsOnStatus::PUBLISHED,
    ]);

    $registration = SeminarRegistration::factory()->create([
        'country_id' => 1,
        'wants_hands_on' => true,
        'kompetensi' => 'Dokter Gigi Umum',
        'seminar_id' => $this->seminar->id,
        'selected_seminar' => $this->seminar->name,
    ]);

    HandsOnRegistration::create([
        'seminar_registration_id' => $registration->id,
        'hands_on_id' => $this->handsOn->id,
        'registration_type' => 'combined',
        'payment_status' => 'pending',
    ]);

    livewire(EditSeminarRegistration::class, ['record' => $registration->getRouteKey()])
        ->fillForm([
            'hands_on_sessions' => [$secondHandsOn->id],
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $registration->refresh();

    expect($registration->handsOnRegistrations()->pluck('hands_on_id')->all())
        ->toEqual([$secondHandsOn->id])
        ->and($registration->hands_on_total_amount)->toBe(400000);
});

it('removes hands-on registrations when wants_hands_on is unchecked', function () {
    $registration = SeminarRegistration::factory()->create([
        'country_id' => 1,
        'wants_hands_on' => true,
        'kompetensi' => 'Dokter Gigi Umum',
        'seminar_id' => $this->seminar->id,
        'selected_seminar' => $this->seminar->name,
    ]);

    HandsOnRegistration::create([
        'seminar_registration_id' => $registration->id,
        'hands_on_id' => $this->handsOn->id,
        'registration_type' => 'combined',
        'payment_status' => 'pending',
    ]);

    livewire(EditSeminarRegistration::class, ['record' => $registration->getRouteKey()])
        ->fillForm([
            'wants_hands_on' => false,
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $registration->refresh();

    expect($registration->handsOnRegistrations()->count())->toBe(0)
        ->and($registration->hands_on_total_amount)->toBe(0);
});
