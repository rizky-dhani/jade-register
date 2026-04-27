<?php

namespace App\Filament\Resources\Addons\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class AddonsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('filament.addons.name'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('code')
                    ->label(__('filament.addons.code'))
                    ->searchable()
                    ->sortable()
                    ->badge(),

                TextColumn::make('description')
                    ->label(__('filament.addons.description'))
                    ->limit(50)
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('price')
                    ->label(__('filament.addons.price'))
                    ->money('IDR')
                    ->sortable(),

                TextColumn::make('currency')
                    ->label(__('filament.addons.currency'))
                    ->searchable()
                    ->badge()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('max_seats')
                    ->label(__('filament.addons.max_seats'))
                    ->numeric()
                    ->sortable()
                    ->placeholder(__('filament.addons.unlimited')),

                TextColumn::make('registered_count')
                    ->label(__('filament.addons.registered'))
                    ->state(fn ($record): int => $record->addonRegistrations()->where('payment_status', 'verified')->count())
                    ->numeric()
                    ->sortable()
                    ->toggleable(),

                IconColumn::make('is_active')
                    ->label(__('filament.addons.is_active'))
                    ->boolean()
                    ->sortable(),

                TextColumn::make('available_from')
                    ->label(__('filament.addons.available_from'))
                    ->date('d M Y')
                    ->sortable()
                    ->placeholder('—'),

                TextColumn::make('available_until')
                    ->label(__('filament.addons.available_until'))
                    ->date('d M Y')
                    ->sortable()
                    ->placeholder('—'),

                TextColumn::make('sort_order')
                    ->label(__('filament.addons.sort_order'))
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->label(__('filament.addons.created_at'))
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->label(__('filament.addons.updated_at'))
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TernaryFilter::make('is_active')
                    ->label(__('filament.addons.is_active'))
                    ->trueLabel(__('filament.addons.active_only'))
                    ->falseLabel(__('filament.addons.inactive_only')),

                SelectFilter::make('currency')
                    ->label(__('filament.addons.currency'))
                    ->options([
                        'IDR' => 'IDR',
                        'USD' => 'USD',
                    ]),
            ])
            ->recordActions([
                EditAction::make()
                    ->visible(fn (): bool => auth()->user()?->can('update addons') ?? false),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->visible(fn (): bool => auth()->user()?->can('delete addons') ?? false),
                ]),
            ]);
    }
}
