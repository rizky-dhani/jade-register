<?php

namespace App\Filament\Resources\DigitalWorkshopRegistrations\Schemas;

use App\Models\Country;
use App\Models\DigitalWorkshop;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class DigitalWorkshopRegistrationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('digital_workshop_id')
                    ->label(__('filament.digital_workshop_registrations.workshop'))
                    ->options(DigitalWorkshop::orderBy('sort_order')->pluck('name', 'id'))
                    ->searchable()
                    ->preload()
                    ->required()
                    ->columnSpanFull(),

                Select::make('seminar_registration_id')
                    ->label(__('filament.digital_workshop_registrations.seminar_registration'))
                    ->relationship('seminarRegistration', 'registration_code')
                    ->searchable()
                    ->preload()
                    ->nullable()
                    ->helperText(__('filament.digital_workshop_registrations.seminar_registration_helper'))
                    ->columnSpanFull(),

                Select::make('country_id')
                    ->label(__('seminar.country'))
                    ->options(Country::orderBy('name')->pluck('name', 'id'))
                    ->searchable()
                    ->preload()
                    ->live()
                    ->nullable()
                    ->default(fn (): ?int => Country::where('is_indonesia', true)->value('id'))
                    ->columnSpanFull(),

                Section::make(__('seminar.local_participant'))
                    ->schema([
                        TextInput::make('name_license')
                            ->label(__('filament.seminar_registration.form.name_plataran'))
                            ->nullable()
                            ->maxLength(255),
                        TextInput::make('email')
                            ->label(__('seminar.email'))
                            ->nullable()
                            ->email()
                            ->maxLength(255),
                        TextInput::make('phone')
                            ->label(__('seminar.whatsapp_number'))
                            ->nullable()
                            ->maxLength(20),
                        TextInput::make('nik')
                            ->label(__('filament.seminar_registration.form.nik'))
                            ->nullable()
                            ->maxLength(16)
                            ->helperText(__('seminar.nik_helper')),
                        TextInput::make('pdgi_branch')
                            ->label(__('filament.seminar_registration.form.pdgi_branch'))
                            ->nullable()
                            ->maxLength(255),
                        Select::make('kompetensi')
                            ->label(__('filament.seminar_registration.form.competency'))
                            ->nullable()
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
                            ->placeholder(__('seminar.select_competency')),
                    ])
                    ->columns(3)
                    ->columnSpanFull()
                    ->visible(fn (Get $get): bool => self::isIndonesia($get('country_id'))),

                Section::make(__('seminar.international_participant'))
                    ->schema([
                        TextInput::make('name')
                            ->label(__('seminar.name'))
                            ->nullable()
                            ->maxLength(255),
                        TextInput::make('email')
                            ->label(__('seminar.email'))
                            ->nullable()
                            ->email()
                            ->maxLength(255),
                        TextInput::make('phone')
                            ->label(__('seminar.whatsapp_number'))
                            ->nullable()
                            ->maxLength(20),
                        Select::make('status')
                            ->label(__('seminar.status'))
                            ->nullable()
                            ->options([
                                'Dentist' => __('seminar.dentist'),
                                'Student' => __('seminar.student'),
                            ])
                            ->placeholder(__('seminar.select_status')),
                    ])
                    ->columns(2)
                    ->columnSpanFull()
                    ->visible(fn (Get $get): bool => ! self::isIndonesia($get('country_id'))),

                Section::make(__('seminar.payment_information'))
                    ->schema([
                        TextInput::make('amount')
                            ->label(__('filament.digital_workshop_registrations.amount'))
                            ->numeric()
                            ->integer()
                            ->minValue(0)
                            ->required(),
                        Select::make('registration_type')
                            ->label(__('filament.digital_workshop_registrations.registration_type'))
                            ->options([
                                'standalone' => __('filament.digital_workshop_registrations.standalone'),
                                'bundled' => __('filament.digital_workshop_registrations.bundled'),
                            ])
                            ->default('standalone')
                            ->required(),
                        Select::make('payment_method')
                            ->label(__('seminar.payment_method'))
                            ->nullable()
                            ->options([
                                'bank_transfer' => __('seminar.bank_transfer'),
                                'qris' => 'QRIS',
                            ]),
                        Select::make('payment_status')
                            ->label(__('filament.digital_workshop_registrations.payment_status'))
                            ->options([
                                'pending' => 'Pending',
                                'verified' => 'Verified',
                                'rejected' => 'Rejected',
                            ]),
                        TextInput::make('rejection_reason')
                            ->label(__('filament.digital_workshop_registrations.reject_reason'))
                            ->nullable()
                            ->maxLength(255)
                            ->columnSpanFull(),
                        FileUpload::make('payment_proof_path')
                            ->label(__('seminar.payment_proof'))
                            ->disk('public')
                            ->previewable()
                            ->downloadable()
                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'application/pdf'])
                            ->maxSize(5120)
                            ->directory('payment-proofs')
                            ->visibility('public')
                            ->nullable()
                            ->columnSpanFull(),
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

        return (bool) Country::where('id', $countryId)->value('is_indonesia');
    }
}
