<?php

namespace App\Filament\Resources\SeminarRegistrations\Pages;

use App\Exports\SeminarRegistrationExport;
use App\Filament\Resources\SeminarRegistrations\Actions\SaveContactAction;
use App\Filament\Resources\SeminarRegistrations\SeminarRegistrationResource;
use App\Models\SeminarRegistration;
use App\Services\VCard;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ListSeminarRegistrations extends ListRecords
{
    protected static string $resource = SeminarRegistrationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('lunchRegistrations')
                ->label('Lunch Registrations')
                ->icon('heroicon-o-rectangle-stack')
                ->color('gray')
                ->url(SeminarRegistrationResource::getUrl('lunch')),
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
            Action::make('exportAllContacts')
                ->label(__('filament.contacts.export_contacts'))
                ->icon(Heroicon::OutlinedUsers)
                ->action(function (): ?StreamedResponse {
                    $contacts = SaveContactAction::contactsFor($this->getFilteredTableQuery()?->get() ?? collect());

                    if ($contacts === []) {
                        Notification::make()
                            ->title(__('filament.contacts.none_found'))
                            ->warning()
                            ->send();

                        return null;
                    }

                    return VCard::download($contacts, 'seminar-contacts_'.now()->format('d-m-Y_H-i-s').'.vcf');
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
