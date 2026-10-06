<?php

namespace App\Filament\Resources\DigitalWorkshops\Tables;

use App\Enums\HandsOnStatus;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;

class DigitalWorkshopsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')
                    ->label(__('filament.digital_workshop.code'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->limit(25)
                    ->tooltip(fn ($record) => $record->name),

                TextColumn::make('event_date')
                    ->label(__('filament.digital_workshop.event_date'))
                    ->date('F j, Y')
                    ->sortable(),

                TextColumn::make('event_time')
                    ->label(__('filament.digital_workshop.event_time'))
                    ->time('H:i'),

                TextColumn::make('event_end_time')
                    ->label(__('filament.digital_workshop.event_end_time'))
                    ->time('H:i'),

                TextColumn::make('max_seats')
                    ->label(__('filament.digital_workshop.max_seats'))
                    ->numeric()
                    ->sortable(),

                TextColumn::make('registrations_count')
                    ->label(__('filament.digital_workshop.registered'))
                    ->state(fn ($record) => $record->getRegisteredCount())
                    ->numeric(),

                TextColumn::make('available_seats')
                    ->label(__('filament.digital_workshop.available'))
                    ->state(fn ($record) => $record->getAvailableSeats())
                    ->numeric()
                    ->color(function ($record) {
                        $available = $record->getAvailableSeats();
                        if ($available === 0) {
                            return 'danger';
                        }
                        if ($available <= 5) {
                            return 'warning';
                        }

                        return 'success';
                    }),

                TextColumn::make('price')
                    ->label(__('filament.digital_workshop.price'))
                    ->money('IDR')
                    ->sortable(),

                TextColumn::make('status')
                    ->label(__('filament.digital_workshop.status'))
                    ->badge()
                    ->color(fn (HandsOnStatus $state): string => $state->getColor())
                    ->formatStateUsing(fn (HandsOnStatus $state): string => $state->getLabel())
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label(__('filament.digital_workshop.status'))
                    ->options(HandsOnStatus::class),
            ])
            ->recordActions([
                EditAction::make()
                    ->visible(fn (): bool => auth()->user()?->can('update digital workshops') ?? false),

                Action::make('publish')
                    ->label('Publish')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn ($record): bool => $record?->status === HandsOnStatus::DRAFT
                        && (auth()->user()?->can('update digital workshops') ?? false))
                    ->action(function ($record) {
                        $record->update(['status' => HandsOnStatus::PUBLISHED]);
                        Notification::make()
                            ->success()
                            ->title('Digital Workshop published successfully')
                            ->send();
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->visible(fn (): bool => auth()->user()?->can('delete digital workshops') ?? false),

                    BulkAction::make('publish')
                        ->label('Publish')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->visible(fn (): bool => auth()->user()?->can('update digital workshops') ?? false)
                        ->requiresConfirmation()
                        ->modalHeading('Publish Digital Workshop entries')
                        ->modalDescription('Are you sure you want to publish the selected Digital Workshop entries?')
                        ->action(function (Collection $records) {
                            $records->each->update(['status' => HandsOnStatus::PUBLISHED]);
                            Notification::make()
                                ->success()
                                ->title('Selected Digital Workshop entries published successfully')
                                ->send();
                        }),
                ]),
            ])
            ->defaultSort('sort_order');
    }
}
