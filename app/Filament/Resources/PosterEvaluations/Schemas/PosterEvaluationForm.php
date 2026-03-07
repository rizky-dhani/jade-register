<?php

namespace App\Filament\Resources\PosterEvaluations\Schemas;

use App\Models\PosterEvaluation;
use App\Models\PosterSubmission;
use App\Models\User;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
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
                    ->columns(2)
                    ->schema([
                        Select::make('poster_submission_id')
                            ->label('Poster Submission')
                            ->options(PosterSubmission::pluck('title', 'id'))
                            ->required(),
                        Select::make('judge_id')
                            ->label('Judge')
                            ->options(User::pluck('name', 'id'))
                            ->required(),
                    ]),
                Section::make('Scores')
                    ->description('Score each criterion from 0 to the maximum value.')
                    ->columns(2)
                    ->schema([
                        TextInput::make('content_score')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(PosterEvaluation::MAX_CONTENT_SCORE)
                            ->label('Content Score')
                            ->helperText('Max: '.PosterEvaluation::MAX_CONTENT_SCORE.' points')
                            ->required(),
                        TextInput::make('creativity_score')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(PosterEvaluation::MAX_CREATIVITY_SCORE)
                            ->label('Creativity Score')
                            ->helperText('Max: '.PosterEvaluation::MAX_CREATIVITY_SCORE.' points')
                            ->required(),
                        TextInput::make('visual_score')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(PosterEvaluation::MAX_VISUAL_SCORE)
                            ->label('Visual Score')
                            ->helperText('Max: '.PosterEvaluation::MAX_VISUAL_SCORE.' points')
                            ->required(),
                        TextInput::make('presentation_score')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(PosterEvaluation::MAX_PRESENTATION_SCORE)
                            ->label('Presentation Score')
                            ->helperText('Max: '.PosterEvaluation::MAX_PRESENTATION_SCORE.' points')
                            ->required(),
                    ]),
                Section::make('Feedback')
                    ->schema([
                        Textarea::make('comments')
                            ->label('Comments')
                            ->rows(4),
                    ]),
            ]);
    }
}
