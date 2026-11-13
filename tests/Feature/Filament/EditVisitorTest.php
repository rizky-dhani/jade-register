<?php

use App\Filament\Resources\Visitors\Pages\ListVisitors;
use App\Models\User;
use App\Models\Visitor;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

use function Pest\Laravel\actingAs;
use function Pest\Livewire\livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);

    $user = User::factory()->create();
    $user->assignRole('Super Admin');
    actingAs($user);
});

function visitor(array $attributes = []): Visitor
{
    return Visitor::create(array_merge([
        'name' => 'Rina Anggraini',
        'email' => 'rina.anggraini@example.test',
        'phone' => '+6281290000001',
        'affiliation' => 'Universitas Indonesia',
        'qr_token' => Str::random(64),
    ], $attributes));
}

it('renders the edit form for a visitor who has not checked in', function () {
    $visitor = visitor();

    livewire(ListVisitors::class)
        ->mountTableAction('edit', $visitor)
        ->assertOk()
        ->assertSchemaStateSet([
            'name' => 'Rina Anggraini',
            'isScanned' => 'No',
            'scanned_at' => null,
        ], 'mountedActionSchema0');
});

it('renders the attendance status entries for a checked-in visitor', function () {
    $visitor = visitor(['scanned_at' => now()]);

    livewire(ListVisitors::class)
        ->mountTableAction('edit', $visitor)
        ->assertOk()
        ->assertSchemaStateSet([
            'isScanned' => 'Yes',
            'scanned_at' => $visitor->scanned_at,
        ], 'mountedActionSchema0');
});
