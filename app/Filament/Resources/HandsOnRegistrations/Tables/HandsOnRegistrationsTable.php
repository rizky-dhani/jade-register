<?php

namespace App\Filament\Resources\HandsOnRegistrations\Tables;

use App\Models\HandsOnRegistration;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class HandsOnRegistrationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query
                ->join('seminar_registrations', 'hands_on_registrations.seminar_registration_id', '=', 'seminar_registrations.id')
                ->join('hands_ons', 'hands_on_registrations.hands_on_id', '=', 'hands_ons.id')
                ->orderBy('seminar_registrations.name_license')
                ->orderBy('hands_ons.event_date')
                ->select('hands_on_registrations.*'))
            ->columns([
                TextColumn::make('seminarRegistration.registration_code')
                    ->label(__('filament.hands_on_registrations.registration_code'))
                    ->searchable()
                    ->sortable(query: fn (Builder $query, string $direction) => $query->orderBy('seminar_registrations.registration_code', $direction)),

                TextColumn::make('seminarRegistration.name_license')
                    ->label(__('filament.hands_on_registrations.participant'))
                    ->searchable()
                    ->sortable(query: fn (Builder $query, string $direction) => $query->orderBy('seminar_registrations.name_license', $direction)),

                TextColumn::make('seminarRegistration.email')
                    ->label(__('seminar.email'))
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('handsOn.ho_code')
                    ->label(__('filament.hands_on_registrations.hands_on_event'))
                    ->searchable()
                    ->sortable(query: fn (Builder $query, string $direction) => $query->orderBy('hands_ons.ho_code', $direction))
                    ->formatStateUsing(fn (HandsOnRegistration $record): string => $record->handsOn
                        ? "[{$record->handsOn->ho_code}] {$record->handsOn->name}"
                        : ''),

                TextColumn::make('handsOn.event_date')
                    ->label(__('filament.hands_on_registrations.event_date'))
                    ->date()
                    ->sortable(query: fn (Builder $query, string $direction) => $query->orderBy('hands_ons.event_date', $direction)),

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
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('verifyPayment')
                        ->label(__('seminar.verify_payment'))
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->deselectRecordsAfterCompletion()
                        ->visible(fn (): bool => auth()->user()?->hasRole('Super Admin') ?? false)
                        ->action(fn (Collection $records) => $records->each->update([
                            'payment_status' => 'verified',
                            'verified_at' => now(),
                        ])),
                    DeleteBulkAction::make()
                        ->visible(fn (): bool => auth()->user()?->hasRole('Super Admin') ?? false),
                ]),
            ]);
    }
}
