<?php

namespace App\Filament\Resources\HandsOnRegistrations\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Schema;

class HandsOnRegistrationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('seminarRegistration.registration_code')
                    ->label(__('filament.hands_on_registrations.registration_code'))
                    ->disabled()
                    ->dehydrated(false),

                Fieldset::make(__('seminar.registrant_information'))
                    ->columns(2)
                    ->schema([
                        TextInput::make('seminarRegistration.name_license')
                            ->label(__('seminar.name_plataran'))
                            ->disabled()
                            ->dehydrated(false),
                        TextInput::make('seminarRegistration.email')
                            ->label(__('seminar.email'))
                            ->disabled()
                            ->dehydrated(false),
                        TextInput::make('seminarRegistration.phone')
                            ->label(__('seminar.whatsapp_number'))
                            ->disabled()
                            ->dehydrated(false),
                        TextInput::make('seminarRegistration.nik')
                            ->label(__('seminar.nik'))
                            ->disabled()
                            ->dehydrated(false),
                        TextInput::make('seminarRegistration.pdgi_branch')
                            ->label(__('seminar.pdgi_branch'))
                            ->disabled()
                            ->dehydrated(false),
                        TextInput::make('seminarRegistration.kompetensi')
                            ->label(__('seminar.competency'))
                            ->disabled()
                            ->dehydrated(false),
                    ]),

                Fieldset::make(__('seminar.payment_information'))
                    ->columns(2)
                    ->schema([
                        TextInput::make('seminarRegistration.payment_method')
                            ->label(__('seminar.payment_method'))
                            ->disabled()
                            ->dehydrated(false),
                        Select::make('payment_status')
                            ->label(__('filament.hands_on_registrations.payment_status'))
                            ->options([
                                'pending' => 'Pending',
                                'verified' => 'Verified',
                                'rejected' => 'Rejected',
                            ]),
                        DateTimePicker::make('verified_at')
                            ->label(__('filament.hands_on_registrations.verified_at')),
                    ]),
            ]);
    }
}
