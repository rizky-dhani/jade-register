<?php

namespace App\Filament\Resources\Attendances\Pages;

use App\Filament\Resources\Attendances\AttendanceResource;
use App\Filament\Resources\Attendances\Tables\HandsOnAttendancesTable;
use App\Models\HandsOnRegistration;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ListHandsOnAttendances extends ListRecords
{
    protected static string $resource = AttendanceResource::class;

    protected static ?string $title = 'Hands On Attendance';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-wrench-screwdriver';

    protected static string|\BackedEnum|null $activeNavigationIcon = 'heroicon-s-wrench-screwdriver';

    protected static string|\UnitEnum|null $navigationGroup = 'Attendance';

    protected static ?int $navigationSort = 2;

    /**
     * One row per session registration, so a participant enrolled in two
     * hands-on sessions appears twice — each session is checked at its own door.
     *
     * Verified only, matching AttendanceCheckInService::ensureVerified().
     */
    protected function getTableQuery(): Builder
    {
        return HandsOnRegistration::query()
            ->where('payment_status', 'verified')
            ->with(['seminarRegistration', 'handsOn', 'attendance.checkedInBy', 'attendance.checkedOutBy']);
    }

    public function table(Table $table): Table
    {
        return HandsOnAttendancesTable::configure($table);
    }
}
