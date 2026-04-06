<?php

namespace App\Filament\Resources\PosterSubmissions\Schemas;

use App\Models\PosterCategory;
use App\Models\PosterSubmission;
use App\Models\PosterTopic;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PosterSubmissionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Submission Details')
                    ->columns(2)
                    ->schema([
                        TextInput::make('title')
                            ->required()
                            ->maxLength(255),
                        Select::make('status')
                            ->options([
                                PosterSubmission::STATUS_DRAFT => 'Draft',
                                PosterSubmission::STATUS_SUBMITTED => 'Submitted',
                                PosterSubmission::STATUS_UNDER_REVIEW => 'Under Review',
                                PosterSubmission::STATUS_ACCEPTED => 'Accepted',
                                PosterSubmission::STATUS_FINALIST => 'Finalist',
                                PosterSubmission::STATUS_WINNER => 'Winner',
                                PosterSubmission::STATUS_REJECTED => 'Rejected',
                            ])
                            ->required(),
                        Select::make('poster_category_id')
                            ->label(__('filament.poster_submissions.category'))
                            ->options(PosterCategory::pluck('name', 'id'))
                            ->required(),
                        Select::make('poster_topic_id')
                            ->label(__('filament.poster_submissions.topic'))
                            ->options(PosterTopic::pluck('name', 'id'))
                            ->required(),
                    ]),
                Section::make('Authors')
                    ->columns(2)
                    ->schema([
                        TextInput::make('author_names')
                            ->label(__('filament.poster_submissions.author_names'))
                            ->required()
                            ->maxLength(500),
                        TextInput::make('author_emails')
                            ->label(__('filament.poster_submissions.author_emails'))
                            ->required()
                            ->maxLength(500),
                        TextInput::make('affiliation')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('presenter_name')
                            ->label(__('filament.poster_submissions.presenter_name'))
                            ->required()
                            ->maxLength(255),
                    ]),
                Section::make('Abstract')
                    ->schema([
                        Textarea::make('abstract_text')
                            ->required()
                            ->maxLength(1500)
                            ->rows(6),
                    ]),
                Section::make('Admin Notes')
                    ->schema([
                        Textarea::make('rejection_reason')
                            ->label(__('filament.poster_submissions.rejection_reason'))
                            ->rows(3),
                    ]),
            ]);
    }
}
