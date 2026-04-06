<?php

namespace App\Filament\Resources\PosterSubmissions\Tables;

use App\Models\PosterSubmission;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PosterSubmissionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->searchable()
                    ->limit(50),
                TextColumn::make('user.name')
                    ->label(__('filament.poster_submissions.author'))
                    ->searchable(),
                TextColumn::make('category.name')
                    ->label(__('filament.poster_submissions.category')),
                TextColumn::make('topic.name')
                    ->label(__('filament.poster_submissions.topic')),
                TextColumn::make('average_score')
                    ->label(__('filament.poster_submissions.score'))
                    ->formatStateUsing(fn (?int $state): string => $state ? "{$state}/100" : '-')
                    ->sortable(),
                BadgeColumn::make('status')
                    ->colors([
                        'gray' => PosterSubmission::STATUS_DRAFT,
                        'info' => PosterSubmission::STATUS_SUBMITTED,
                        'warning' => PosterSubmission::STATUS_UNDER_REVIEW,
                        'success' => [PosterSubmission::STATUS_ACCEPTED, PosterSubmission::STATUS_FINALIST, PosterSubmission::STATUS_WINNER],
                        'danger' => PosterSubmission::STATUS_REJECTED,
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        PosterSubmission::STATUS_DRAFT => 'Draft',
                        PosterSubmission::STATUS_SUBMITTED => 'Submitted',
                        PosterSubmission::STATUS_UNDER_REVIEW => 'Under Review',
                        PosterSubmission::STATUS_ACCEPTED => 'Accepted',
                        PosterSubmission::STATUS_FINALIST => 'Finalist',
                        PosterSubmission::STATUS_WINNER => 'Winner',
                        PosterSubmission::STATUS_REJECTED => 'Rejected',
                        default => $state,
                    }),
                TextColumn::make('submitted_at')
                    ->dateTime('d M Y, H:i')
                    ->label(__('filament.poster_submissions.submitted')),
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
