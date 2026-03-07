<?php

namespace App\Filament\Resources\PosterSubmissions\Pages;

use App\Filament\Resources\PosterSubmissions\PosterSubmissionResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPosterSubmission extends EditRecord
{
    protected static string $resource = PosterSubmissionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
