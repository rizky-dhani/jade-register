<?php

namespace App\Filament\Resources\HandsOnRegistrations\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class HandsOnRegistrationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('registration_code')
                    ->label(__('filament.hands_on_registrations.registration_code'))
                    ->disabled()
                    ->dehydrated(false),

                Fieldset::make(__('seminar.registrant_information'))
                    ->columns(2)
                    ->schema([
                        TextInput::make('name_license')
                            ->label(__('seminar.name_plataran')),
                        TextInput::make('email')
                            ->label(__('seminar.email')),
                        TextInput::make('phone')
                            ->label(__('seminar.whatsapp_number')),
                        TextInput::make('nik')
                            ->label(__('seminar.nik')),
                        TextInput::make('pdgi_branch')
                            ->label(__('seminar.pdgi_branch')),
                        TextInput::make('kompetensi')
                            ->label(__('seminar.competency')),
                    ]),

                Fieldset::make(__('seminar.payment_information'))
                    ->columns(2)
                    ->schema([
                        Select::make('payment_method')
                            ->label(__('seminar.payment_method'))
                            ->options([
                                'bank_transfer' => __('seminar.bank_transfer'),
                                'qris' => 'QRIS',
                            ]),
                        Select::make('payment_status')
                            ->label(__('filament.hands_on_registrations.payment_status'))
                            ->options([
                                'pending' => 'Pending',
                                'verified' => 'Verified',
                                'rejected' => 'Rejected',
                            ]),
                        TextInput::make('hands_on_total_amount')
                            ->label(__('filament.hands_on_registrations.total_amount'))
                            ->numeric()
                            ->prefix('Rp'),
                        DateTimePicker::make('verified_at')
                            ->label(__('filament.hands_on_registrations.verified_at')),
                    ]),
            ]);
    }
}
