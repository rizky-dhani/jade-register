<?php

namespace App\Filament\Resources\SeminarRegistrations\Schemas;

use App\Models\SeminarRegistration;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ViewEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class SeminarRegistrationInfolist
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
                            ->label(__('seminar.registration_code'))
                            ->copyable()
                            ->badge(),

                        TextEntry::make('payment_status')
                            ->label(__('seminar.payment_status'))
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
                    ])->columns(3),

                Section::make(__('seminar.personal_information'))
                    ->columnSpan(4)
                    ->schema([
                        TextEntry::make('name_license')
                            ->label(__('seminar.name_plataran')),

                        TextEntry::make('email')
                            ->label(__('seminar.email'))
                            ->copyable(),

                        TextEntry::make('phone')
                            ->label(__('seminar.whatsapp_number')),

                        TextEntry::make('nik')
                            ->label(__('seminar.nik')),

                        TextEntry::make('pdgi_branch')
                            ->label(__('seminar.pdgi_branch')),

                        TextEntry::make('kompetensi')
                            ->label(__('seminar.competency')),

                        TextEntry::make('country.name')
                            ->label(__('seminar.country'))
                            ->visible(fn (SeminarRegistration $record): bool => ! ($record->country?->is_indonesia ?? true)),

                        TextEntry::make('status')
                            ->label(__('seminar.status'))
                            ->formatStateUsing(fn (string $state): string => match ($state) {
                                'Dentist' => __('seminar.dentist'),
                                'Student' => __('seminar.student'),
                                default => $state,
                            })
                            ->visible(fn (SeminarRegistration $record): bool => ! ($record->country?->is_indonesia ?? true)),
                    ])->columns(6),

                Section::make(__('seminar.registration_package'))
                    ->schema([
                        TextEntry::make('selected_seminar')
                            ->label(__('seminar.selected_package')),

                        TextEntry::make('amount')
                            ->label(__('seminar.amount'))
                            ->money('IDR'),

                        TextEntry::make('hands_on_total_amount')
                            ->label(__('seminar.hands_on_total_amount'))
                            ->money('IDR')
                            ->default(0),

                        TextEntry::make('payment_method')
                            ->label(__('seminar.payment_method'))
                            ->formatStateUsing(fn (string $state): string => match ($state) {
                                'bank_transfer' => __('seminar.bank_transfer'),
                                'qris' => __('seminar.qris'),
                                default => $state,
                            }),
                    ])->columns(2),

                Section::make(__('seminar.hands_on_sessions'))
                    ->schema([
                        TextEntry::make('wants_hands_on')
                            ->label(__('seminar.wants_hands_on'))
                            ->formatStateUsing(fn (bool $state): string => $state ? __('seminar.yes') : __('seminar.no'))
                            ->badge()
                            ->color(fn (bool $state): string => $state ? 'success' : 'gray'),

                        ViewEntry::make('handsOnRegistrations')
                            ->label(__('seminar.hands_on_registrations'))
                            ->view('filament.infolists.hands-on-list')
                            ->visible(fn (SeminarRegistration $record): bool => $record->handsOnRegistrations->isNotEmpty()),
                    ])->columns(1),

                Section::make(__('seminar.payment_proof'))
                    ->schema([
                        ViewEntry::make('payment_proof_path')
                            ->label(__('seminar.payment_proof'))
                            ->view('filament.infolists.payment-proof-preview')
                            ->visible(fn (SeminarRegistration $record): bool => $record->payment_proof_path !== null),

                        TextEntry::make('verified_at')
                            ->label(__('seminar.verified_at'))
                            ->dateTime('d M Y H:i')
                            ->placeholder('—'),

                        TextEntry::make('verifiedBy.name')
                            ->label(__('seminar.verified_by'))
                            ->placeholder('—'),
                    ])->columns(2),
            ]);
    }
}
