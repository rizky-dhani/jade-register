<?php

namespace App\Filament\Resources\HandsOnEvents\Pages;

use App\Filament\Resources\HandsOnEvents\HandsOnEventResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditHandsOnEvent extends EditRecord
{
    protected static string $resource = HandsOnEventResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
