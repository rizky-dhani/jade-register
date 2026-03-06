<?php

namespace App\Filament\Resources\SeminarRegistrations\Pages;

use App\Filament\Resources\SeminarRegistrations\SeminarRegistrationResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditSeminarRegistration extends EditRecord
{
    protected static string $resource = SeminarRegistrationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
