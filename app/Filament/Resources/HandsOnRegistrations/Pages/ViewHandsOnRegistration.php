<?php

namespace App\Filament\Resources\HandsOnRegistrations\Pages;

use App\Filament\Resources\HandsOnRegistrations\HandsOnRegistrationResource;
use App\Filament\Resources\HandsOnRegistrations\Schemas\HandsOnRegistrationInfolist;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Schema;

class ViewHandsOnRegistration extends ViewRecord
{
    protected static string $resource = HandsOnRegistrationResource::class;

    public function infolist(Schema $schema): Schema
    {
        return HandsOnRegistrationInfolist::configure($schema);
    }

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make()
                ->label(__('filament.actions.edit'))
                ->icon('heroicon-m-pencil-square'),
        ];
    }
}
