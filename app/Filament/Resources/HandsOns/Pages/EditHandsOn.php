<?php

namespace App\Filament\Resources\HandsOns\Pages;

use App\Filament\Resources\HandsOns\HandsOnResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditHandsOn extends EditRecord
{
    protected static string $resource = HandsOnResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
