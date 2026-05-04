<?php

namespace App\Filament\Resources\HandsOns\Schemas;

use App\Enums\HandsOnStatus;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class HandsOnForm
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

                        TextInput::make('ho_code')
                            ->required()
                            ->maxLength(255)
                            ->label('HO Code')
                            ->helperText('Unique code for this hands-on event (e.g., HO-DIW-001)')
                            ->unique(ignoreRecord: true),

                        TextInput::make('doctor_name')
                            ->maxLength(255)
                            ->label('Doctor Name')
                            ->helperText('Name of the doctor assigned to this hands-on session'),

                        Textarea::make('description')
                            ->rows(3)
                            ->maxLength(1000),
                    ]),

                Section::make('Pricing')
                    ->columnSpan(1)
                    ->schema([
                        TextInput::make('original_price')
                            ->numeric()
                            ->integer()
                            ->minValue(0)
                            ->label(__('filament.hands_on.original_price'))
                            ->placeholder('e.g., 1500000')
                            ->helperText('Regular price before any discounts'),

                        TextInput::make('discounted_price')
                            ->numeric()
                            ->integer()
                            ->minValue(0)
                            ->label(__('filament.hands_on.discounted_price'))
                            ->placeholder('e.g., 1200000')
                            ->helperText('Early bird promotional price (leave empty for no discount)'),

                        TextInput::make('early_bird_deadline')
                            ->label(__('filament.hands_on.early_bird_deadline'))
                            ->type('datetime-local')
                            ->helperText('Deadline for early bird pricing (leave empty for no deadline)'),

                        TextInput::make('currency')
                            ->required()
                            ->default('IDR')
                            ->maxLength(3),
                    ]),

                Section::make('Media')
                    ->columnSpan(1)
                    ->schema([
                        FileUpload::make('flyer_path')
                            ->label('Flyer Image')
                            ->image()
                            ->disk('public')
                            ->directory('hands-on/flyers')
                            ->visibility('public')
                            ->getUploadedFileNameForStorageUsing(
                                fn (TemporaryUploadedFile $file, Get $get): string => ($get('ho_code') ?? 'temp-'.uniqid()).'.'.$file->getClientOriginalExtension()
                            ),

                        FileUpload::make('skp_path')
                            ->label('SKP Image')
                            ->image()
                            ->disk('public')
                            ->directory('hands-on/skp')
                            ->visibility('public')
                            ->getUploadedFileNameForStorageUsing(
                                fn (TemporaryUploadedFile $file, Get $get): string => ($get('ho_code') ?? 'temp-'.uniqid()).'.'.$file->getClientOriginalExtension()
                            ),
                    ]),

                Section::make('Schedule & Capacity')
                    ->columnSpan(1)
                    ->columns(2)
                    ->schema([
                        DatePicker::make('event_date')
                            ->required()
                            ->label(__('filament.hands_on.event_date'))
                            ->displayFormat('F j, Y')
                            ->minDate('2026-11-13')
                            ->maxDate('2026-11-15'),

                        TextInput::make('max_seats')
                            ->numeric()
                            ->integer()
                            ->minValue(1)
                            ->label(__('filament.hands_on.max_seats'))
                            ->placeholder('e.g., 30')
                            ->helperText('Maximum number of registrations allowed (leave empty for unlimited)'),

                        Select::make('status')
                            ->label(__('filament.hands_on.status'))
                            ->options(collect(HandsOnStatus::cases())
                                ->mapWithKeys(fn (HandsOnStatus $s) => [$s->value => $s->getLabel()])
                                ->toArray())
                            ->default(HandsOnStatus::DRAFT->value)
                            ->required(),

                        Toggle::make('is_active')
                            ->label(__('filament.hands_on.active'))
                            ->default(true),
                    ]),
            ]);
    }
}
