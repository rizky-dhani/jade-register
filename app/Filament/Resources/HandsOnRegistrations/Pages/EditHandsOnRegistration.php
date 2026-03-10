<?php

namespace App\Filament\Resources\HandsOnRegistrations\Pages;

use App\Filament\Resources\HandsOnRegistrations\HandsOnRegistrationResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditHandsOnRegistration extends EditRecord
{
    protected static string $resource = HandsOnRegistrationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
