<?php

namespace App\Filament\Resources\PosterEvaluations\Pages;

use App\Filament\Resources\PosterEvaluations\PosterEvaluationResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPosterEvaluation extends EditRecord
{
    protected static string $resource = PosterEvaluationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
