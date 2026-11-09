<?php

use App\Livewire\AttendanceVerify;
use App\Models\Attendance;
use App\Models\SeminarRegistration;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Livewire\livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);

    $this->user = User::factory()->create();
    $this->user->assignRole('Admin');
    $this->registration = SeminarRegistration::factory()->verified()->create();
});

test('the verify page offers a check-in button for each of the three event days', function () {
    $this->actingAs($this->user);

    livewire(AttendanceVerify::class, ['token' => $this->registration->qr_token])
        ->assertSee(__('seminar.seminar_day_label', ['day' => 1]))
        ->assertSee(__('seminar.seminar_day_label', ['day' => 2]))
        ->assertSee(__('seminar.seminar_day_label', ['day' => 3]))
        ->assertSee(__('seminar.check_in_day_button', ['day' => 1]))
        ->assertSee(__('seminar.check_in_day_button', ['day' => 2]))
        ->assertSee(__('seminar.check_in_day_button', ['day' => 3]));
});

test('each day check-in writes its own attendance row', function () {
    $this->actingAs($this->user);

    livewire(AttendanceVerify::class, ['token' => $this->registration->qr_token])
        ->call('checkInSeminarDay', 1)
        ->call('checkInSeminarDay', 2)
        ->call('checkInSeminarDay', 3);

    $rows = Attendance::query()
        ->where('seminar_registration_id', $this->registration->id)
        ->where('activity_type', 'seminar')
        ->orderBy('day')
        ->get();

    expect($rows)->toHaveCount(3)
        ->and($rows->pluck('day')->all())->toBe([1, 2, 3])
        ->and($rows->every(fn (Attendance $row): bool => $row->checked_in_by === $this->user->id))->toBeTrue();
});

test('checking the same day in twice creates one row and keeps the first stamp', function () {
    $this->actingAs($this->user);

    $component = livewire(AttendanceVerify::class, ['token' => $this->registration->qr_token]);
    $component->call('checkInSeminarDay', 2);

    $original = Attendance::query()->where('day', 2)->sole();
    $originalStaff = $original->checked_in_by;
    $originalTime = $original->checked_in_at;

    $otherStaff = User::factory()->create();
    $otherStaff->assignRole('Admin');
    $this->actingAs($otherStaff);

    livewire(AttendanceVerify::class, ['token' => $this->registration->qr_token])
        ->call('checkInSeminarDay', 2);

    $original->refresh();

    expect(Attendance::query()->where('seminar_registration_id', $this->registration->id)->count())->toBe(1)
        ->and($original->checked_in_by)->toBe($originalStaff)
        ->and($original->checked_in_at->equalTo($originalTime))->toBeTrue();
});

test('a day outside 1-3 is refused', function () {
    $this->actingAs($this->user);

    livewire(AttendanceVerify::class, ['token' => $this->registration->qr_token])
        ->call('checkInSeminarDay', 4);

    $this->assertDatabaseCount('attendances', 0);
});

test('an unverified participant cannot be checked in for any day', function () {
    $pending = SeminarRegistration::factory()->pending()->create();
    $this->actingAs($this->user);

    livewire(AttendanceVerify::class, ['token' => $pending->qr_token])
        ->call('checkInSeminarDay', 1)
        ->assertSee(__('seminar.payment_not_verified'));

    expect(Attendance::query()->where('seminar_registration_id', $pending->id)->exists())->toBeFalse();
});

test('the page shows which days are already checked in and which are still open', function () {
    $this->actingAs($this->user);

    Attendance::factory()->day(1)->create([
        'seminar_registration_id' => $this->registration->id,
        'activity_type' => 'seminar',
        'checked_in_by' => $this->user->id,
    ]);

    livewire(AttendanceVerify::class, ['token' => $this->registration->qr_token])
        ->assertDontSee(__('seminar.check_in_day_button', ['day' => 1]))
        ->assertSee(__('seminar.check_in_day_button', ['day' => 2]))
        ->assertSee(__('seminar.check_in_day_button', ['day' => 3]));
});

test('nextOpenSeminarDay reports the first day without a check-in', function () {
    expect($this->registration->nextOpenSeminarDay())->toBe(1);

    Attendance::factory()->day(1)->create([
        'seminar_registration_id' => $this->registration->id,
        'activity_type' => 'seminar',
        'checked_in_by' => $this->user->id,
    ]);
    Attendance::factory()->day(2)->create([
        'seminar_registration_id' => $this->registration->id,
        'activity_type' => 'seminar',
        'checked_in_by' => $this->user->id,
    ]);

    expect($this->registration->fresh()->nextOpenSeminarDay())->toBe(3);
});
