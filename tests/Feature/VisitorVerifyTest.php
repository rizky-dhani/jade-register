<?php

use App\Filament\Resources\Attendances\Pages\ListVisitorAttendances;
use App\Livewire\VisitorVerify;
use App\Models\User;
use App\Models\Visitor;
use App\Models\VisitorAttendance;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

use function Pest\Laravel\actingAs;
use function Pest\Livewire\livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);

    $this->visitor = Visitor::create([
        'name' => 'Rina Anggraini',
        'email' => 'rina.anggraini@example.test',
        'phone' => '+6281290000001',
        'affiliation' => 'Universitas Indonesia',
        'qr_token' => Str::random(64),
    ]);

    $this->admin = User::factory()->create();
    $this->admin->assignRole('Admin');
});

it('records a visitor attendance row when an admin confirms the check-in', function () {
    actingAs($this->admin);

    livewire(VisitorVerify::class, ['token' => $this->visitor->qr_token])
        ->call('confirmAttendance')
        ->assertSet('showSuccess', true)
        ->assertSet('isAlreadyScanned', true);

    expect($this->visitor->refresh()->scanned_at)->not->toBeNull();

    $this->assertDatabaseHas('visitor_attendances', [
        'visitor_id' => $this->visitor->id,
        'checked_in_by' => $this->admin->id,
    ]);
});

it('lets a super admin confirm the check-in', function () {
    $superAdmin = User::factory()->create();
    $superAdmin->assignRole('Super Admin');

    actingAs($superAdmin);

    livewire(VisitorVerify::class, ['token' => $this->visitor->qr_token])
        ->call('confirmAttendance')
        ->assertHasNoErrors();

    $this->assertDatabaseHas('visitor_attendances', [
        'visitor_id' => $this->visitor->id,
        'checked_in_by' => $superAdmin->id,
    ]);
});

it('rejects the check-in from a user without the admin role', function () {
    $participant = User::factory()->create();
    $participant->assignRole('Participant');

    actingAs($participant);

    livewire(VisitorVerify::class, ['token' => $this->visitor->qr_token])
        ->call('confirmAttendance')
        ->assertHasErrors(['auth']);

    expect($this->visitor->refresh()->scanned_at)->toBeNull();
    $this->assertDatabaseCount('visitor_attendances', 0);
});

it('records a single attendance row when the same visitor is confirmed twice', function () {
    actingAs($this->admin);

    livewire(VisitorVerify::class, ['token' => $this->visitor->qr_token])
        ->call('confirmAttendance');

    livewire(VisitorVerify::class, ['token' => $this->visitor->qr_token])
        ->assertSee(__('seminar.visitor_already_scanned'))
        ->call('confirmAttendance');

    $this->assertDatabaseCount('visitor_attendances', 1);
});

it('shows the invalid message for an unknown token', function () {
    actingAs($this->admin);

    livewire(VisitorVerify::class, ['token' => 'not-a-real-token'])
        ->assertSee(__('seminar.qr_code_invalid'));
});

it('lists the check-in on the visitor attendance page', function () {
    $superAdmin = User::factory()->create();
    $superAdmin->assignRole('Super Admin');

    actingAs($superAdmin);

    livewire(VisitorVerify::class, ['token' => $this->visitor->qr_token])
        ->call('confirmAttendance');

    livewire(ListVisitorAttendances::class)
        ->assertOk()
        ->assertCanSeeTableRecords(VisitorAttendance::all());
});
