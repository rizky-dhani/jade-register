<?php

use App\Filament\Resources\Attendances\AttendanceResource;
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

it('never searches attendances by a non-existent name column', function () {
    expect(AttendanceResource::canGloballySearch())->toBeFalse()
        ->and(AttendanceResource::getGloballySearchableAttributes())->toBe([]);
});
