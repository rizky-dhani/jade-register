<?php

use App\Filament\Resources\Attendances\Pages\ListAttendanceRoster;
use App\Filament\Resources\HandsOnRegistrations\Pages\ListHandsOnRegistrations;
use App\Filament\Resources\SeminarRegistrations\Pages\ListSeminarRegistrations;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\actingAs;
use function Pest\Livewire\livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);

    $this->user = User::factory()->create();
    $this->user->assignRole('Super Admin');
    actingAs($this->user);
});

it('applies filters through the table apply action', function (string $page) {
    $applyAction = livewire($page)->instance()->getTable()->getFiltersApplyAction();

    expect($applyAction->getCustomAlpineClickHandler())->toBeNull()
        ->and($applyAction->getLivewireClickHandler())->toBe('applyTableFilters');
})->with([
    'attendance' => ListAttendanceRoster::class,
    'seminar registrations' => ListSeminarRegistrations::class,
    'hands-on registrations' => ListHandsOnRegistrations::class,
]);
