<?php

use App\Enums\HandsOnStatus;
use App\Filament\Resources\DigitalWorkshops\Pages\CreateDigitalWorkshop;
use App\Filament\Resources\DigitalWorkshops\Pages\ListDigitalWorkshops;
use App\Models\DigitalWorkshop;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;

use function Pest\Laravel\actingAs;
use function Pest\Livewire\livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);

    $user = User::factory()->create();
    $user->assignRole('Super Admin');
    actingAs($user);

    $this->makeAdminUser = function (): User {
        $admin = User::factory()->create();
        $admin->assignRole('Admin');

        return $admin;
    };
});
test('lists workshops', function () {
    $workshop = DigitalWorkshop::factory()->create();

    livewire(ListDigitalWorkshops::class)
        ->assertOk()
        ->assertCanSeeTableRecords([$workshop]);
});

test('creates a workshop with required fields', function () {
    livewire(CreateDigitalWorkshop::class)
        ->fillForm([
            'name' => 'Digital Workshop Batch 1',
            'code' => 'DW-02',
            'event_date' => '2026-11-21',
            'event_time' => '09:00',
            'event_end_time' => '12:00',
            'price' => 1199000,
            'bundle_price' => 999000,
            'sort_order' => 0,
            'status' => HandsOnStatus::DRAFT->value,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    expect(DigitalWorkshop::where('code', 'DW-02')->exists())->toBeTrue();
});

test('rejects a duplicate code', function () {
    DigitalWorkshop::factory()->create(['code' => 'DW-01']);

    livewire(CreateDigitalWorkshop::class)
        ->fillForm([
            'name' => 'Duplicate',
            'code' => 'DW-01',
            'event_date' => '2026-11-21',
            'event_time' => '09:00',
            'event_end_time' => '12:00',
        ])
        ->call('create')
        ->assertHasFormErrors(['code']);
});

test('requires an event date', function () {
    livewire(CreateDigitalWorkshop::class)
        ->fillForm([
            'name' => 'No Date',
            'code' => 'DW-03',
            'event_time' => '09:00',
            'event_end_time' => '12:00',
        ])
        ->call('create')
        ->assertHasFormErrors(['event_date']);
});

test('rejects a non-numeric price', function () {
    livewire(CreateDigitalWorkshop::class)
        ->fillForm([
            'name' => 'Bad Price',
            'code' => 'DW-04',
            'event_date' => '2026-11-21',
            'event_time' => '09:00',
            'event_end_time' => '12:00',
            'price' => '1.199.000',
        ])
        ->call('create')
        ->assertHasFormErrors(['price']);
});

test('accepts a zero price', function () {
    livewire(CreateDigitalWorkshop::class)
        ->fillForm([
            'name' => 'Free Session',
            'code' => 'DW-05',
            'event_date' => '2026-11-21',
            'event_time' => '09:00',
            'event_end_time' => '12:00',
            'price' => 0,
            'bundle_price' => 0,
            'sort_order' => 0,
            'status' => HandsOnStatus::DRAFT->value,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    expect(DigitalWorkshop::where('code', 'DW-05')->first()?->price)->toBe(0);
});

test('rejects an end time before the start time', function () {
    livewire(CreateDigitalWorkshop::class)
        ->fillForm([
            'name' => 'Backwards',
            'code' => 'DW-06',
            'event_date' => '2026-11-21',
            'event_time' => '12:00',
            'event_end_time' => '09:00',
        ])
        ->call('create')
        ->assertHasFormErrors(['event_end_time']);
});

test('publishes a draft workshop', function () {
    $workshop = DigitalWorkshop::factory()->create(['status' => HandsOnStatus::DRAFT]);

    livewire(ListDigitalWorkshops::class)
        ->callTableAction('publish', $workshop);

    expect($workshop->refresh()->status)->toBe(HandsOnStatus::PUBLISHED);
});

test('hides the publish action for an already published workshop', function () {
    $workshop = DigitalWorkshop::factory()->create(['status' => HandsOnStatus::PUBLISHED]);

    livewire(ListDigitalWorkshops::class)
        ->assertTableActionHidden('publish', $workshop);
});

test('denies an Admin the force delete ability', function () {
    $workshop = DigitalWorkshop::factory()->create();
    $admin = ($this->makeAdminUser)();

    // Gate::before grants Super Admin every check, so assert through an explicit
    // Admin user rather than the actingAs() session set in beforeEach.
    expect(Gate::forUser($admin)->denies('forceDelete', $workshop))->toBeTrue();
});

test('allows an Admin to view and update', function () {
    $workshop = DigitalWorkshop::factory()->create();
    $admin = ($this->makeAdminUser)();

    expect(Gate::forUser($admin)->allows('viewAny', DigitalWorkshop::class))->toBeTrue();
    expect(Gate::forUser($admin)->allows('update', $workshop))->toBeTrue();
});

test('denies a user without the role any access', function () {
    $workshop = DigitalWorkshop::factory()->create();
    $outsider = User::factory()->create();

    expect(Gate::forUser($outsider)->denies('viewAny', DigitalWorkshop::class))->toBeTrue();
    expect(Gate::forUser($outsider)->denies('update', $workshop))->toBeTrue();
});
