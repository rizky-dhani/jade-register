<?php

namespace App\Filament\Resources\PosterSubmissions\Pages;

use App\Filament\Resources\PosterSubmissions\PosterSubmissionResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPosterSubmissions extends ListRecords
{
    protected static string $resource = PosterSubmissionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
