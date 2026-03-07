<?php

namespace App\Filament\Resources\Settings\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class SettingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Bank Payment Details')
                    ->schema([
                        TextInput::make('bank_name')
                            ->label('Bank Name')
                            ->maxLength(100),
                        TextInput::make('bank_account_name')
                            ->label('Account Name')
                            ->maxLength(100),
                        TextInput::make('bank_account_number')
                            ->label('Account Number')
                            ->maxLength(50),
                        Textarea::make('payment_instructions')
                            ->label('Payment Instructions')
                            ->rows(3),
                    ]),
                Section::make('Venue Settings')
                    ->schema([
                        TextInput::make('venue_name')
                            ->label('Venue Name')
                            ->maxLength(255),
                        Textarea::make('venue_address')
                            ->label('Venue Address')
                            ->rows(2),
                        TextInput::make('venue_latitude')
                            ->label('Venue Latitude')
                            ->numeric(),
                        TextInput::make('venue_longitude')
                            ->label('Venue Longitude')
                            ->numeric(),
                        TextInput::make('detection_radius')
                            ->label('Detection Radius (meters)')
                            ->numeric()
                            ->default(500),
                    ]),
            ]);
    }
}
