<?php

namespace App\Filament\Resources\Attendances\Pages;

use App\Filament\Resources\Attendances\AttendanceResource;
use App\Filament\Resources\Attendances\Tables\AttendanceRosterTable;
use App\Models\AttendanceRoster;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

/**
 * The single attendance desk list: seminar and hands-on participants together.
 *
 * Rows are the `attendance_roster` view — one row per verified participant.
 * Both registration sides are eager loaded here so no column or action has to
 * query per row.
 */
class ListAttendanceRoster extends ListRecords
{
    protected static string $resource = AttendanceResource::class;

    protected static ?string $title = 'Attendance';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-clipboard-document-check';

    protected static string|\BackedEnum|null $activeNavigationIcon = 'heroicon-s-clipboard-document-check';

    protected static string|\UnitEnum|null $navigationGroup = 'Attendance';

    protected static ?int $navigationSort = 1;

    protected function getTableQuery(): Builder
    {
        return AttendanceRoster::query()
            ->with([
                'seminarRegistration.seminarPackage',
                'seminarRegistration.seminarAttendances.checkedInBy',
                'seminarRegistration.seminarAttendances.checkedOutBy',
                'seminarRegistration.verifiedHandsOnRegistrations',
                'handsOnRegistration.handsOn',
                'handsOnRegistration.activeAttendance',
            ]);
    }

    public function table(Table $table): Table
    {
        return AttendanceRosterTable::configure($table);
    }
}
