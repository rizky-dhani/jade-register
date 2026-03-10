<?php

namespace App\Filament\Resources\HandsOns;

use App\Filament\Resources\HandsOns\Pages\CreateHandsOn;
use App\Filament\Resources\HandsOns\Pages\EditHandsOn;
use App\Filament\Resources\HandsOns\Pages\ListHandsOns;
use App\Filament\Resources\HandsOns\Schemas\HandsOnForm;
use App\Filament\Resources\HandsOns\Tables\HandsOnsTable;
use App\Models\HandsOn;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class HandsOnResource extends Resource
{
    protected static ?string $model = HandsOn::class;

    protected static \UnitEnum|string|null $navigationGroup = 'Events';

    protected static ?int $navigationGroupSort = 2;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTicket;

    protected static ?string $navigationLabel = 'Hands On';

    protected static ?int $navigationSort = 20;

    public static function form(Schema $schema): Schema
    {
        return HandsOnForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return HandsOnsTable::configure($table);
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
            'index' => ListHandsOns::route('/'),
            'create' => CreateHandsOn::route('/create'),
            'edit' => EditHandsOn::route('/{record}/edit'),
        ];
    }
}
