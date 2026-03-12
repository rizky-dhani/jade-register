<?php

namespace App\Filament\Resources\HandsOnRegistrations;

use App\Filament\Resources\HandsOnRegistrations\Pages\CreateHandsOnRegistration;
use App\Filament\Resources\HandsOnRegistrations\Pages\EditHandsOnRegistration;
use App\Filament\Resources\HandsOnRegistrations\Pages\ListHandsOnRegistrations;
use App\Filament\Resources\HandsOnRegistrations\Schemas\HandsOnRegistrationForm;
use App\Filament\Resources\HandsOnRegistrations\Tables\HandsOnRegistrationsTable;
use App\Models\HandsOnRegistration;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class HandsOnRegistrationResource extends Resource
{
    protected static ?string $model = HandsOnRegistration::class;

    protected static \UnitEnum|string|null $navigationGroup = 'Registrations';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedHandRaised;

    protected static ?string $navigationLabel = 'Hands On Registrations';

    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return HandsOnRegistrationForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return HandsOnRegistrationsTable::configure($table);
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
            'index' => ListHandsOnRegistrations::route('/'),
            'create' => CreateHandsOnRegistration::route('/create'),
            'edit' => EditHandsOnRegistration::route('/{record}/edit'),
        ];
    }

    public static function getRedirectUrl(): string
    {
        return self::getUrl('index');
    }
}
