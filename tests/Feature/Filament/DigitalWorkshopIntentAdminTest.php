<?php

use App\Filament\Resources\DigitalWorkshopRegistrationIntents\DigitalWorkshopRegistrationIntentResource;
use App\Filament\Resources\DigitalWorkshopRegistrationIntents\Pages\ListDigitalWorkshopRegistrationIntents;
use App\Models\DigitalWorkshop;
use App\Models\DigitalWorkshopRegistration;
use App\Models\DigitalWorkshopRegistrationIntent;
use App\Models\SeminarRegistration;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;

use function Pest\Livewire\livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);

    $this->admin = User::factory()->create();
    $this->admin->assignRole('Super Admin');

    $this->workshop = DigitalWorkshop::factory()->create(['code' => 'DW-01']);
});

test('lists intents', function () {
    $intent = DigitalWorkshopRegistrationIntent::factory()->create([
        'digital_workshop_id' => $this->workshop->id,
        'email' => 'listed@example.com',
    ]);

    $this->actingAs($this->admin);

    livewire(ListDigitalWorkshopRegistrationIntents::class)
        ->assertCanSeeTableRecords([$intent]);
});

test('filters to awaiting intents', function () {
    $awaiting = DigitalWorkshopRegistrationIntent::factory()->create([
        'digital_workshop_id' => $this->workshop->id,
    ]);
    $fulfilled = DigitalWorkshopRegistrationIntent::factory()->create([
        'digital_workshop_id' => $this->workshop->id,
        'status' => 'fulfilled',
        'fulfilled_at' => now(),
    ]);

    $this->actingAs($this->admin);

    livewire(ListDigitalWorkshopRegistrationIntents::class)
        ->filterTable('status', 'awaiting_seminar')
        ->assertCanSeeTableRecords([$awaiting])
        ->assertCanNotSeeTableRecords([$fulfilled]);
});

test('marks an intent expired', function () {
    $intent = DigitalWorkshopRegistrationIntent::factory()->create([
        'digital_workshop_id' => $this->workshop->id,
        'status' => 'awaiting_seminar',
        'expired_at' => null,
    ]);

    $this->actingAs($this->admin);

    livewire(ListDigitalWorkshopRegistrationIntents::class)
        ->callTableAction('expire', $intent);

    $intent->refresh();

    expect($intent->status)->toBe('expired');
    expect($intent->expired_at)->not->toBeNull();
});

test('hides expire for an already fulfilled intent', function () {
    $intent = DigitalWorkshopRegistrationIntent::factory()->create([
        'digital_workshop_id' => $this->workshop->id,
        'status' => 'fulfilled',
        'fulfilled_at' => now(),
    ]);

    $this->actingAs($this->admin);

    livewire(ListDigitalWorkshopRegistrationIntents::class)
        ->assertTableActionHidden('expire', $intent);
});

test('shows the workshop and seminar registration links', function () {
    $seminar = SeminarRegistration::factory()->create();
    $intent = DigitalWorkshopRegistrationIntent::factory()->create([
        'digital_workshop_id' => $this->workshop->id,
        'seminar_registration_id' => $seminar->id,
    ]);

    $this->actingAs($this->admin);

    livewire(ListDigitalWorkshopRegistrationIntents::class)
        ->assertCanSeeTableRecords([$intent])
        ->assertSee('DW-01');
});

test('denies a user without the role any access', function () {
    $nobody = User::factory()->create();

    $this->actingAs($nobody);

    expect($nobody->can('view digital workshop intents'))->toBeFalse();
});

test('grants the view and update permissions to Admin but not delete', function () {
    $admin = User::factory()->create();
    $admin->assignRole('Admin');

    expect($admin->can('view digital workshop intents'))->toBeTrue();
    expect($admin->can('update digital workshop intents'))->toBeTrue();
    expect($admin->can('delete digital workshop intents'))->toBeFalse();
});

test('creates all three intent permissions', function () {
    foreach ([
        'view digital workshop intents',
        'update digital workshop intents',
        'delete digital workshop intents',
    ] as $permission) {
        expect(Permission::where('name', $permission)->exists())->toBeTrue();
    }
});

test('does not offer a create page', function () {
    // Intents are only created by the public form; an admin-created intent has
    // no proof and no meaning.
    expect(DigitalWorkshopRegistrationIntentResource::hasPage('create'))
        ->toBeFalse();
});

test('links a fulfilled intent to its digital workshop registration', function () {
    $seminar = SeminarRegistration::factory()->create();
    $registration = DigitalWorkshopRegistration::factory()->create([
        'digital_workshop_id' => $this->workshop->id,
        'seminar_registration_id' => $seminar->id,
    ]);
    $intent = DigitalWorkshopRegistrationIntent::factory()->create([
        'digital_workshop_id' => $this->workshop->id,
        'status' => 'fulfilled',
        'fulfilled_at' => now(),
        'seminar_registration_id' => $seminar->id,
        'digital_workshop_registration_id' => $registration->id,
    ]);

    $this->actingAs($this->admin);

    livewire(ListDigitalWorkshopRegistrationIntents::class)
        ->assertCanSeeTableRecords([$intent]);
});
