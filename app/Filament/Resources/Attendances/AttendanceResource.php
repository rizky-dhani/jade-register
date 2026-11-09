<?php

namespace App\Filament\Resources\Attendances;

use App\Filament\Resources\Attendances\Pages\CreateAttendance;
use App\Filament\Resources\Attendances\Pages\EditAttendance;
use App\Filament\Resources\Attendances\Pages\ListAttendanceRoster;
use App\Filament\Resources\Attendances\Pages\ListVisitorAttendances;
use App\Filament\Resources\Attendances\Schemas\AttendanceForm;
use App\Models\Attendance;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class AttendanceResource extends Resource
{
    protected static ?string $model = Attendance::class;

    protected static \UnitEnum|string|null $navigationGroup = 'Attendance';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    /**
     * Attendance has no display-name column of its own (the participant name
     * lives on the related registration), so it is excluded from global
     * search. Declaring a title attribute here made Filament query a
     * non-existent `attendances.name` column.
     */
    protected static bool $isGloballySearchable = false;

    protected static bool $shouldRegisterNavigation = false;

    public static function getModelLabel(): string
    {
        return __('filament.navigation.attendance');
    }

    public static function getPluralModelLabel(): string
    {
        return __('filament.navigation.attendance');
    }

    public static function getNavigationLabel(): string
    {
        return __('filament.navigation.attendance');
    }

    public static function form(Schema $schema): Schema
    {
        return AttendanceForm::configure($schema);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAttendanceRoster::route('/'),
            'visitor' => ListVisitorAttendances::route('/visitor'),
            'create' => CreateAttendance::route('/create'),
            'edit' => EditAttendance::route('/{record}/edit'),
        ];
    }
}
