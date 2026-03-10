<?php

namespace App\Filament\Resources\HandsOnEvents;

use App\Filament\Resources\HandsOnEvents\Pages\CreateHandsOnEvent;
use App\Filament\Resources\HandsOnEvents\Pages\EditHandsOnEvent;
use App\Filament\Resources\HandsOnEvents\Pages\ListHandsOnEvents;
use App\Filament\Resources\HandsOnEvents\Schemas\HandsOnEventForm;
use App\Filament\Resources\HandsOnEvents\Tables\HandsOnEventsTable;
use App\Models\HandsOnEvent;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class HandsOnEventResource extends Resource
{
    protected static ?string $model = HandsOnEvent::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return HandsOnEventForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return HandsOnEventsTable::configure($table);
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
            'index' => ListHandsOnEvents::route('/'),
            'create' => CreateHandsOnEvent::route('/create'),
            'edit' => EditHandsOnEvent::route('/{record}/edit'),
        ];
    }
}
