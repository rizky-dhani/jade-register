<?php

namespace App\Filament\Resources\DigitalWorkshops;

use App\Filament\Resources\DigitalWorkshops\Pages\CreateDigitalWorkshop;
use App\Filament\Resources\DigitalWorkshops\Pages\EditDigitalWorkshop;
use App\Filament\Resources\DigitalWorkshops\Pages\ListDigitalWorkshops;
use App\Filament\Resources\DigitalWorkshops\Schemas\DigitalWorkshopForm;
use App\Filament\Resources\DigitalWorkshops\Tables\DigitalWorkshopsTable;
use App\Models\DigitalWorkshop;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class DigitalWorkshopResource extends Resource
{
    protected static ?string $model = DigitalWorkshop::class;

    protected static \UnitEnum|string|null $navigationGroup = 'Events';

    protected static ?int $navigationGroupSort = 2;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPresentationChartBar;

    protected static ?int $navigationSort = 21;

    public static function getModelLabel(): string
    {
        return __('filament.navigation.digital_workshop');
    }

    public static function getPluralModelLabel(): string
    {
        return __('filament.navigation.digital_workshops');
    }

    public static function getNavigationLabel(): string
    {
        return __('filament.navigation.digital_workshops');
    }

    public static function form(Schema $schema): Schema
    {
        return DigitalWorkshopForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return DigitalWorkshopsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListDigitalWorkshops::route('/'),
            'create' => CreateDigitalWorkshop::route('/create'),
            'edit' => EditDigitalWorkshop::route('/{record}/edit'),
        ];
    }

    public static function getRedirectUrl(): string
    {
        return self::getUrl('index');
    }
}
