<?php

namespace App\Filament\Resources\HandsOnRegistrations\Schemas;

use App\Enums\HandsOnStatus;
use App\Models\Country;
use App\Models\HandsOn;
use Carbon\Carbon;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Radio;
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

                // Hands-On Session Selection (per date, max 1 per day)
                ...self::buildHandsOnRadioGroups(),

                // Hidden hands_on_id for model binding
                TextInput::make('hands_on_id')
                    ->hidden()
                    ->dehydrated()
                    ->default(null),

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

    private static function buildHandsOnRadioGroups(): array
    {
        $dayMap = [
            '2026-11-13' => 1,
            '2026-11-14' => 2,
            '2026-11-15' => 3,
        ];

        $eventsByDate = HandsOn::where('is_active', true)
            ->where('status', HandsOnStatus::PUBLISHED)
            ->whereDate('event_date', '>=', '2026-11-13')
            ->whereDate('event_date', '<=', '2026-11-15')
            ->orderBy('event_date')
            ->orderBy('ho_code')
            ->get()
            ->groupBy(fn (HandsOn $handsOn): string => $handsOn->event_date->format('Y-m-d'));

        $components = [];

        $sections = [];
        foreach ($eventsByDate as $date => $events) {
            $dayNumber = $dayMap[$date] ?? Carbon::parse($date)->format('j');

            $options = $events->mapWithKeys(fn (HandsOn $ho): array => [
                $ho->id => collect([
                    $ho->ho_code,
                    $ho->name,
                    $ho->doctor_name,
                ])->filter()->implode(' - '),
            ]);

            $descriptions = $events->mapWithKeys(fn (HandsOn $ho): array => [
                $ho->id => collect([
                    $ho->formatted_original_price,
                    $ho->isFull()
                        ? __('seminar.sold_out')
                        : ($ho->max_seats
                            ? trans_choice('seminar.limited_seats', $ho->remaining_stock, ['count' => $ho->remaining_stock])
                            : __('seminar.unlimited')),
                    $ho->description,
                ])->filter()->implode(' | '),
            ]);

            $disabledOptions = $events->filter(fn (HandsOn $ho): bool => $ho->isFull() || $ho->current_price === null || $ho->current_price <= 0)
                ->pluck('id')
                ->map(fn (int $id): string => (string) $id)
                ->values()
                ->toArray();

            $sections[] = Section::make(__('seminar.day_number', ['day' => 'ke-'.$dayNumber]))
                ->schema([
                    Radio::make("selectedHandsOn.{$date}")
                        ->hiddenLabel()
                        ->options($options)
                        ->descriptions($descriptions)
                        ->disableOptionWhen(fn (string $value): bool => in_array($value, $disabledOptions))
                        ->columnSpanFull(),
                ])
                ->columnSpanFull()
                ->columns(1);
        }

        // Wrap in a parent section if we have events, or show a message
        if (! empty($sections)) {
            $components[] = Section::make(__('seminar.hands_on_sessions'))
                ->schema($sections)
                ->columnSpanFull();
        }

        return $components;
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
