<?php

namespace App\Filament\Resources\Visitors;

use App\Filament\Navigation\NavigationGroup;
use App\Filament\Resources\Visitors\Pages\ListVisitors;
use App\Filament\Resources\Visitors\Schemas\VisitorForm;
use App\Filament\Resources\Visitors\Tables\VisitorsTable;
use App\Models\Visitor;
use App\Models\Visitor;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class VisitorResource extends Resource
{
    protected static ?string $model = Visitor::class;

    protected static UnitEnum|string|null $navigationGroup = NavigationGroup::REGISTRATIONS;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUsers;

    protected static ?string $navigationLabel = 'Visitors';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return VisitorForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return VisitorsTable::configure($table);
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
            'index' => ListVisitors::route('/'),
        ];
    }
}
