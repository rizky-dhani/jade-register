<?php

namespace App\Filament\Resources\DigitalWorkshopRegistrations\Tables;

use App\Models\DigitalWorkshop;
use App\Models\DigitalWorkshopRegistration;
use App\Services\RegistrationService;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;

class DigitalWorkshopRegistrationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('registration_code')
                    ->label(__('seminar.registration_code'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('name_license')
                    ->label(__('filament.digital_workshop_registrations.name_license'))
                    ->searchable()
                    ->limit(25)
                    ->tooltip(fn ($record) => $record->name_license)
                    ->toggleable(),

                TextColumn::make('name')
                    ->label(__('seminar.name'))
                    ->searchable()
                    ->limit(25)
                    ->tooltip(fn ($record) => $record->name)
                    ->toggleable(),

                TextColumn::make('email')
                    ->label(__('seminar.email'))
                    ->searchable(),

                TextColumn::make('digitalWorkshop.code')
                    ->label(__('filament.digital_workshop_registrations.workshop'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('amount')
                    ->label(__('filament.digital_workshop_registrations.amount'))
                    ->money('IDR')
                    ->sortable(),

                TextColumn::make('registration_type')
                    ->label(__('filament.digital_workshop_registrations.registration_type'))
                    ->badge()
                    ->color(fn (string $state): string => $state === 'bundled' ? 'success' : 'gray'),

                TextColumn::make('payment_status')
                    ->label(__('filament.digital_workshop_registrations.payment_status'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'verified' => 'success',
                        'rejected' => 'danger',
                        default => 'warning',
                    }),

                TextColumn::make('created_at')
                    ->label(__('filament.digital_workshop_registrations.created_at'))
                    ->dateTime('d M Y H:i')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('payment_status')
                    ->label(__('filament.digital_workshop_registrations.payment_status'))
                    ->options([
                        'pending' => 'Pending',
                        'verified' => 'Verified',
                        'rejected' => 'Rejected',
                    ]),

                SelectFilter::make('registration_type')
                    ->label(__('filament.digital_workshop_registrations.registration_type'))
                    ->options([
                        'standalone' => __('filament.digital_workshop_registrations.standalone'),
                        'bundled' => __('filament.digital_workshop_registrations.bundled'),
                    ]),

                SelectFilter::make('digital_workshop_id')
                    ->label(__('filament.digital_workshop_registrations.workshop'))
                    ->options(DigitalWorkshop::orderBy('sort_order')->pluck('name', 'id')),
            ])
            ->recordActions([
                Action::make('viewPaymentProof')
                    ->label(__('filament.digital_workshop_registrations.view_payment_proof'))
                    ->icon('heroicon-o-photo')
                    ->visible(fn (DigitalWorkshopRegistration $record): bool => $record->payment_proof_path !== null)
                    ->modalHeading(__('filament.digital_workshop_registrations.view_payment_proof'))
                    ->slideOver()
                    ->modalContent(function (DigitalWorkshopRegistration $record) {
                        return view('components.payment-proof-modal', [
                            'url' => asset('storage/'.$record->payment_proof_path),
                            'extension' => strtolower(pathinfo($record->payment_proof_path, PATHINFO_EXTENSION)),
                        ]);
                    }),

                Action::make('verifyPayment')
                    ->label(__('seminar.verify_payment'))
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn (DigitalWorkshopRegistration $record): bool => $record->payment_status !== 'verified'
                        && (auth()->user()?->can('update digital workshop registrations') ?? false))
                    ->action(function (DigitalWorkshopRegistration $record): void {
                        $record->update([
                            'payment_status' => 'verified',
                            'verified_at' => now(),
                        ]);

                        Notification::make()->success()->title(__('seminar.verify_payment'))->send();
                    }),

                Action::make('rejectPayment')
                    ->label(__('seminar.reject_payment'))
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn (DigitalWorkshopRegistration $record): bool => $record->payment_status !== 'rejected'
                        && (auth()->user()?->can('update digital workshop registrations') ?? false))
                    ->schema([
                        Textarea::make('rejection_reason')
                            ->label(__('filament.digital_workshop_registrations.reject_reason'))
                            ->required()
                            ->rows(3),
                    ])
                    ->action(function (DigitalWorkshopRegistration $record, array $data): void {
                        $record->update([
                            'payment_status' => 'rejected',
                            'rejection_reason' => $data['rejection_reason'],
                        ]);

                        Notification::make()->success()->title(__('seminar.reject_payment'))->send();
                    }),

                Action::make('resendEmailConfirmation')
                    ->label(__('seminar.resend_email_confirmation'))
                    ->icon('heroicon-o-envelope')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading(__('seminar.resend_email_confirmation'))
                    ->modalDescription(__('seminar.resend_email_confirmation_description'))
                    ->modalSubmitActionLabel(__('seminar.resend_email_confirmation'))
                    ->visible(fn (DigitalWorkshopRegistration $record): bool => $record->recipientEmail() !== null)
                    ->action(function (DigitalWorkshopRegistration $record, RegistrationService $registrationService): void {
                        $registrationService->sendDigitalWorkshopSubmissionConfirmation($record);
                    })
                    ->successNotificationTitle(__('seminar.email_confirmation_resent')),

                EditAction::make()
                    ->visible(fn (): bool => auth()->user()?->can('update digital workshop registrations') ?? false),
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
            ])
            ->defaultSort('created_at', 'desc');
    }
}
