<?php

namespace App\Filament\Resources\SeminarRegistrations\Pages;

use App\Filament\Resources\SeminarRegistrations\SeminarRegistrationResource;
use App\Filament\Resources\SeminarRegistrations\Tables\SeminarRegistrationsTable;
use App\Models\SeminarRegistration;
use Filament\Actions\Action;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ListLunchSeminarRegistrations extends ListRecords
{
    protected static string $resource = SeminarRegistrationResource::class;

    protected static ?string $title = 'Lunch Seminar Registrations';

    protected function getHeaderActions(): array
    {
        return [
            Action::make('allRegistrations')
                ->label('All Registrations')
                ->icon('heroicon-o-arrow-left')
                ->color('gray')
                ->url(SeminarRegistrationResource::getUrl('index')),
        ];
    }

    public static function lunchQuery(): Builder
    {
        return SeminarRegistration::query()->where(function (Builder $q) {
            $q->whereHas('seminarPackage', fn (Builder $p) => $p->where('includes_lunch', true))
                ->orWhereHas('addonRegistrations', fn (Builder $a) => $a
                    ->whereHas('addon', fn (Builder $ad) => $ad->where('code', 'LUNCH_ADDON')));
        });
    }

    public function table(Table $table): Table
    {
        return SeminarRegistrationsTable::configure($table)
            ->modifyQueryUsing(fn (Builder $query) => $query->where(function (Builder $q) {
                $q->whereHas('seminarPackage', fn (Builder $p) => $p->where('includes_lunch', true))
                    ->orWhereHas('addonRegistrations', fn (Builder $a) => $a
                        ->whereHas('addon', fn (Builder $ad) => $ad->where('code', 'LUNCH_ADDON')));
            }));
    }

    public function getTabs(): array
    {
        $base = static::lunchQuery();

        return [
            'all' => Tab::make(__('seminar.all'))
                ->badge((clone $base)->count()),
            'local' => Tab::make(__('seminar.local'))
                ->badge((clone $base)->where(function (Builder $q) {
                    $q->whereHas('country', fn (Builder $c) => $c->where('is_indonesia', true))
                        ->orWhereDoesntHave('country');
                })->count())
                ->modifyQueryUsing(fn (Builder $query) => $query
                    ->where(function (Builder $q) {
                        $q->whereHas('country', fn (Builder $c) => $c->where('is_indonesia', true))
                            ->orWhereDoesntHave('country');
                    })),
            'international' => Tab::make(__('seminar.international'))
                ->badge((clone $base)->whereHas('country', fn (Builder $c) => $c->where('is_indonesia', false))->count())
                ->modifyQueryUsing(fn (Builder $query) => $query
                    ->whereHas('country', fn (Builder $c) => $c->where('is_indonesia', false))),
        ];
    }
}
