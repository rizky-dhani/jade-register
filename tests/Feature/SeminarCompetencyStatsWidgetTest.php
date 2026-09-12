<?php

use App\Filament\Pages\SeminarRegistrationReport;
use App\Filament\Resources\SeminarRegistrations\Widgets\SeminarCompetencyStatsWidget;
use App\Models\SeminarRegistration;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    Role::create(['name' => 'Super Admin', 'guard_name' => 'web']);
});

test('competency widget groups all registrations by kompetensi', function () {
    SeminarRegistration::factory()->create(['kompetensi' => 'Dokter Gigi Umum']);
    SeminarRegistration::factory()->create(['kompetensi' => 'Dokter Gigi Umum']);
    SeminarRegistration::factory()->create(['kompetensi' => 'Sp.Ort']);
    SeminarRegistration::factory()->create(['kompetensi' => 'Sp.Ort', 'payment_status' => 'verified']);

    Livewire::test(SeminarCompetencyStatsWidget::class)
        ->assertSee('Dokter Gigi Umum')
        ->assertSee('Sp.Ort');
});

test('competency widget excludes null and empty kompetensi', function () {
    SeminarRegistration::factory()->create(['kompetensi' => 'Dokter Gigi Umum']);
    SeminarRegistration::factory()->create(['kompetensi' => null]);
    SeminarRegistration::factory()->create(['kompetensi' => '']);

    $counts = SeminarRegistration::selectRaw('kompetensi, COUNT(*) as count')
        ->whereNotNull('kompetensi')
        ->where('kompetensi', '!=', '')
        ->groupBy('kompetensi')
        ->pluck('count', 'kompetensi');

    expect($counts)->toHaveCount(1)
        ->and($counts)->toHaveKey('Dokter Gigi Umum');
});

test('report page renders for super admin', function () {
    $admin = User::factory()->create();
    $admin->assignRole('Super Admin');

    $this->actingAs($admin)
        ->get(SeminarRegistrationReport::getUrl())
        ->assertSuccessful();
});
