<?php

namespace App\Filament\Resources\SeminarPackages;

use App\Filament\Resources\SeminarPackages\Pages\CreateSeminarPackage;
use App\Filament\Resources\SeminarPackages\Pages\EditSeminarPackage;
use App\Filament\Resources\SeminarPackages\Pages\ListSeminarPackages;
use App\Filament\Resources\SeminarPackages\Schemas\SeminarPackageForm;
use App\Filament\Resources\SeminarPackages\Tables\SeminarPackagesTable;
use App\Models\SeminarPackage;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class SeminarPackageResource extends Resource
{
    protected static ?string $model = SeminarPackage::class;

    protected static \UnitEnum|string|null $navigationGroup = 'Settings';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTicket;

    protected static ?string $navigationLabel = 'Seminar Packages';

    protected static ?int $navigationSort = 21;

    public static function form(Schema $schema): Schema
    {
        return SeminarPackageForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SeminarPackagesTable::configure($table);
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
            'index' => ListSeminarPackages::route('/'),
            'create' => CreateSeminarPackage::route('/create'),
            'edit' => EditSeminarPackage::route('/{record}/edit'),
        ];
    }
}
