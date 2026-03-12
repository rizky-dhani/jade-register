<?php

namespace App\Filament\Resources\SeminarRegistrations;

use App\Filament\Resources\SeminarRegistrations\Pages\CreateSeminarRegistration;
use App\Filament\Resources\SeminarRegistrations\Pages\EditSeminarRegistration;
use App\Filament\Resources\SeminarRegistrations\Pages\ListSeminarRegistrations;
use App\Filament\Resources\SeminarRegistrations\Schemas\SeminarRegistrationForm;
use App\Filament\Resources\SeminarRegistrations\Tables\SeminarRegistrationsTable;
use App\Models\SeminarRegistration;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class SeminarRegistrationResource extends Resource
{
    protected static ?string $model = SeminarRegistration::class;

    protected static \UnitEnum|string|null $navigationGroup = 'Registrations';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentCheck;

    protected static ?string $navigationLabel = 'Seminar Registrations';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return SeminarRegistrationForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SeminarRegistrationsTable::configure($table);
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
            'index' => ListSeminarRegistrations::route('/'),
            'create' => CreateSeminarRegistration::route('/create'),
            'edit' => EditSeminarRegistration::route('/{record}/edit'),
        ];
    }

    public static function getRedirectUrl(): string
    {
        return self::getUrl('index');
    }
}
