<?php

namespace App\Filament\Resources\SeminarRegistrations\Pages;

use App\Exports\SeminarRegistrationExport;
use App\Filament\Resources\SeminarRegistrations\SeminarRegistrationResource;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\Select;
use Filament\Resources\Pages\ListRecords;
use Maatwebsite\Excel\Facades\Excel;

class ListSeminarRegistrations extends ListRecords
{
    protected static string $resource = SeminarRegistrationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('refresh')
                ->label('Refresh')
                ->icon('heroicon-o-arrow-path')
                ->color('gray')
                ->action(fn () => $this->dispatch('$refresh')),
            Action::make('export')
                ->label('Export Excel')
                ->icon('heroicon-o-arrow-down-tray')
                ->form([
                    Select::make('payment_method')
                        ->label('Filter Metode Pembayaran')
                        ->placeholder('Semua Metode')
                        ->options([
                            'bank_transfer' => 'Transfer Bank',
                            'qris' => 'QRIS',
                        ])
                        ->native(false),
                ])
                ->action(function (array $data) {
                    $paymentMethod = $data['payment_method'] ?? null;

                    return Excel::download(
                        new SeminarRegistrationExport($paymentMethod),
                        'seminar-registrations_'.now()->format('d-m-Y').'.xlsx',
                    );
                }),
            CreateAction::make(),
        ];
    }
}
