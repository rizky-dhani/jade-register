<?php

namespace App\Filament\Widgets;

use App\Models\SeminarRegistration;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class MySeminarRegistrations extends TableWidget
{
    protected static ?int $sort = 1;

    protected int|string|array $columnSpan = 'full';

    public static function canView(): bool
    {
        return auth()->user()?->hasRole('Participant');
    }

    public function getHeading(): string
    {
        return 'My Registrations';
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(fn (): Builder => SeminarRegistration::query()
                ->where('user_id', auth()->id())
                ->with(['handsOns', 'country'])
                ->latest()
            )
            ->columns([
                TextColumn::make('registration_code')
                    ->label('Registration Code')
                    ->searchable()
                    ->copyable(),
                TextColumn::make('selected_seminar')
                    ->label('Seminar Package')
                    ->searchable(),
                TextColumn::make('country.name')
                    ->label('Country'),
                TextColumn::make('payment_status')
                    ->label('Payment Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'verified' => 'success',
                        'pending' => 'warning',
                        'rejected' => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('amount')
                    ->label('Amount')
                    ->money(fn ($record) => $record->currency ?? 'IDR'),
                TextColumn::make('hands_on_registrations')
                    ->label('Hands On Sessions')
                    ->formatStateUsing(function ($record) {
                        $handsOns = $record->handsOns;
                        if ($handsOns->isEmpty()) {
                            return '-';
                        }

                        return $handsOns->map(function ($handsOn) {
                            return $handsOn->name.' ('.$handsOn->event_date->format('M d, Y').')';
                        })->implode(', ');
                    })
                    ->wrap(),
                TextColumn::make('created_at')
                    ->label('Registered At')
                    ->dateTime('M d, Y H:i')
                    ->sortable(),
            ])
            ->emptyStateHeading('No registrations yet')
            ->emptyStateDescription('You have not registered for any seminars yet.');
    }
}
