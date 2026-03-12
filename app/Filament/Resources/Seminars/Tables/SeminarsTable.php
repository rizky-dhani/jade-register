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

                TextColumn::make('formatted_original_price')
                    ->label('Original Price')
                    ->sortable(),

                TextColumn::make('formatted_discounted_price')
                    ->label('Discounted Price')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('early_bird_deadline')
                    ->label('Discount Until')
                    ->dateTime('M d, Y H:i')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('stock_limit')
                    ->label('Stock Limit')
                    ->sortable()
                    ->getStateUsing(fn ($record): string => $record->stock_limit === null ? 'Unlimited' : (string) $record->stock_limit),

                IconColumn::make('includes_lunch')
                    ->boolean()
                    ->label('Lunch'),

                IconColumn::make('is_early_bird')
                    ->boolean()
                    ->label('Early Bird'),

                TextColumn::make('applies_to')
                    ->label('Applies To')
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
                    ->label('Active'),

                TextColumn::make('sort_order')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('applies_to')
                    ->label('Applies To')
                    ->options([
                        'local' => 'Local (Indonesia)',
                        'international' => 'International',
                        'all' => 'All Participants',
                    ]),

                Filter::make('is_active')
                    ->label('Active Only')
                    ->query(fn (Builder $query) => $query->where('is_active', true)),

                Filter::make('is_early_bird')
                    ->label('Early Bird Only')
                    ->query(fn (Builder $query) => $query->where('is_early_bird', true)),

                Filter::make('includes_lunch')
                    ->label('Includes Lunch')
                    ->query(fn (Builder $query) => $query->where('includes_lunch', true)),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('sort_order');
    }
}
