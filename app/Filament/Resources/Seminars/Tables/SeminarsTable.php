<?php

namespace App\Filament\Resources\Seminars\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class SeminarsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('code')
                    ->searchable()
                    ->sortable()
                    ->fontFamily('mono')
                    ->size('sm'),

                IconColumn::make('is_early_bird')
                    ->boolean()
                    ->label(__('filament.seminars.early_bird')),

                TextColumn::make('seminar_price')
                    ->label(__('filament.seminars.seminar_price'))
                    ->getStateUsing(fn ($record): string => $record->is_early_bird && $record->discounted_price
                        ? $record->formatted_discounted_price
                        : $record->formatted_original_price)
                    ->sortable(),

                TextColumn::make('formatted_original_price')
                    ->label(__('filament.seminars.original_price'))
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('formatted_discounted_price')
                    ->label(__('filament.seminars.discounted_price'))
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('early_bird_deadline')
                    ->label(__('filament.seminars.discount_until'))
                    ->dateTime('M d, Y H:i')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('max_seats')
                    ->label(__('filament.seminars.max_seats'))
                    ->sortable()
                    ->getStateUsing(fn ($record): string => $record->max_seats === null ? 'Unlimited' : (string) $record->max_seats),

                IconColumn::make('includes_lunch')
                    ->boolean()
                    ->label(__('filament.seminars.lunch')),

                TextColumn::make('applies_to')
                    ->label(__('filament.seminars.applies_to'))
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'local' => 'Local',
                        'international' => 'International',
                        'all' => 'All',
                        default => $state,
                    })
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'local' => 'success',
                        'international' => 'info',
                        'all' => 'warning',
                        default => 'gray',
                    }),

                IconColumn::make('is_active')
                    ->boolean()
                    ->label(__('filament.seminars.active')),

                TextColumn::make('sort_order')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('applies_to')
                    ->label(__('filament.seminars.applies_to'))
                    ->options([
                        'local' => 'Local (Indonesia)',
                        'international' => 'International',
                        'all' => 'All Participants',
                    ]),

                Filter::make('is_active')
                    ->label(__('filament.seminars.active_only'))
                    ->query(fn (Builder $query) => $query->where('is_active', true)),

                Filter::make('is_early_bird')
                    ->label(__('filament.seminars.early_bird_only'))
                    ->query(fn (Builder $query) => $query->where('is_early_bird', true)),

                Filter::make('includes_lunch')
                    ->label(__('filament.seminars.includes_lunch'))
                    ->query(fn (Builder $query) => $query->where('includes_lunch', true)),
            ])
            ->recordActions([
                EditAction::make()
                    ->visible(fn (): bool => auth()->user()?->can('update seminars') ?? false),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->visible(fn (): bool => auth()->user()?->can('delete seminars') ?? false),
                ]),
            ])
            ->defaultSort('sort_order');
    }
}
