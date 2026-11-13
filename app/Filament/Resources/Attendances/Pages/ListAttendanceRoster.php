<?php

namespace App\Filament\Resources\Attendances\Pages;

use App\Filament\Resources\Attendances\AttendanceResource;
use App\Filament\Resources\Attendances\Tables\AttendanceRosterTable;
use App\Filament\Resources\Attendances\Widgets\AttendanceDailyStatsWidget;
use App\Models\Attendance;
use App\Models\AttendanceRoster;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
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

    public function getTabs(): array
    {
        $tabs = ['all' => Tab::make(__('filament.attendance.all'))];

        foreach (Attendance::SEMINAR_DAYS as $day) {
            $attendanceDay = Attendance::query()
                ->where('activity_type', 'seminar')
                ->where('day', $day)
                ->whereNull('hands_on_registration_id')
                ->whereNull('cancelled_at');

            foreach (['check_in' => 'checked_in_at', 'check_out' => 'checked_out_at'] as $tab => $column) {
                $tabs["{$tab}_day_{$day}"] = Tab::make(__('filament.attendance.'.$tab.'_day', ['day' => $day]))
                    ->badge((clone $attendanceDay)->whereNotNull($column)->count())
                    ->modifyQueryUsing(fn (Builder $query): Builder => $query->whereHas(
                        'seminarRegistration.seminarAttendances',
                        fn (Builder $attendanceQuery): Builder => $attendanceQuery
                            ->where('day', $day)
                            ->whereNotNull($column)
                    ));
            }
        }

        return $tabs;
    }

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-clipboard-document-check';

    protected static string|\BackedEnum|null $activeNavigationIcon = 'heroicon-s-clipboard-document-check';

    protected static string|\UnitEnum|null $navigationGroup = 'Attendance';

    protected static ?int $navigationSort = 1;

    protected function getTableQuery(): Builder
    {
        return AttendanceRoster::query()
            ->with([
                'seminarRegistration.seminarPackage',
                'seminarRegistration.addonRegistrations.addon',
                'seminarRegistration.seminarAttendances.checkedInBy',
                'seminarRegistration.seminarAttendances.checkedOutBy',
                'seminarRegistration.verifiedHandsOnRegistrations',
                'handsOnRegistration.handsOn',
                'handsOnRegistration.activeAttendance',
            ]);
    }

    protected function getHeaderWidgets(): array
    {
        return [
            AttendanceDailyStatsWidget::class,
        ];
    }

    public function table(Table $table): Table
    {
        return AttendanceRosterTable::configure($table);
    }
}
