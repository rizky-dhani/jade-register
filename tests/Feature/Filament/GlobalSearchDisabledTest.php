<?php

use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\actingAs;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);

    $this->user = User::factory()->create();
    $this->user->assignRole('Super Admin');
    actingAs($this->user);

    Filament::setCurrentPanel('dashboard');
});

it('disables global search on the dashboard panel', function () {
    expect(Filament::getGlobalSearchProvider())->toBeNull()
        ->and(Filament::isGlobalSearchEnabled())->toBeFalse();
});

it('does not render the global search bar in the topbar', function () {
    $this->get(Filament::getUrl())
        ->assertSuccessful()
        ->assertDontSee('fi-global-search', escape: false);
});
