<?php

namespace App\Filament\Resources\Attendances\Pages;

use App\Filament\Resources\Attendances\AttendanceResource;
use App\Filament\Resources\Attendances\Tables\SeminarAttendancesTable;
use App\Models\Attendance;
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

    protected function getTableQuery(): Builder
    {
        return Attendance::query()
            ->where('activity_type', 'seminar')
            ->with(['seminarRegistration', 'checkedInBy']);
    }

    public function table(Table $table): Table
    {
        return SeminarAttendancesTable::configure($table);
    }
}
