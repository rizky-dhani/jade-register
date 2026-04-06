<?php

namespace App\Filament\Resources\Attendances\Pages;

use App\Filament\Resources\Attendances\AttendanceResource;
use App\Filament\Resources\Attendances\Tables\HandsOnAttendancesTable;
use App\Models\Attendance;
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

    protected function getTableQuery(): Builder
    {
        return Attendance::query()
            ->where('activity_type', 'hands_on')
            ->with(['handsOnRegistration.seminarRegistration', 'handsOnRegistration.handsOn', 'checkedInBy']);
    }

    public function table(Table $table): Table
    {
        return HandsOnAttendancesTable::configure($table);
    }
}
