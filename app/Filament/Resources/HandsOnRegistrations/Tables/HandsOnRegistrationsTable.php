<?php

namespace App\Filament\Resources\HandsOnRegistrations\Tables;

use App\Models\HandsOnRegistration;
use App\Services\RegistrationService;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class HandsOnRegistrationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query
                ->leftJoin('seminar_registrations', 'hands_on_registrations.seminar_registration_id', '=', 'seminar_registrations.id')
                ->join('hands_ons', 'hands_on_registrations.hands_on_id', '=', 'hands_ons.id')
                ->orderBy('hands_on_registrations.created_at', 'desc')
                ->orderBy('seminar_registrations.name_license')
                ->orderBy('hands_on_registrations.name_license')
                ->orderBy('hands_ons.event_date')
                ->select('hands_on_registrations.*'))
            ->columns([
                TextColumn::make('registration_code')
                    ->label(__('filament.hands_on_registrations.registration_code'))
                    ->state(fn (HandsOnRegistration $record): string => $record->registration_code ?? $record->seminarRegistration?->registration_code ?? '-')
                    ->searchable(query: fn (Builder $query, string $value) => $query->where(function (Builder $q) use ($value) {
                        $q->where('hands_on_registrations.registration_code', 'like', "%{$value}%")
                            ->orWhere('seminar_registrations.registration_code', 'like', "%{$value}%");
                    }))
                    ->sortable(query: fn (Builder $query, string $direction) => $query->orderBy('hands_on_registrations.registration_code', $direction)->orderBy('seminar_registrations.registration_code', $direction))
                    ->copyable()
                    ->copyMessage('Code copied')
                    ->copyMessageDuration(1500),

                TextColumn::make('seminarRegistration.name_license')
                    ->label(__('filament.hands_on_registrations.participant'))
                    ->state(fn (HandsOnRegistration $record): string => $record->seminarRegistration?->name_license ?? $record->name_license ?? $record->name ?? '-')
                    ->searchable(query: fn (Builder $query, string $value) => $query->where(function (Builder $q) use ($value) {
                        $q->where('seminar_registrations.name_license', 'like', "%{$value}%")
                            ->orWhere('hands_on_registrations.name_license', 'like', "%{$value}%")
                            ->orWhere('hands_on_registrations.name', 'like', "%{$value}%");
                    }))
                    ->sortable(query: fn (Builder $query, string $direction) => $query->orderBy('seminar_registrations.name_license', $direction)->orderBy('hands_on_registrations.name_license', $direction)),

                TextColumn::make('seminarRegistration.email')
                    ->label(__('seminar.email'))
                    ->state(fn (HandsOnRegistration $record): string => $record->seminarRegistration?->email ?? $record->email ?? '-')
                    ->searchable(query: fn (Builder $query, string $value) => $query->where(function (Builder $q) use ($value) {
                        $q->where('seminar_registrations.email', 'like', "%{$value}%")
                            ->orWhere('hands_on_registrations.email', 'like', "%{$value}%");
                    }))
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('handsOn.ho_code')
                    ->label(__('filament.hands_on_registrations.hands_on_event'))
                    ->searchable(query: fn (Builder $query, string $value) => $query->where('hands_ons.ho_code', 'like', "%{$value}%"))
                    ->sortable(query: fn (Builder $query, string $direction) => $query->orderBy('hands_ons.ho_code', $direction))
                    ->formatStateUsing(fn (HandsOnRegistration $record): string => $record->handsOn
                        ? "[{$record->handsOn->ho_code}] {$record->handsOn->name}"
                        : ''),

                TextColumn::make('handsOn.event_date')
                    ->label(__('filament.hands_on_registrations.event_date'))
                    ->date()
                    ->sortable(query: fn (Builder $query, string $direction) => $query->orderBy('hands_ons.event_date', $direction)),

                TextColumn::make('seminar_name')
                    ->label(__('filament.hands_on_registrations.seminar_name'))
                    ->state(fn (HandsOnRegistration $record): ?string => $record->seminarRegistration?->selected_seminar_label)
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('seminar_price')
                    ->label(__('filament.hands_on_registrations.seminar_price'))
                    ->state(fn (HandsOnRegistration $record): ?string => $record->seminarRegistration?->seminarPackage?->formatted_current_price)
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('hands_on_price')
                    ->label(__('filament.hands_on_registrations.hands_on_price'))
                    ->state(fn (HandsOnRegistration $record): ?string => $record->handsOn?->formatted_original_price)
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('total_price')
                    ->label(__('filament.hands_on_registrations.total_price'))
                    ->state(function (HandsOnRegistration $record): ?string {
                        $seminarAmount = $record->seminarRegistration?->amount ?? 0;
                        $handsOnPrice = $record->handsOn?->current_price ?? 0;
                        $total = $seminarAmount + $handsOnPrice;
                        $currency = $record->seminarRegistration?->currency ?? 'IDR';

                        if ($currency === 'USD') {
                            return '$'.number_format($total, 2);
                        }

                        return 'Rp '.number_format($total, 0, ',', '.');
                    })
                    ->toggleable(isToggledHiddenByDefault: true),

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
                SelectFilter::make('event_date')
                    ->label(__('filament.hands_on_registrations.event_date'))
                    ->options([
                        '2026-11-13' => '13 Nov 2026',
                        '2026-11-14' => '14 Nov 2026',
                        '2026-11-15' => '15 Nov 2026',
                    ])
                    ->query(fn (Builder $query, array $data) => $query->when(
                        $data['value'],
                        fn (Builder $query, string $value) => $query->whereDate('hands_ons.event_date', $value),
                    )),
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
                    ->slideOver()
                    ->modalContent(function (HandsOnRegistration $record) {
                        $url = asset('storage/'.$record->payment_proof_path);
                        $extension = pathinfo($record->payment_proof_path, PATHINFO_EXTENSION);

                        return view('components.payment-proof-modal', [
                            'url' => $url,
                            'extension' => strtolower($extension),
                        ]);
                    })
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
                Action::make('resendEmailConfirmation')
                    ->label(__('seminar.resend_email_confirmation'))
                    ->icon('heroicon-o-envelope')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading(__('seminar.resend_email_confirmation'))
                    ->modalDescription(__('seminar.resend_email_confirmation_description'))
                    ->modalSubmitActionLabel(__('seminar.resend_email_confirmation'))
                    ->action(function (HandsOnRegistration $record, RegistrationService $registrationService): void {
                        $registrationService->sendHandsOnAttendanceConfirmation($record);
                    })
                    ->successNotificationTitle(__('seminar.email_confirmation_resent')),
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
