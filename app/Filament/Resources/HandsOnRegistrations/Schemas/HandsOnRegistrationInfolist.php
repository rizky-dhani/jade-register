<?php

namespace App\Filament\Resources\HandsOnRegistrations\Schemas;

use App\Filament\Resources\SeminarRegistrations\SeminarRegistrationResource;
use App\Models\HandsOnRegistration;
use Filament\Actions\Action;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class HandsOnRegistrationInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(6)
            ->components([
                Section::make(__('seminar.registration_code'))
                    ->columnSpan(2)
                    ->schema([
                        TextEntry::make('registration_code')
                            ->label(__('filament.hands_on_registrations.registration_code'))
                            ->state(fn (HandsOnRegistration $record): string => $record->registration_code ?? $record->seminarRegistration?->registration_code ?? '-')
                            ->copyable()
                            ->badge(),

                        TextEntry::make('registration_type')
                            ->label(__('filament.hands_on_registrations.type'))
                            ->badge()
                            ->color(fn (string $state): string => $state === 'bundled' ? 'info' : 'gray')
                            ->formatStateUsing(fn (string $state): string => ucfirst($state)),

                        TextEntry::make('payment_status')
                            ->label(__('filament.hands_on_registrations.payment_status'))
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'pending' => 'warning',
                                'verified' => 'success',
                                'rejected' => 'danger',
                                default => 'gray',
                            })
                            ->formatStateUsing(fn (string $state): string => ucfirst($state)),

                        TextEntry::make('created_at')
                            ->label(__('filament.hands_on_registrations.created_at'))
                            ->dateTime('d M Y H:i'),
                    ])->columns(2),

                Section::make(__('seminar.personal_information'))
                    ->columnSpan(4)
                    ->schema([
                        TextEntry::make('participant_name')
                            ->label(__('filament.hands_on_registrations.participant'))
                            ->state(fn (HandsOnRegistration $record): string => $record->seminarRegistration?->name_license ?? $record->name_license ?? $record->name ?? '-'),

                        TextEntry::make('recipient_email')
                            ->label(__('seminar.email'))
                            ->state(fn (HandsOnRegistration $record): ?string => $record->recipientEmail())
                            ->copyable(),

                        TextEntry::make('phone')
                            ->label(__('filament.hands_on_registrations.phone'))
                            ->state(fn (HandsOnRegistration $record): ?string => $record->seminarRegistration?->phone ?? $record->phone),

                        TextEntry::make('nik')
                            ->label(__('seminar.nik'))
                            ->state(fn (HandsOnRegistration $record): ?string => $record->seminarRegistration?->nik ?? $record->nik),

                        TextEntry::make('pdgi_branch')
                            ->label(__('seminar.pdgi_branch'))
                            ->state(fn (HandsOnRegistration $record): ?string => $record->seminarRegistration?->pdgi_branch ?? $record->pdgi_branch),

                        TextEntry::make('kompetensi')
                            ->label(__('seminar.competency'))
                            ->state(fn (HandsOnRegistration $record): ?string => $record->seminarRegistration?->kompetensi ?? $record->kompetensi),
                    ])->columns(3),

                Section::make(__('filament.navigation.hands_on'))
                    ->columnSpan(3)
                    ->schema([
                        TextEntry::make('handsOn.name')
                            ->label(__('filament.hands_on_registrations.hands_on_event')),

                        TextEntry::make('handsOn.event_date')
                            ->label(__('filament.hands_on_registrations.event_date'))
                            ->date('d M Y'),
                    ])->columns(2),

                Section::make(__('seminar.linked_seminar_registration'))
                    ->columnSpan(3)
                    ->schema([
                        TextEntry::make('seminarRegistration.registration_code')
                            ->label(__('filament.hands_on_registrations.seminar_registration'))
                            ->copyable()
                            ->badge()
                            ->url(fn (HandsOnRegistration $record): ?string => $record->seminarRegistration
                                ? SeminarRegistrationResource::getUrl('view', ['record' => $record->seminarRegistration])
                                : null),

                        TextEntry::make('seminarRegistration.name_license')
                            ->label(__('filament.hands_on_registrations.participant')),

                        TextEntry::make('seminarRegistration.selected_seminar_label')
                            ->label(__('filament.hands_on_registrations.seminar_name'))
                            ->visible(fn (HandsOnRegistration $record): bool => $record->seminarRegistration !== null),
                    ])->columns(3)
                    ->visible(fn (HandsOnRegistration $record): bool => $record->seminar_registration_id !== null),

                Section::make(__('seminar.payment_information'))
                    ->columnSpan(6)
                    ->schema([
                        TextEntry::make('payment_method')
                            ->label(__('seminar.payment_method'))
                            ->formatStateUsing(fn (string $state): string => match ($state) {
                                'bank_transfer' => __('seminar.bank_transfer'),
                                'qris' => __('seminar.qris'),
                                default => $state,
                            }),

                        TextEntry::make('payment_proof_path')
                            ->label(__('filament.hands_on_registrations.payment_proof'))
                            ->badge()
                            ->color('primary')
                            ->icon('heroicon-o-eye')
                            ->formatStateUsing(fn () => __('filament.actions.view'))
                            ->action(
                                Action::make('viewHandsOnPaymentProof')
                                    ->label(__('filament.hands_on_registrations.view_payment_proof'))
                                    ->slideOver()
                                    ->modalContent(function (HandsOnRegistration $record) {
                                        $url = asset('storage/'.$record->payment_proof_path);
                                        $extension = pathinfo($record->payment_proof_path, PATHINFO_EXTENSION);

                                        return view('components.payment-proof-modal', compact('url', 'extension'));
                                    })
                                    ->modalCancelAction(false),
                            )
                            ->visible(fn (HandsOnRegistration $record): bool => $record->payment_proof_path !== null),

                        TextEntry::make('verified_at')
                            ->label(__('filament.hands_on_registrations.verified_at'))
                            ->dateTime('d M Y H:i')
                            ->placeholder('—'),
                    ])->columns(3),
            ]);
    }
}
