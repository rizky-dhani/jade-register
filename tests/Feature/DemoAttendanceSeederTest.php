<?php

use App\Models\Attendance;
use App\Models\SeminarRegistration;
use App\Models\Visitor;
use App\Models\VisitorAttendance;
use Database\Seeders\CountrySeeder;
use Database\Seeders\DemoAttendanceSeeder;
use Database\Seeders\HandsOnSeeder;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

beforeEach(function () {
    Mail::fake();
    Storage::fake('public');

    $this->seed([
        RolePermissionSeeder::class,
        CountrySeeder::class,
        HandsOnSeeder::class,
    ]);
});

it('seeds visitors that the attendance desk can scan', function () {
    $this->seed(DemoAttendanceSeeder::class);

    expect(Visitor::count())->toBe(4)
        ->and(Visitor::whereNotNull('qr_token')->count())->toBe(4)
        ->and(Visitor::whereNotNull('scanned_at')->count())->toBe(2)
        ->and(VisitorAttendance::count())->toBe(2);
});

it('seeds seminar and hands-on attendance rows', function () {
    $this->seed(DemoAttendanceSeeder::class);

    $seminarAttendances = Attendance::where('activity_type', 'seminar')->get();
    $handsOnAttendances = Attendance::where('activity_type', 'hands_on')->get();

    expect($seminarAttendances)->toHaveCount(2)
        ->and($handsOnAttendances)->toHaveCount(2)
        ->and($seminarAttendances->every(fn (Attendance $attendance): bool => $attendance->checked_in_by !== null))->toBeTrue();

    $handsOnAttendances->each(function (Attendance $attendance): void {
        expect($attendance->hands_on_registration_id)->not->toBeNull()
            ->and($attendance->seminar_registration_id)->not->toBeNull()
            ->and($attendance->handsOnRegistration->payment_status)->toBe('verified');
    });
});

it('leaves most registrations unscanned so check-in can be demoed', function () {
    $this->seed(DemoAttendanceSeeder::class);

    expect(Attendance::where('activity_type', 'seminar')->count())->toBeLessThan(
        SeminarRegistration::count()
    );
});

it('does not duplicate rows when seeded twice', function () {
    $this->seed(DemoAttendanceSeeder::class);

    $visitorCount = Visitor::count();
    $visitorAttendanceCount = VisitorAttendance::count();
    $attendanceCount = Attendance::count();

    $this->seed(DemoAttendanceSeeder::class);

    expect(Visitor::count())->toBe($visitorCount)
        ->and(VisitorAttendance::count())->toBe($visitorAttendanceCount)
        ->and(Attendance::count())->toBe($attendanceCount);
});
