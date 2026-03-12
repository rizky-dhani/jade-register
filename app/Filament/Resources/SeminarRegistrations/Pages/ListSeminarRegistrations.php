<?php

namespace App\Filament\Resources\SeminarRegistrations\Pages;

use App\Exports\SeminarRegistrationExport;
use App\Filament\Resources\SeminarRegistrations\SeminarRegistrationResource;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Maatwebsite\Excel\Facades\Excel;

class ListSeminarRegistrations extends ListRecords
{
    protected static string $resource = SeminarRegistrationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('export')
                ->label('Export Excel')
                ->icon('heroicon-o-arrow-down-tray')
                ->action(function () {
                    return Excel::download(new SeminarRegistrationExport, 'seminar-registrations.xlsx');
                }),
            CreateAction::make(),
        ];
    }
}
