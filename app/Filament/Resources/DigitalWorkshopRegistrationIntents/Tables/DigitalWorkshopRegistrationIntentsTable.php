<?php

namespace App\Filament\Resources\DigitalWorkshopRegistrationIntents\Tables;

use App\Models\DigitalWorkshop;
use App\Models\DigitalWorkshopRegistrationIntent;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class DigitalWorkshopRegistrationIntentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('email')
                    ->label(__('filament.digital_workshop_intents.email'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('name_license')
                    ->label(__('filament.digital_workshop_intents.name'))
                    ->getStateUsing(fn (DigitalWorkshopRegistrationIntent $record): ?string => $record->participantName())
                    ->searchable(['name', 'name_license'])
                    ->limit(25)
                    ->tooltip(fn (DigitalWorkshopRegistrationIntent $record): ?string => $record->participantName()),

                TextColumn::make('digitalWorkshop.code')
                    ->label(__('filament.digital_workshop_intents.workshop'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('status')
                    ->label(__('filament.digital_workshop_intents.status'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'awaiting_seminar' => 'warning',
                        'fulfilled' => 'success',
                        'expired' => 'gray',
                        default => 'gray',
                    }),

                TextColumn::make('payment_proof_path')
                    ->label(__('filament.digital_workshop_intents.payment_proof'))
                    ->formatStateUsing(fn (?string $state): string => $state ? __('filament.digital_workshop_intents.proof_uploaded') : '-')
                    ->toggleable(),

                TextColumn::make('seminar_registration_id')
                    ->label(__('filament.digital_workshop_intents.seminar_registration'))
                    ->formatStateUsing(fn (?int $state): string => $state ? '#'.$state : '-')
                    ->url(fn (DigitalWorkshopRegistrationIntent $record): ?string => $record->seminar_registration_id
                        ? route('filament.dashboard.resources.seminar-registrations.edit', ['record' => $record->seminar_registration_id])
                        : null)
                    ->color('primary'),

                TextColumn::make('created_at')
                    ->label(__('filament.digital_workshop_intents.created_at'))
                    ->dateTime('d M Y H:i')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label(__('filament.digital_workshop_intents.status'))
                    ->options([
                        'awaiting_seminar' => __('filament.digital_workshop_intents.awaiting_seminar'),
                        'fulfilled' => __('filament.digital_workshop_intents.fulfilled'),
                        'expired' => __('filament.digital_workshop_intents.expired'),
                    ]),

                SelectFilter::make('digital_workshop_id')
                    ->label(__('filament.digital_workshop_intents.workshop'))
                    ->options(DigitalWorkshop::orderBy('sort_order')->pluck('name', 'id')),
            ])
            ->recordActions([
                Action::make('viewPaymentProof')
                    ->label(__('filament.digital_workshop_intents.view_payment_proof'))
                    ->icon('heroicon-o-photo')
                    ->visible(fn (DigitalWorkshopRegistrationIntent $record): bool => $record->payment_proof_path !== null)
                    ->modalHeading(__('filament.digital_workshop_intents.view_payment_proof'))
                    ->slideOver()
                    ->modalContent(function (DigitalWorkshopRegistrationIntent $record) {
                        return view('components.payment-proof-modal', [
                            'url' => asset('storage/'.$record->payment_proof_path),
                            'extension' => strtolower(pathinfo($record->payment_proof_path, PATHINFO_EXTENSION)),
                        ]);
                    }),

                Action::make('expire')
                    ->label(__('filament.digital_workshop_intents.expire'))
                    ->icon('heroicon-o-clock')
                    ->color('gray')
                    ->requiresConfirmation()
                    ->modalHeading(__('filament.digital_workshop_intents.expire'))
                    ->modalDescription(__('filament.digital_workshop_intents.expire_description'))
                    ->visible(fn (DigitalWorkshopRegistrationIntent $record): bool => $record->isAwaiting()
                        && (auth()->user()?->can('update digital workshop intents') ?? false))
                    ->action(function (DigitalWorkshopRegistrationIntent $record): void {
                        $record->update([
                            'status' => 'expired',
                            'expired_at' => now(),
                        ]);

                        Notification::make()->success()->title(__('filament.digital_workshop_intents.expired'))->send();
                    }),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
