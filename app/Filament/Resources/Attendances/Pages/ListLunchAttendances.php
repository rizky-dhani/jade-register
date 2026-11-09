<?php

namespace App\Filament\Resources\Attendances\Pages;

use App\Filament\Resources\Attendances\AttendanceResource;
use App\Filament\Resources\Attendances\Tables\LunchRedemptionsTable;
use App\Models\LunchRedemption;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

/**
 * Lunch coupon desk log: one row per redemption.
 *
 * Unlike the attendance roster this is not a participant list — a participant
 * only appears once the coupon has actually been handed over, so the page reads
 * as an audit trail of who served lunch and when.
 */
class ListLunchAttendances extends ListRecords
{
    protected static string $resource = AttendanceResource::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-cake';

    protected static string|\BackedEnum|null $activeNavigationIcon = 'heroicon-s-cake';

    protected static string|\UnitEnum|null $navigationGroup = 'Attendance';

    protected static ?int $navigationSort = 2;

    public static function getNavigationLabel(): string
    {
        return __('filament.lunch_attendance.title');
    }

    public function getTitle(): string
    {
        return __('filament.lunch_attendance.title');
    }

    protected function getTableQuery(): Builder
    {
        return LunchRedemption::query()
            ->with(['seminarRegistration.seminarPackage', 'redeemedBy']);
    }

    public function table(Table $table): Table
    {
        return LunchRedemptionsTable::configure($table);
    }
}
