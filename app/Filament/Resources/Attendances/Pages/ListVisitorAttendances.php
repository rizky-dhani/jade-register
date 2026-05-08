<?php

namespace App\Filament\Resources\Attendances\Pages;

use App\Filament\Resources\Attendances\AttendanceResource;
use App\Filament\Resources\Attendances\Tables\VisitorAttendancesTable;
use App\Models\VisitorAttendance;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ListVisitorAttendances extends ListRecords
{
    protected static string $resource = AttendanceResource::class;

    protected static ?string $title = 'Visitor Attendance';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static string|\BackedEnum|null $activeNavigationIcon = 'heroicon-s-rectangle-stack';

    protected static string|\UnitEnum|null $navigationGroup = 'Attendance';

    protected static ?int $navigationSort = 3;

    protected function getTableQuery(): Builder
    {
        return VisitorAttendance::query()
            ->with(['visitor', 'checkedInBy']);
    }

    public function table(Table $table): Table
    {
        return VisitorAttendancesTable::configure($table);
    }
}
