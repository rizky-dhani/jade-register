<?php

namespace App\Filament\Resources\Attendances\Pages;

use App\Filament\Resources\Attendances\AttendanceResource;
use App\Filament\Resources\Attendances\Tables\SeminarAttendancesTable;
use App\Models\SeminarRegistration;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ListSeminarAttendances extends ListRecords
{
    protected static string $resource = AttendanceResource::class;

    protected static ?string $title = 'Seminar Attendance';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static string|\BackedEnum|null $activeNavigationIcon = 'heroicon-s-rectangle-stack';

    protected static string|\UnitEnum|null $navigationGroup = 'Attendance';

    protected static ?int $navigationSort = 1;

    /**
     * Driven by the registrations, not by attendance rows: a participant who has
     * not been scanned yet still needs a row on the desk list, otherwise the
     * "not checked in" filter can never return anything.
     *
     * Verified only, matching AttendanceCheckInService::ensureVerified() — an
     * unverified payment can never be checked in, so it is not desk work.
     *
     * `seminarAttendances` is the participant's one row per event day; the table
     * flattens them into a day-by-day check-in column set.
     */
    protected function getTableQuery(): Builder
    {
        return SeminarRegistration::query()
            ->where('payment_status', 'verified')
            ->with(['seminarPackage', 'seminarAttendances.checkedInBy', 'seminarAttendances.checkedOutBy']);
    }

    public function table(Table $table): Table
    {
        return SeminarAttendancesTable::configure($table);
    }
}
