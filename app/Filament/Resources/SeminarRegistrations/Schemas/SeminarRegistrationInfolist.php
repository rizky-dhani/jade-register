<?php

namespace App\Filament\Resources\SeminarRegistrations\Schemas;

use App\Models\SeminarRegistration;
use Filament\Actions\Action;
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
                    ->columnSpan(3)
                    ->schema([
                        TextEntry::make('selected_seminar_label')
                            ->label(__('seminar.selected_package')),

                        ViewEntry::make('seminar_package')
                            ->label(__('seminar.package_price_breakdown'))
                            ->view('filament.infolists.seminar-price-breakdown')
                            ->visible(fn (SeminarRegistration $record): bool => $record->seminarPackage !== null),

                        TextEntry::make('amount')
                            ->label(__('seminar.amount'))
                            ->money('IDR'),

                        TextEntry::make('hands_on_total_amount')
                            ->label(__('seminar.hands_on_total_amount'))
                            ->money('IDR')
                            ->default(0),

                        TextEntry::make('addons_total_amount')
                            ->label(__('seminar.addons_total_amount'))
                            ->money('IDR')
                            ->default(0)
                            ->visible(fn (SeminarRegistration $record): bool => $record->addonRegistrations->isNotEmpty()),

                        ViewEntry::make('addonRegistrations')
                            ->label(__('seminar.selected_addons'))
                            ->view('filament.infolists.addon-list')
                            ->visible(fn (SeminarRegistration $record): bool => $record->addonRegistrations->isNotEmpty()),

                        TextEntry::make('payment_method')
                            ->label(__('seminar.payment_method'))
                            ->formatStateUsing(fn (string $state): string => match ($state) {
                                'bank_transfer' => __('seminar.bank_transfer'),
                                'qris' => __('seminar.qris'),
                                default => $state,
                            }),
                    ])->columns(3),

                Section::make(__('seminar.hands_on_sessions'))
                    ->columnSpan(3)
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
                    ])->columns(3),

                Section::make(__('seminar.payment_proof'))
                    ->columnSpan(6)
                    ->schema([
                        TextEntry::make('payment_proof_path')
                            ->label(__('seminar.payment_proof'))
                            ->badge()
                            ->color('primary')
                            ->icon('heroicon-o-eye')
                            ->formatStateUsing(fn () => __('filament.actions.view'))
                            ->action(
                                Action::make('viewSeminarPaymentProof')
                                    ->label(__('seminar.view_payment_proof_seminar'))
                                    ->slideOver()
                                    ->modalContent(function (SeminarRegistration $record) {
                                        $path = $record->payment_proof_path;
                                        $url = asset('storage/'.$path);
                                        $extension = pathinfo($path, PATHINFO_EXTENSION);

                                        return view('components.payment-proof-modal', compact('url', 'extension'));
                                    })
                                    ->modalCancelAction(false),
                            )
                            ->visible(fn (SeminarRegistration $record): bool => $record->payment_proof_path !== null),

                        TextEntry::make('addonRegistrations')
                            ->label(__('seminar.addon_payment_proofs'))
                            ->badge()
                            ->color('primary')
                            ->icon('heroicon-o-photo')
                            ->formatStateUsing(fn (SeminarRegistration $record): string => (string) $record->addonRegistrations->whereNotNull('payment_proof_path')->count())
                            ->action(
                                Action::make('viewAddonPaymentProofs')
                                    ->label(__('filament.actions.view'))
                                    ->slideOver()
                                    ->modalContent(function (SeminarRegistration $record) {
                                        return view('filament.infolists.addon-payment-proofs', [
                                            'record' => $record,
                                        ]);
                                    })
                                    ->modalCancelAction(false),
                            )
                            ->visible(fn (SeminarRegistration $record): bool => $record->addonRegistrations->whereNotNull('payment_proof_path')->isNotEmpty()),

                        TextEntry::make('verified_at')
                            ->label(__('seminar.verified_at'))
                            ->dateTime('d M Y H:i')
                            ->placeholder('—'),

                        TextEntry::make('verifiedBy.name')
                            ->label(__('seminar.verified_by'))
                            ->placeholder('—'),
                    ])->columns(3),
            ]);
    }
}
