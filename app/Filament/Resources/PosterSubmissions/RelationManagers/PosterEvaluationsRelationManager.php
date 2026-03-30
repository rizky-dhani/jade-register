<?php

namespace App\Filament\Resources\PosterSubmissions\RelationManagers;

use App\Models\PosterEvaluation;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class PosterEvaluationsRelationManager extends RelationManager
{
    protected static string $relationship = 'evaluations';

    public function table(Table $table): Table
    {
        $user = auth()->user();
        $isSuperAdmin = $user?->hasRole('Super Admin') ?? false;

        return $table
            ->modifyQueryUsing(function (Builder $query) {
                $user = auth()->user();

                // Judges can only see their own evaluations
                if ($user && ! $user->hasRole('Super Admin')) {
                    $query->where('judge_id', $user->getKey());
                }
            })
            ->columns([
                Tables\Columns\TextColumn::make('judge.name')
                    ->label('Judge')
                    ->visible($isSuperAdmin),
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
                Tables\Actions\CreateAction::make()
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['judge_id'] = auth()->id();

                        return $data;
                    })
                    ->visible(fn () => $user?->can('evaluate poster submissions') ?? false),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->visible(fn ($record) => $isSuperAdmin || $record->judge_id === $user?->getKey()),
                Tables\Actions\DeleteAction::make()
                    ->visible(fn () => $isSuperAdmin),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->visible(fn () => $isSuperAdmin),
                ]),
            ]);
    }
}
