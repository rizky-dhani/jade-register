<?php

namespace App\Filament\Resources\DigitalWorkshopRegistrations;

use App\Filament\Resources\DigitalWorkshopRegistrations\Pages\CreateDigitalWorkshopRegistration;
use App\Filament\Resources\DigitalWorkshopRegistrations\Pages\EditDigitalWorkshopRegistration;
use App\Filament\Resources\DigitalWorkshopRegistrations\Pages\ListDigitalWorkshopRegistrations;
use App\Filament\Resources\DigitalWorkshopRegistrations\Schemas\DigitalWorkshopRegistrationForm;
use App\Filament\Resources\DigitalWorkshopRegistrations\Tables\DigitalWorkshopRegistrationsTable;
use App\Models\DigitalWorkshopRegistration;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class DigitalWorkshopRegistrationResource extends Resource
{
    protected static ?string $model = DigitalWorkshopRegistration::class;

    protected static \UnitEnum|string|null $navigationGroup = 'Data';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserGroup;

    protected static ?int $navigationSort = 4;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['digitalWorkshop', 'seminarRegistration']);
    }

    public static function getModelLabel(): string
    {
        return __('filament.navigation.digital_workshop_registrations');
    }

    public static function getPluralModelLabel(): string
    {
        return __('filament.navigation.digital_workshop_registrations');
    }

    public static function getNavigationLabel(): string
    {
        return __('filament.navigation.digital_workshop_registrations');
    }

    public static function form(Schema $schema): Schema
    {
        return DigitalWorkshopRegistrationForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return DigitalWorkshopRegistrationsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListDigitalWorkshopRegistrations::route('/'),
            'create' => CreateDigitalWorkshopRegistration::route('/create'),
            'edit' => EditDigitalWorkshopRegistration::route('/{record}/edit'),
        ];
    }

    public static function getRedirectUrl(): string
    {
        return self::getUrl('index');
    }
}
