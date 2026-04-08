<?php

namespace App\Filament\Resources\HandsOnRegistrations\Tables;

use App\Models\HandsOnRegistration;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Contracts\View\View;

class HandsOnRegistrationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('seminarRegistration.registration_code')
                    ->label(__('filament.hands_on_registrations.registration_code'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('seminarRegistration.name')
                    ->label(__('filament.hands_on_registrations.participant'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('handsOn.name')
                    ->label(__('filament.hands_on_registrations.hands_on_event'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('handsOn.event_date')
                    ->label(__('filament.hands_on_registrations.event_date'))
                    ->date('F j, Y')
                    ->sortable(),

                TextColumn::make('registration_type')
                    ->label(__('filament.hands_on_registrations.type'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'combined' => 'info',
                        'standalone' => 'warning',
                        default => 'gray',
                    }),

                TextColumn::make('payment_status')
                    ->label(__('filament.hands_on_registrations.payment_status'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'verified' => 'success',
                        'rejected' => 'danger',
                        default => 'gray',
                    }),

                TextColumn::make('verified_at')
                    ->label(__('filament.hands_on_registrations.verified_at'))
                    ->dateTime()
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label(__('filament.hands_on_registrations.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('registration_type')
                    ->options([
                        'combined' => 'Combined',
                        'standalone' => 'Standalone',
                    ]),

                SelectFilter::make('payment_status')
                    ->label(__('filament.hands_on_registrations.payment_status'))
                    ->options([
                        'pending' => 'Pending',
                        'verified' => 'Verified',
                        'rejected' => 'Rejected',
                    ]),
            ])
            ->recordActions([
                Action::make('viewPaymentProof')
                    ->label(__('filament.hands_on_registrations.view_payment_proof'))
                    ->icon('heroicon-o-photo')
                    ->visible(fn (HandsOnRegistration $record): bool => $record->payment_proof_path !== null)
                    ->modalHeading(__('filament.hands_on_registrations.view_payment_proof'))
                    ->modalContent(fn (HandsOnRegistration $record): View => view(
                        'filament.modals.payment-proof',
                        ['record' => $record]
                    ))
                    ->extraModalFooterActions(function (HandsOnRegistration $record): array {
                        if ($record->payment_status === 'pending') {
                            return [
                                Action::make('verifyPayment')
                                    ->label(__('seminar.verify_payment'))
                                    ->icon('heroicon-o-check-circle')
                                    ->color('warning')
                                    ->requiresConfirmation()
                                    ->action(function (HandsOnRegistration $record): void {
                                        $record->update([
                                            'payment_status' => 'verified',
                                            'verified_at' => now(),
                                        ]);
                                    }),
                            ];
                        }

                        return [];
                    }),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
