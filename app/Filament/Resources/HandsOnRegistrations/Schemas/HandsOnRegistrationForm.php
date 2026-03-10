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
                    ->label('Seminar Registration')
                    ->relationship('seminarRegistration', 'registration_code')
                    ->searchable()
                    ->preload()
                    ->required(),

                Select::make('hands_on_id')
                    ->label('Hands On Event')
                    ->relationship('handsOn', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),

                Select::make('registration_type')
                    ->label('Registration Type')
                    ->options([
                        'combined' => 'Combined',
                        'standalone' => 'Standalone',
                    ])
                    ->default('combined')
                    ->required(),

                Select::make('payment_status')
                    ->label('Payment Status')
                    ->options([
                        'pending' => 'Pending',
                        'verified' => 'Verified',
                        'rejected' => 'Rejected',
                    ])
                    ->default('pending')
                    ->required(),

                FileUpload::make('payment_proof_path')
                    ->label('Payment Proof')
                    ->image()
                    ->directory('payment-proofs')
                    ->visibility('public'),

                DateTimePicker::make('verified_at')
                    ->label('Verified At'),
            ]);
    }
}
