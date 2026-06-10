<?php

namespace App\Filament\Resources\PosterEvaluations\Schemas;

use App\Models\PosterEvaluation;
use App\Models\PosterSubmission;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PosterEvaluationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Evaluation Details')
                    ->schema([
                        Select::make('poster_submission_id')
                            ->label(__('filament.poster_evaluations.poster_submission'))
                            ->options(PosterSubmission::pluck('title', 'id'))
                            ->required()
                            ->searchable(),
                        Hidden::make('judge_id')
                            ->default(auth()->id()),
                    ]),
                Section::make('Scores')
                    ->description('Score each criterion from 0 to the maximum value.')
                    ->schema([
                        TextInput::make('content_score')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(PosterEvaluation::MAX_CONTENT_SCORE)
                            ->label(__('filament.poster_evaluations.content_score'))
                            ->helperText('Max: '.PosterEvaluation::MAX_CONTENT_SCORE.' points')
                            ->required(),
                        TextInput::make('creativity_score')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(PosterEvaluation::MAX_CREATIVITY_SCORE)
                            ->label(__('filament.poster_evaluations.creativity_score'))
                            ->helperText('Max: '.PosterEvaluation::MAX_CREATIVITY_SCORE.' points')
                            ->required(),
                        TextInput::make('visual_score')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(PosterEvaluation::MAX_VISUAL_SCORE)
                            ->label(__('filament.poster_evaluations.visual_score'))
                            ->helperText('Max: '.PosterEvaluation::MAX_VISUAL_SCORE.' points')
                            ->required(),
                        TextInput::make('presentation_score')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(PosterEvaluation::MAX_PRESENTATION_SCORE)
                            ->label(__('filament.poster_evaluations.presentation_score'))
                            ->helperText('Max: '.PosterEvaluation::MAX_PRESENTATION_SCORE.' points')
                            ->required(),
                    ]),
            ]);

    }
}
