<?php

namespace App\Filament\Resources\HandsOnRegistrations\Tables;

use App\Models\SeminarRegistration;
use Filament\Actions\Action;
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
                TextColumn::make('registration_code')
                    ->label(__('filament.hands_on_registrations.registration_code'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('name_license')
                    ->label(__('filament.hands_on_registrations.participant'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('email')
                    ->label(__('seminar.email'))
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('handsOnRegistrations')
                    ->label(__('filament.hands_on_registrations.hands_on_event'))
                    ->formatStateUsing(fn (SeminarRegistration $record): string => $record->handsOnRegistrations
                        ->filter(fn ($reg) => $reg->handsOn)
                        ->map(fn ($reg) => $reg->handsOn->ho_code
                            ? "[{$reg->handsOn->ho_code}] {$reg->handsOn->name}"
                            : $reg->handsOn->name)
                        ->implode('<br>'))
                    ->html(),

                TextColumn::make('handsOnRegistrations')
                    ->label(__('filament.hands_on_registrations.event_date'))
                    ->formatStateUsing(fn (SeminarRegistration $record): string => $record->handsOnRegistrations
                        ->filter(fn ($reg) => $reg->handsOn && $reg->handsOn->event_date)
                        ->map(fn ($reg) => $reg->handsOn->event_date->format('F j, Y'))
                        ->implode('<br>'))
                    ->html(),

                TextColumn::make('hands_on_total_amount')
                    ->label(__('filament.hands_on_registrations.total_amount'))
                    ->money('IDR')
                    ->sortable(),

                TextColumn::make('payment_status')
                    ->label(__('filament.hands_on_registrations.payment_status'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'verified' => 'success',
                        'rejected' => 'danger',
                        default => 'gray',
                    }),

                TextColumn::make('created_at')
                    ->label(__('filament.hands_on_registrations.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
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
                    ->visible(fn (SeminarRegistration $record): bool => $record->payment_proof_path !== null)
                    ->modalHeading(__('filament.hands_on_registrations.view_payment_proof'))
                    ->modalContent(fn (SeminarRegistration $record): View => view(
                        'filament.modals.payment-proof',
                        ['record' => $record]
                    ))
                    ->extraModalFooterActions(function (SeminarRegistration $record): array {
                        if ($record->payment_status === 'pending') {
                            return [
                                Action::make('verifyPayment')
                                    ->label(__('seminar.verify_payment'))
                                    ->icon('heroicon-o-check-circle')
                                    ->color('warning')
                                    ->requiresConfirmation()
                                    ->action(function (SeminarRegistration $record): void {
                                        $record->update([
                                            'payment_status' => 'verified',
                                            'verified_at' => now(),
                                        ]);

                                        // Verify all associated pivot records
                                        $record->handsOnRegistrations()->update([
                                            'payment_status' => 'verified',
                                            'verified_at' => now(),
                                        ]);
                                    }),
                            ];
                        }

                        return [];
                    }),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
