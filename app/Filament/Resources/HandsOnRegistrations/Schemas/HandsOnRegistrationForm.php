<?php

namespace App\Filament\Resources\HandsOnRegistrations\Schemas;

use App\Models\Country;
use App\Models\HandsOn;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class HandsOnRegistrationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('seminarRegistration.country_id')
                    ->label(__('seminar.country'))
                    ->options(Country::orderBy('name')->pluck('name', 'id'))
                    ->searchable()
                    ->preload()
                    ->live()
                    ->required()
                    ->default(fn (): ?int => Country::where('is_indonesia', true)->value('id'))
                    ->columnSpanFull(),

                // Local (Indonesia) Participant Information
                Section::make(__('seminar.local_participant'))
                    ->schema([
                        TextInput::make('seminarRegistration.name')
                            ->label(__('filament.seminar_registration.form.name_str'))
                            ->nullable()
                            ->maxLength(255)
                            ->autocomplete('name')
                            ->hidden(),
                        TextInput::make('seminarRegistration.name_license')
                            ->label(__('filament.seminar_registration.form.name_plataran'))
                            ->required()
                            ->maxLength(255)
                            ->autocomplete('name'),
                        TextInput::make('seminarRegistration.email')
                            ->label(__('filament.seminar_registration.form.email_plataran'))
                            ->required()
                            ->email()
                            ->maxLength(255)
                            ->autocomplete('email'),
                        TextInput::make('seminarRegistration.phone')
                            ->label(__('filament.seminar_registration.form.whatsapp_number'))
                            ->required()
                            ->maxLength(20)
                            ->autocomplete('tel'),
                        TextInput::make('seminarRegistration.nik')
                            ->label(__('filament.seminar_registration.form.nik'))
                            ->required()
                            ->maxLength(16)
                            ->autocomplete('off')
                            ->helperText(__('seminar.nik_helper')),
                        TextInput::make('seminarRegistration.pdgi_branch')
                            ->label(__('filament.seminar_registration.form.pdgi_branch'))
                            ->required()
                            ->maxLength(255)
                            ->autocomplete('organization'),
                        Select::make('seminarRegistration.kompetensi')
                            ->label(__('filament.seminar_registration.form.competency'))
                            ->required()
                            ->options([
                                'Dokter Gigi Umum' => __('seminar.competency_gp'),
                                'Sp.KG' => __('seminar.competency_sp_kg'),
                                'Sp.KGA' => __('seminar.competency_sp_kga'),
                                'Sp.Pros' => __('seminar.competency_sp_pros'),
                                'Sp.B.M.M' => __('seminar.competency_sp_bmm'),
                                'Sp.Perio' => __('seminar.competency_sp_perio'),
                                'Sp.Ort' => __('seminar.competency_sp_ort'),
                                'Sp.RKG' => __('seminar.competency_sp_rkg'),
                                'Sp.PM' => __('seminar.competency_sp_pm'),
                                'Sp.OF' => __('seminar.competency_sp_of'),
                                'Sp.PMM' => __('seminar.competency_sp_pmm'),
                                'Mahasiswa Kedokteran Gigi' => __('seminar.competency_dental_student'),
                                'drg Internship' => __('seminar.competency_dentist_internship'),
                            ])
                            ->disableOptionWhen(fn (string $value): bool => in_array($value, ['Sp.KGA', 'Sp.B.M.M', 'Sp.Ort']))
                            ->placeholder(__('seminar.select_competency'))
                            ->columnSpanFull(),
                    ])
                    ->columns(3)
                    ->columnSpanFull()
                    ->visible(fn (Get $get): bool => self::isIndonesia($get('seminarRegistration.country_id'))),

                // International Participant Information
                Section::make(__('seminar.international_participant'))
                    ->schema([
                        TextInput::make('seminarRegistration.name')
                            ->label(__('seminar.name'))
                            ->required()
                            ->maxLength(255)
                            ->autocomplete('name'),
                        TextInput::make('seminarRegistration.email')
                            ->label(__('seminar.email'))
                            ->required()
                            ->email()
                            ->maxLength(255)
                            ->autocomplete('email'),
                        TextInput::make('seminarRegistration.phone')
                            ->label(__('seminar.whatsapp_number'))
                            ->required()
                            ->maxLength(20)
                            ->autocomplete('tel'),
                        Select::make('seminarRegistration.status')
                            ->label(__('seminar.status'))
                            ->required()
                            ->options([
                                'Dentist' => __('seminar.dentist'),
                                'Student' => __('seminar.student'),
                            ])
                            ->placeholder(__('seminar.select_status')),
                    ])
                    ->columns(2)
                    ->columnSpanFull()
                    ->visible(fn (Get $get): bool => ! self::isIndonesia($get('seminarRegistration.country_id'))),

                // Hands-On Session Selection
                Section::make(__('seminar.hands_on_sessions'))
                    ->schema([
                        Select::make('hands_on_id')
                            ->label(__('seminar.select_hands_on'))
                            ->required()
                            ->options(function (): array {
                                return HandsOn::where('is_active', true)
                                    ->orderBy('event_date')
                                    ->orderBy('ho_code')
                                    ->get()
                                    ->mapWithKeys(function (HandsOn $handsOn) {
                                        $label = "{$handsOn->ho_code} - {$handsOn->name} - {$handsOn->event_date?->format('d M Y')} - {$handsOn->formatted_original_price}";
                                        if ($handsOn->isFull()) {
                                            $label .= ' ('.__('seminar.sold_out').')';
                                        }

                                        return [$handsOn->id => $label];
                                    })
                                    ->toArray();
                            })
                            ->searchable()
                            ->preload()
                            ->columnSpanFull(),
                    ])
                    ->columnSpanFull(),

                // Registration Type (hidden, default to 'combined')
                TextInput::make('registration_type')
                    ->default('combined')
                    ->hidden()
                    ->dehydrated(),

                // Payment Information
                Section::make(__('seminar.payment_information'))
                    ->schema([
                        Select::make('seminarRegistration.payment_method')
                            ->label(__('seminar.payment_method'))
                            ->required()
                            ->options([
                                'bank_transfer' => __('seminar.bank_transfer'),
                                'qris' => 'QRIS',
                            ]),
                        FileUpload::make('seminarRegistration.payment_proof_path')
                            ->label(__('seminar.payment_proof'))
                            ->image()
                            ->previewable()
                            ->downloadable()
                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'application/pdf'])
                            ->maxSize(5120)
                            ->directory('payment-proofs')
                            ->visibility('public')
                            ->helperText(__('filament.seminar_registration.form.payment_proof_helper'))
                            ->preserveFilenames()
                            ->dehydrateStateUsing(fn ($state, $record) => $state ?? $record?->seminarRegistration?->payment_proof_path ?? null)
                            ->nullable(),
                        Select::make('payment_status')
                            ->label(__('filament.hands_on_registrations.payment_status'))
                            ->options([
                                'pending' => 'Pending',
                                'verified' => 'Verified',
                                'rejected' => 'Rejected',
                            ]),
                        DateTimePicker::make('verified_at')
                            ->label(__('filament.hands_on_registrations.verified_at')),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),
            ]);
    }

    private static function isIndonesia(?int $countryId): bool
    {
        if (! $countryId) {
            return true;
        }

        $country = Country::find($countryId);

        return $country?->is_indonesia ?? true;
    }
}
