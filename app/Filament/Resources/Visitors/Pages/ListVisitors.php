<?php

namespace App\Filament\Resources\Visitors\Pages;

use App\Exports\VisitorExport;
use App\Filament\Resources\Visitors\VisitorResource;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Maatwebsite\Excel\Facades\Excel;

class ListVisitors extends ListRecords
{
    protected static string $resource = VisitorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('export')
                ->label('Export Excel')
                ->icon('heroicon-o-arrow-down-tray')
                ->action(function () {
                    return Excel::download(new VisitorExport, 'visitors.xlsx');
                }),
            CreateAction::make(),
        ];
    }
}
