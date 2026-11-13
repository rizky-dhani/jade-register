<?php

use App\Filament\Resources\Attendances\Pages\ListAttendanceRoster;
use App\Models\Attendance;
use App\Models\AttendanceRoster;
use App\Models\SeminarRegistration;
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

function attendanceTabRow(int $day, bool $checkedOut = false, bool $cancelled = false): AttendanceRoster
{
    $registration = SeminarRegistration::factory()->verified()->create();
    Attendance::factory()->day($day)
        ->when($checkedOut, fn ($factory) => $factory->checkedOut())
        ->when($cancelled, fn ($factory) => $factory->cancelled())
        ->create(['seminar_registration_id' => $registration->id]);

    return AttendanceRoster::where('participant_key', 'seminar-'.$registration->id)->sole();
}

it('filters attendance rows by daily check-in and check-out tab', function () {
    $checkedInDayOne = attendanceTabRow(1);
    $checkedOutDayOne = attendanceTabRow(1, checkedOut: true);
    $checkedInDayTwo = attendanceTabRow(2);
    $cancelledDayOne = attendanceTabRow(1, cancelled: true);

    livewire(ListAttendanceRoster::class)
        ->set('activeTab', 'check_in_day_1')
        ->assertCanSeeTableRecords([$checkedInDayOne, $checkedOutDayOne])
        ->assertCanNotSeeTableRecords([$checkedInDayTwo, $cancelledDayOne])
        ->set('activeTab', 'check_out_day_1')
        ->assertCanSeeTableRecords([$checkedOutDayOne])
        ->assertCanNotSeeTableRecords([$checkedInDayOne, $checkedInDayTwo, $cancelledDayOne]);
});
