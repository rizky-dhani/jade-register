<?php

namespace App\Filament\Resources\DigitalWorkshopRegistrationIntents;

use App\Filament\Resources\DigitalWorkshopRegistrationIntents\Pages\ListDigitalWorkshopRegistrationIntents;
use App\Filament\Resources\DigitalWorkshopRegistrationIntents\Tables\DigitalWorkshopRegistrationIntentsTable;
use App\Models\DigitalWorkshopRegistrationIntent;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class DigitalWorkshopRegistrationIntentResource extends Resource
{
    protected static ?string $model = DigitalWorkshopRegistrationIntent::class;

    protected static \UnitEnum|string|null $navigationGroup = 'Data';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClock;

    protected static ?int $navigationSort = 5;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['digitalWorkshop', 'seminarRegistration', 'digitalWorkshopRegistration']);
    }

    public static function getModelLabel(): string
    {
        return __('filament.navigation.digital_workshop_registration_intents');
    }

    public static function getPluralModelLabel(): string
    {
        return __('filament.navigation.digital_workshop_registration_intents');
    }

    public static function getNavigationLabel(): string
    {
        return __('filament.navigation.digital_workshop_registration_intents');
    }

    public static function table(Table $table): Table
    {
        return DigitalWorkshopRegistrationIntentsTable::configure($table);
    }

    /**
     * Intents are created by the public bundle form only. An admin-created
     * intent would carry no payment proof and no meaning.
     */
    public static function getPages(): array
    {
        return [
            'index' => ListDigitalWorkshopRegistrationIntents::route('/'),
        ];
    }
}
