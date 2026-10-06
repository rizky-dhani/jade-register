<?php

namespace App\Filament\Resources\DigitalWorkshops\Schemas;

use App\Enums\HandsOnStatus;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class DigitalWorkshopForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                Section::make('Basic Information')
                    ->columnSpan(1)
                    ->schema([
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255),

                        TextInput::make('code')
                            ->required()
                            ->maxLength(255)
                            ->label(__('filament.digital_workshop.code'))
                            ->helperText('Unique code for this workshop session (e.g., DW-01)')
                            ->unique(ignoreRecord: true),

                        Textarea::make('description')
                            ->rows(3)
                            ->maxLength(1000),
                    ]),

                Section::make('Pricing')
                    ->columnSpan(1)
                    ->schema([
                        TextInput::make('price')
                            ->numeric()
                            ->integer()
                            ->minValue(0)
                            ->default(1199000)
                            ->required()
                            ->label(__('filament.digital_workshop.price'))
                            ->placeholder('e.g., 1199000')
                            ->helperText('Standalone price'),

                        TextInput::make('bundle_price')
                            ->numeric()
                            ->integer()
                            ->minValue(0)
                            ->default(999000)
                            ->label(__('filament.digital_workshop.bundle_price'))
                            ->placeholder('e.g., 999000')
                            ->helperText('Price when the attendee has a verified seminar registration'),
                    ]),

                Section::make('Schedule & Capacity')
                    ->columnSpan(2)
                    ->columns(2)
                    ->schema([
                        DatePicker::make('event_date')
                            ->required()
                            ->label(__('filament.digital_workshop.event_date'))
                            ->displayFormat('F j, Y'),

                        TextInput::make('location')
                            ->maxLength(255)
                            ->label(__('filament.digital_workshop.location')),

                        TimePicker::make('event_time')
                            ->required()
                            ->seconds(false)
                            ->displayFormat('H:i')
                            ->label(__('filament.digital_workshop.event_time')),

                        TimePicker::make('event_end_time')
                            ->required()
                            ->seconds(false)
                            ->displayFormat('H:i')
                            ->after('event_time')
                            ->label(__('filament.digital_workshop.event_end_time')),

                        TextInput::make('max_seats')
                            ->numeric()
                            ->integer()
                            ->minValue(0)
                            ->label(__('filament.digital_workshop.max_seats'))
                            ->placeholder('e.g., 30')
                            ->helperText('Leave empty for unlimited'),

                        TextInput::make('sort_order')
                            ->numeric()
                            ->integer()
                            ->default(0)
                            ->label(__('filament.digital_workshop.sort_order')),

                        Select::make('status')
                            ->label(__('filament.digital_workshop.status'))
                            ->options(collect(HandsOnStatus::cases())
                                ->mapWithKeys(fn (HandsOnStatus $s) => [$s->value => $s->getLabel()])
                                ->toArray())
                            ->default(HandsOnStatus::DRAFT->value)
                            ->required(),
                    ]),
            ]);
    }
}
