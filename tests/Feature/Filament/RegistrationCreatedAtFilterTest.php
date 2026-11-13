<?php

use App\Filament\Resources\HandsOnRegistrations\Pages\ListHandsOnRegistrations;
use App\Filament\Resources\SeminarRegistrations\Pages\ListSeminarRegistrations;
use App\Models\HandsOn;
use App\Models\HandsOnRegistration;
use App\Models\SeminarRegistration;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\actingAs;
use function Pest\Livewire\livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);

    $user = User::factory()->create();
    $user->assignRole('Super Admin');
    actingAs($user);
});

/**
 * created_at is not fillable, so it is forced after creation to keep the fixture
 * deterministic.
 */
function stampRegisteredAt(Model $record, string $at): Model
{
    $record->forceFill(['created_at' => $at])->save();

    return $record;
}

it('shows the created at column on the seminar registrations table', function () {
    SeminarRegistration::factory()->create();

    livewire(ListSeminarRegistrations::class)
        ->assertOk()
        ->assertSee(__('filament.seminar_registrations.created_at'));
});

it('filters seminar registrations by creation date range', function () {
    $inside = stampRegisteredAt(SeminarRegistration::factory()->create(), '2026-11-10 09:00:00');
    $after = stampRegisteredAt(SeminarRegistration::factory()->create(), '2026-11-20 09:00:00');
    $before = stampRegisteredAt(SeminarRegistration::factory()->create(), '2026-10-01 09:00:00');

    livewire(ListSeminarRegistrations::class)
        ->filterTable('created_at', [
            'created_from' => '2026-11-01',
            'created_until' => '2026-11-15',
        ])
        ->assertCanSeeTableRecords([$inside])
        ->assertCanNotSeeTableRecords([$after, $before]);
});

it('shows the created at column on the hands-on registrations table', function () {
    $handsOn = HandsOn::factory()->create();
    HandsOnRegistration::factory()->create(['hands_on_id' => $handsOn->id]);

    livewire(ListHandsOnRegistrations::class)
        ->assertOk()
        ->assertSee(__('filament.hands_on_registrations.created_at'));
});

it('filters hands-on registrations by creation date range', function () {
    $handsOn = HandsOn::factory()->create();

    $inside = stampRegisteredAt(
        HandsOnRegistration::factory()->create(['hands_on_id' => $handsOn->id]),
        '2026-11-10 09:00:00',
    );
    $after = stampRegisteredAt(
        HandsOnRegistration::factory()->create(['hands_on_id' => $handsOn->id]),
        '2026-11-20 09:00:00',
    );
    $before = stampRegisteredAt(
        HandsOnRegistration::factory()->create(['hands_on_id' => $handsOn->id]),
        '2026-10-01 09:00:00',
    );

    livewire(ListHandsOnRegistrations::class)
        ->filterTable('created_at', [
            'created_from' => '2026-11-01',
            'created_until' => '2026-11-15',
        ])
        ->assertCanSeeTableRecords([$inside])
        ->assertCanNotSeeTableRecords([$after, $before]);
});
