<?php

namespace App\Filament\Resources\Attendances\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class AttendanceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('seminar_registration_id')
                    ->relationship('seminarRegistration', 'name')
                    ->required(),
                Select::make('hands_on_registration_id')
                    ->relationship('handsOnRegistration', 'id'),
                TextInput::make('activity_type')
                    ->required(),
                DateTimePicker::make('checked_in_at')
                    ->required(),
                TextInput::make('checked_in_by')
                    ->required()
                    ->numeric(),
                Textarea::make('notes')
                    ->columnSpanFull(),
            ]);
    }
}
