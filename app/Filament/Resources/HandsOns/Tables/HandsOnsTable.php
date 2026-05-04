<?php

namespace App\Filament\Resources\HandsOns\Tables;

use App\Enums\HandsOnStatus;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class HandsOnsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('ho_code')
                    ->label('HO Code')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('event_date')
                    ->date('F j, Y')
                    ->sortable(),

                TextColumn::make('max_seats')
                    ->label(__('filament.hands_on.max_seats'))
                    ->numeric()
                    ->sortable(),

                TextColumn::make('registrations_count')
                    ->label(__('filament.hands_on.registered'))
                    ->counts('handsOnRegistrations')
                    ->numeric(),

                TextColumn::make('available_seats')
                    ->label(__('filament.hands_on.available'))
                    ->state(function ($record) {
                        $registered = $record->handsOnRegistrations()
                            ->whereIn('payment_status', ['pending', 'verified'])
                            ->count();

                        return max(0, $record->max_seats - $registered);
                    })
                    ->numeric()
                    ->color(function ($record) {
                        $registered = $record->handsOnRegistrations()
                            ->whereIn('payment_status', ['pending', 'verified'])
                            ->count();
                        $available = max(0, $record->max_seats - $registered);
                        if ($available === 0) {
                            return 'danger';
                        }
                        if ($available <= 5) {
                            return 'warning';
                        }

                        return 'success';
                    }),

                TextColumn::make('original_price')
                    ->label(__('filament.hands_on.original_price'))
                    ->money('IDR')
                    ->sortable(),

                TextColumn::make('discounted_price')
                    ->label(__('filament.hands_on.discounted_price'))
                    ->money('IDR')
                    ->sortable(),

                TextColumn::make('status')
                    ->label(__('filament.hands_on.status'))
                    ->badge()
                    ->color(fn (HandsOnStatus $state): string => $state->getColor())
                    ->formatStateUsing(fn (HandsOnStatus $state): string => $state->getLabel())
                    ->sortable(),

                IconColumn::make('is_active')
                    ->boolean()
                    ->label(__('filament.hands_on.active')),
            ])
            ->filters([
                SelectFilter::make('event_date')
                    ->options([
                        '2026-11-13' => 'November 13, 2026',
                        '2026-11-14' => 'November 14, 2026',
                        '2026-11-15' => 'November 15, 2026',
                    ]),

                Filter::make('is_active')
                    ->label(__('filament.hands_on.active_only'))
                    ->query(fn (Builder $query) => $query->where('is_active', true)),

                Filter::make('has_available_seats')
                    ->label(__('filament.hands_on.has_available_seats'))
                    ->query(function (Builder $query) {
                        $query->whereHas('handsOnRegistrations', function ($q) {
                            $q->whereIn('payment_status', ['pending', 'verified']);
                        }, '<', $query->raw('max_seats'));
                    }),
            ])
            ->recordActions([
                EditAction::make()
                    ->visible(fn (): bool => auth()->user()?->can('update hands ons') ?? false),

                Action::make('publish')
                    ->label('Publish')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn ($record): bool => $record?->status === HandsOnStatus::DRAFT
                        && (auth()->user()?->can('update hands ons') ?? false))
                    ->action(function ($record) {
                        $record->update(['status' => HandsOnStatus::PUBLISHED]);
                        Notification::make()
                            ->success()
                            ->title('Hands-On published successfully')
                            ->send();
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->visible(fn (): bool => auth()->user()?->can('delete hands ons') ?? false),

                    BulkAction::make('publish')
                        ->label('Publish')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->visible(fn (): bool => auth()->user()?->can('update hands ons') ?? false)
                        ->action(function (Collection $records) {
                            $records->each->update(['status' => HandsOnStatus::PUBLISHED]);
                            Notification::make()
                                ->success()
                                ->title('Selected Hands-On entries published successfully')
                                ->send();
                        }),
                ]),
            ])
            ->defaultSort('event_date');
    }
}
