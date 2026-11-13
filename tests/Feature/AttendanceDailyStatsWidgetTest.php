<?php

use App\Filament\Resources\Attendances\Widgets\AttendanceDailyStatsWidget;
use App\Models\Attendance;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
    $this->staff = User::factory()->create();
    $this->staff->assignRole('Admin');
});

it('shows per-day check-in and check-out totals excluding cancelled and hands-on attendances', function () {
    $this->actingAs($this->staff);

    Attendance::factory()->create([
        'activity_type' => 'seminar',
        'day' => 1,
        'checked_out_at' => now(),
    ]);
    Attendance::factory()->create([
        'activity_type' => 'seminar',
        'day' => 1,
        'checked_out_at' => null,
    ]);
    Attendance::factory()->create([
        'activity_type' => 'seminar',
        'day' => 1,
        'cancelled_at' => now(),
    ]);
    Attendance::factory()->create([
        'activity_type' => 'seminar',
        'day' => 2,
    ]);
    Attendance::factory()->create([
        'activity_type' => 'hands_on',
        'day' => 1,
    ]);

    Livewire::test(AttendanceDailyStatsWidget::class)
        ->assertSee('Day 1')
        ->assertSee('2 / 1')
        ->assertSee('Day 2')
        ->assertSee('1 / 0')
        ->assertSee('Day 3')
        ->assertSee('0 / 0');
});
