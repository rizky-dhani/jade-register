<?php

namespace App\Filament\Resources\PosterEvaluations\Tables;

use App\Models\PosterEvaluation;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PosterEvaluationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('submission.title')
                    ->label('Poster')
                    ->searchable()
                    ->limit(40),
                TextColumn::make('judge.name')
                    ->label('Judge')
                    ->searchable(),
                TextColumn::make('content_score')
                    ->label('Content')
                    ->formatStateUsing(fn (int $state): string => "{$state}/".PosterEvaluation::MAX_CONTENT_SCORE),
                TextColumn::make('creativity_score')
                    ->label('Creativity')
                    ->formatStateUsing(fn (int $state): string => "{$state}/".PosterEvaluation::MAX_CREATIVITY_SCORE),
                TextColumn::make('visual_score')
                    ->label('Visual')
                    ->formatStateUsing(fn (int $state): string => "{$state}/".PosterEvaluation::MAX_VISUAL_SCORE),
                TextColumn::make('presentation_score')
                    ->label('Presentation')
                    ->formatStateUsing(fn (int $state): string => "{$state}/".PosterEvaluation::MAX_PRESENTATION_SCORE),
                TextColumn::make('total_score')
                    ->label('Total')
                    ->formatStateUsing(fn (int $state): string => "{$state}/100")
                    ->sortable(),
                TextColumn::make('evaluated_at')
                    ->dateTime('d M Y, H:i')
                    ->label('Evaluated'),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
