<?php

namespace App\Filament\Resources\PosterSubmissions\RelationManagers;

use App\Models\PosterEvaluation;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class PosterEvaluationsRelationManager extends RelationManager
{
    protected static string $relationship = 'evaluations';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('judge.name')
                    ->label('Judge'),
                Tables\Columns\TextColumn::make('content_score')
                    ->label('Content')
                    ->formatStateUsing(fn (int $state): string => "{$state}/".PosterEvaluation::MAX_CONTENT_SCORE),
                Tables\Columns\TextColumn::make('creativity_score')
                    ->label('Creativity')
                    ->formatStateUsing(fn (int $state): string => "{$state}/".PosterEvaluation::MAX_CREATIVITY_SCORE),
                Tables\Columns\TextColumn::make('visual_score')
                    ->label('Visual')
                    ->formatStateUsing(fn (int $state): string => "{$state}/".PosterEvaluation::MAX_VISUAL_SCORE),
                Tables\Columns\TextColumn::make('presentation_score')
                    ->label('Presentation')
                    ->formatStateUsing(fn (int $state): string => "{$state}/".PosterEvaluation::MAX_PRESENTATION_SCORE),
                Tables\Columns\TextColumn::make('total_score')
                    ->label('Total')
                    ->formatStateUsing(fn (int $state): string => "{$state}/100"),
                Tables\Columns\TextColumn::make('evaluated_at')
                    ->dateTime('d M Y, H:i'),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
