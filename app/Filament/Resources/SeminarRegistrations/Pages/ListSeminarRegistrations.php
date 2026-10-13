<?php

namespace App\Filament\Resources\SeminarRegistrations\Pages;

use App\Exports\SeminarRegistrationExport;
use App\Filament\Resources\SeminarRegistrations\SeminarRegistrationResource;
use App\Models\SeminarRegistration;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\Select;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;
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
                        'seminar-registrations_'.now()->format('d-m-Y_H-i-s').'.xlsx',
                    );
                }),
            CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make(__('seminar.all')),
            'local' => Tab::make(__('seminar.local'))
                ->badge(fn () => SeminarRegistration::query()
                    ->where(fn ($q) => $q->whereHas('country', fn ($c) => $c->where('is_indonesia', true))
                        ->orWhereDoesntHave('country'))
                    ->count())
                ->modifyQueryUsing(fn (Builder $query) => $query
                    ->where(fn ($q) => $q->whereHas('country', fn ($c) => $c->where('is_indonesia', true))
                        ->orWhereDoesntHave('country'))),
            'international' => Tab::make(__('seminar.international'))
                ->badge(fn () => SeminarRegistration::query()
                    ->whereHas('country', fn ($c) => $c->where('is_indonesia', false))
                    ->count())
                ->modifyQueryUsing(fn (Builder $query) => $query
                    ->whereHas('country', fn ($c) => $c->where('is_indonesia', false))),
        ];
    }
}
