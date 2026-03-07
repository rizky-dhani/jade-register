<?php

namespace App\Filament\Resources\PosterEvaluations;

use App\Filament\Resources\PosterEvaluations\Pages\CreatePosterEvaluation;
use App\Filament\Resources\PosterEvaluations\Pages\EditPosterEvaluation;
use App\Filament\Resources\PosterEvaluations\Pages\ListPosterEvaluations;
use App\Filament\Resources\PosterEvaluations\Schemas\PosterEvaluationForm;
use App\Filament\Resources\PosterEvaluations\Tables\PosterEvaluationsTable;
use App\Models\PosterEvaluation;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class PosterEvaluationResource extends Resource
{
    protected static ?string $model = PosterEvaluation::class;

    protected static \UnitEnum|string|null $navigationGroup = 'Competitions';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentList;

    protected static ?string $navigationLabel = 'Poster Evaluations';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return PosterEvaluationForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PosterEvaluationsTable::configure($table);
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
            'index' => ListPosterEvaluations::route('/'),
            'create' => CreatePosterEvaluation::route('/create'),
            'edit' => EditPosterEvaluation::route('/{record}/edit'),
        ];
    }
}
