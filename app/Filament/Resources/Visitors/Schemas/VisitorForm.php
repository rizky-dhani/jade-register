<?php

namespace App\Filament\Resources\Visitors\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\TextEntry;
use Filament\Schemas\Schema;

class VisitorForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                TextInput::make('email')
                    ->required()
                    ->email()
                    ->maxLength(255),
                TextInput::make('phone')
                    ->required()
                    ->maxLength(20),
                TextInput::make('affiliation')
                    ->maxLength(255),
                Section::make('Attendance Status')
                    ->description('Visitor check-in information')
                    ->visibleOn('edit')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('isScanned')
                            ->label('Checked In')
                            ->state(fn ($record) => $record->isScanned() ? 'Yes' : 'No')
                            ->badge()
                            ->color(fn ($record) => $record->isScanned() ? 'success' : 'danger'),
                        TextEntry::make('scanned_at')
                            ->label('Checked In At')
                            ->placeholder('Not checked in yet'),
                    ]),
            ]);
    }
}
