<?php

namespace App\Filament\Resources\PosterSubmissions;

use App\Filament\Resources\PosterSubmissions\Pages\CreatePosterSubmission;
use App\Filament\Resources\PosterSubmissions\Pages\EditPosterSubmission;
use App\Filament\Resources\PosterSubmissions\Pages\ListPosterSubmissions;
use App\Filament\Resources\PosterSubmissions\RelationManagers\PosterEvaluationsRelationManager;
use App\Filament\Resources\PosterSubmissions\Schemas\PosterSubmissionForm;
use App\Filament\Resources\PosterSubmissions\Tables\PosterSubmissionsTable;
use App\Models\PosterSubmission;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class PosterSubmissionResource extends Resource
{
    protected static ?string $model = PosterSubmission::class;

    protected static \UnitEnum|string|null $navigationGroup = 'Competitions';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $navigationLabel = 'Poster Submissions';

    protected static ?int $navigationSort = 1;

    public static function getModelLabel(): string
    {
        return __('filament.navigation.poster_submissions');
    }

    public static function getPluralModelLabel(): string
    {
        return __('filament.navigation.poster_submissions');
    }

    public static function getNavigationLabel(): string
    {
        return __('filament.navigation.poster_submissions');
    }

    public static function form(Schema $schema): Schema
    {
        return PosterSubmissionForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PosterSubmissionsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            PosterEvaluationsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPosterSubmissions::route('/'),
            'create' => CreatePosterSubmission::route('/create'),
            'edit' => EditPosterSubmission::route('/{record}/edit'),
        ];
    }
}
