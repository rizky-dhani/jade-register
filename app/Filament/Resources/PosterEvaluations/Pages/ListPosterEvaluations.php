<?php

namespace App\Filament\Resources\PosterEvaluations\Pages;

use App\Filament\Resources\PosterEvaluations\PosterEvaluationResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPosterEvaluations extends ListRecords
{
    protected static string $resource = PosterEvaluationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->visible(fn () => auth()->user()?->hasRole('Super Admin') ?? false),
        ];
    }
}
