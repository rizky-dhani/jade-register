<?php

namespace App\Filament\Resources\SeminarRegistrations\Schemas;

use App\Models\Country;
use App\Models\HandsOn;
use App\Models\Seminar;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class SeminarRegistrationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('country_id')
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
                        TextInput::make('name')
                            ->label(__('filament.seminar_registration.form.name_str'))
                            ->nullable()
                            ->maxLength(255)
                            ->autocomplete('name')
                            ->hidden(),
                        TextInput::make('name_license')
                            ->label(__('filament.seminar_registration.form.name_plataran'))
                            ->required()
                            ->maxLength(255)
                            ->autocomplete('name'),
                        TextInput::make('email')
                            ->label(__('filament.seminar_registration.form.email_plataran'))
                            ->required()
                            ->email()
                            ->maxLength(255)
                            ->autocomplete('email'),
                        TextInput::make('phone')
                            ->label(__('filament.seminar_registration.form.whatsapp_number'))
                            ->required()
                            ->maxLength(20)
                            ->autocomplete('tel'),
                        TextInput::make('nik')
                            ->label(__('filament.seminar_registration.form.nik'))
                            ->required()
                            ->maxLength(16)
                            ->numeric()
                            ->autocomplete('off')
                            ->helperText(__('seminar.nik_helper')),
                        TextInput::make('pdgi_branch')
                            ->label(__('filament.seminar_registration.form.pdgi_branch'))
                            ->required()
                            ->maxLength(255)
                            ->autocomplete('organization'),
                        Select::make('kompetensi')
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
                    ->visible(fn (Get $get): bool => self::isIndonesia($get('country_id'))),

                // International Participant Information
                Section::make(__('seminar.international_participant'))
                    ->schema([
                        TextInput::make('name')
                            ->label(__('seminar.name'))
                            ->required()
                            ->maxLength(255)
                            ->autocomplete('name'),
                        TextInput::make('email')
                            ->label(__('seminar.email'))
                            ->required()
                            ->email()
                            ->maxLength(255)
                            ->autocomplete('email'),
                        TextInput::make('phone')
                            ->label(__('seminar.whatsapp_number'))
                            ->required()
                            ->maxLength(20)
                            ->autocomplete('tel'),
                        Select::make('status')
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
                    ->visible(fn (Get $get): bool => ! self::isIndonesia($get('country_id'))),

                Section::make(__('seminar.seminar_package'))
                    ->schema([
                        Radio::make('selected_seminar')
                            ->label(__('seminar.select_seminar'))
                            ->required()
                            ->options(function (): array {
                                return Seminar::active()
                                    ->get()
                                    ->mapWithKeys(function (Seminar $seminar) {
                                        return [$seminar->name => "{$seminar->name} - {$seminar->label} - {$seminar->formatted_current_price}"];
                                    })
                                    ->toArray();
                            }),
                        Checkbox::make('wants_hands_on')
                            ->label(__('seminar.want_to_join_hands_on'))
                            ->live(),
                        Section::make(__('seminar.hands_on_sessions'))
                            ->schema([
                                CheckboxList::make('hands_on_sessions')
                                    ->label(__('seminar.select_hands_on'))
                                    ->options(function (): array {
                                        return HandsOn::query()
                                            ->where('is_active', true)
                                            ->get()
                                            ->mapWithKeys(function (HandsOn $handsOn) {
                                                $label = "{$handsOn->name} - {$handsOn->event_date?->format('d M Y')} - {$handsOn->formatted_original_price}";
                                                if ($handsOn->isFull()) {
                                                    $label .= ' ('.__('seminar.sold_out').')';
                                                }

                                                return [$handsOn->id => $label];
                                            })
                                            ->toArray();
                                    })
                                    ->descriptions(function (): array {
                                        return HandsOn::query()
                                            ->where('is_active', true)
                                            ->get()
                                            ->mapWithKeys(function (HandsOn $handsOn) {
                                                $remaining = $handsOn->remaining_stock;
                                                $description = $handsOn->description;
                                                if ($remaining !== null && $remaining < PHP_INT_MAX) {
                                                    $description .= ' - '.trans_choice('seminar.limited_seats', $remaining, ['count' => $remaining]);
                                                }

                                                return [$handsOn->id => $description];
                                            })
                                            ->toArray();
                                    })
                                    ->live()
                                    ->nullable(),
                            ])
                            ->visible(fn (Get $get): bool => $get('wants_hands_on') === true)
                            ->columnSpanFull(),
                    ])
                    ->columns(1),
                FileUpload::make('payment_proof_path')
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
                    ->dehydrateStateUsing(fn ($state, $record) => $state ?? $record->payment_proof_path ?? null)
                    ->nullable(),
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
