<?php

namespace App\Filament\Resources\HandsOnRegistrations\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Schemas\Schema;

class HandsOnRegistrationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('seminar_registration_id')
                    ->label(__('filament.hands_on_registrations.seminar_registration'))
                    ->relationship('seminarRegistration', 'registration_code')
                    ->searchable()
                    ->preload()
                    ->required(),

                Select::make('hands_on_id')
                    ->label(__('filament.hands_on_registrations.hands_on_event'))
                    ->relationship('handsOn', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),

                Select::make('registration_type')
                    ->label(__('filament.hands_on_registrations.registration_type'))
                    ->options([
                        'combined' => 'Combined',
                        'standalone' => 'Standalone',
                    ])
                    ->default('combined')
                    ->required(),

                Select::make('payment_status')
                    ->label(__('filament.hands_on_registrations.payment_status'))
                    ->options([
                        'pending' => 'Pending',
                        'verified' => 'Verified',
                        'rejected' => 'Rejected',
                    ])
                    ->default('pending')
                    ->required(),

                FileUpload::make('payment_proof_path')
                    ->label(__('filament.hands_on_registrations.payment_proof'))
                    ->image()
                    ->directory('payment-proofs')
                    ->visibility('public'),

                DateTimePicker::make('verified_at')
                    ->label(__('filament.hands_on_registrations.verified_at')),
            ]);
    }
}
