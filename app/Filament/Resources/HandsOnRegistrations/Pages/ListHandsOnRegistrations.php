<?php

namespace App\Filament\Resources\HandsOnRegistrations\Pages;

use App\Exports\HandsOnRegistrationExport;
use App\Filament\Resources\HandsOnRegistrations\HandsOnRegistrationResource;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Maatwebsite\Excel\Facades\Excel;

class ListHandsOnRegistrations extends ListRecords
{
    protected static string $resource = HandsOnRegistrationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('export')
                ->label('Export Excel')
                ->icon('heroicon-o-arrow-down-tray')
                ->action(function () {
                    return Excel::download(new HandsOnRegistrationExport, 'hands-on-registrations.xlsx');
                }),
            CreateAction::make(),
        ];
    }
}
