<?php

use App\Filament\Resources\Attendances\Widgets\AttendanceDailyStatsWidget;
use App\Models\Attendance;
use App\Models\HandsOn;
use App\Models\HandsOnRegistration;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
    $this->staff = User::factory()->create();
    $this->staff->assignRole('Admin');
});

it('is registered on the dashboard', function () {
    expect(Filament::getPanel('dashboard')->getWidgets())
        ->toContain(AttendanceDailyStatsWidget::class);
});

it('shows per-day check-in and check-out totals for seminar days', function () {
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

    Livewire::test(AttendanceDailyStatsWidget::class)
        ->assertSee('Day 1')
        ->assertSee('2 / 1')
        ->assertSee('Day 2')
        ->assertSee('1 / 0')
        ->assertSee('Day 3')
        ->assertSee('0 / 0');
});

it('shows hands-on check-in and check-out totals grouped by session date', function () {
    $this->actingAs($this->staff);

    $firstDay = HandsOn::factory()->create(['event_date' => '2026-11-13']);
    $secondDay = HandsOn::factory()->create(['event_date' => '2026-11-14']);

    $firstDayRegistration = HandsOnRegistration::factory()->create(['hands_on_id' => $firstDay->id]);
    $secondDayRegistration = HandsOnRegistration::factory()->create(['hands_on_id' => $secondDay->id]);

    // Day one: three check-ins, two of them checked out, plus a cancelled row
    // that must not count.
    Attendance::factory()->handsOn()->checkedOut()->create([
        'hands_on_registration_id' => $firstDayRegistration->id,
    ]);
    Attendance::factory()->handsOn()->checkedOut()->create([
        'hands_on_registration_id' => $firstDayRegistration->id,
    ]);
    Attendance::factory()->handsOn()->create([
        'hands_on_registration_id' => $firstDayRegistration->id,
        'checked_out_at' => null,
    ]);
    Attendance::factory()->handsOn()->cancelled()->create([
        'hands_on_registration_id' => $firstDayRegistration->id,
    ]);

    // Day two: two check-ins, one checked out.
    Attendance::factory()->handsOn()->checkedOut()->create([
        'hands_on_registration_id' => $secondDayRegistration->id,
    ]);
    Attendance::factory()->handsOn()->create([
        'hands_on_registration_id' => $secondDayRegistration->id,
        'checked_out_at' => null,
    ]);

    Livewire::test(AttendanceDailyStatsWidget::class)
        ->assertSee('13 Nov 2026')
        ->assertSee('3 / 2')
        ->assertSee('14 Nov 2026')
        ->assertSee('2 / 1');
});
