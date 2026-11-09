<?php

namespace App\Filament\Resources\Attendances\Tables;

use App\Models\LunchRedemption;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

/**
 * The lunch coupon log: one row per redemption, newest first.
 *
 * Rows come from `lunch_redemptions`, so a participant appears only after the
 * coupon was handed over. The page eager loads the registration and the staff
 * user; nothing here queries per cell.
 */
class LunchRedemptionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('seminarRegistration.name_license')
                    ->label(__('filament.attendance.participant_name'))
                    ->getStateUsing(fn (LunchRedemption $record): ?string => $record->seminarRegistration?->name_license)
                    ->searchable(query: fn (Builder $query, string $search): Builder => $query
                        ->whereHas('seminarRegistration', fn (Builder $registration) => $registration
                            ->where('name_license', 'like', "%{$search}%"))),
                TextColumn::make('seminarRegistration.registration_code')
                    ->label(__('filament.attendance.registration_code'))
                    ->searchable(query: fn (Builder $query, string $search): Builder => $query
                        ->whereHas('seminarRegistration', fn (Builder $registration) => $registration
                            ->where('registration_code', 'like', "%{$search}%"))),
                TextColumn::make('seminarRegistration.email')
                    ->label(__('filament.lunch_attendance.email'))
                    ->searchable(query: fn (Builder $query, string $search): Builder => $query
                        ->whereHas('seminarRegistration', fn (Builder $registration) => $registration
                            ->where('email', 'like', "%{$search}%"))),
                TextColumn::make('entitlement')
                    ->label(__('filament.lunch_attendance.entitlement'))
                    ->badge()
                    ->state(fn (LunchRedemption $record): string => self::entitlementLabel($record))
                    ->placeholder('-'),
                TextColumn::make('redeemed_at')
                    ->label(__('lunch-coupon.redeemed_at'))
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('redeemed_by_name')
                    ->label(__('lunch-coupon.redeemed_by'))
                    ->searchable()
                    ->placeholder('-'),
            ])
            ->defaultSort('redeemed_at', 'desc')
            // A redemption is an audit record; it has no edit page of its own.
            ->recordUrl(null)
            ->filters([
                SelectFilter::make('redeemed_by')
                    ->label(__('lunch-coupon.redeemed_by'))
                    ->relationship('redeemedBy', 'name')
                    ->searchable()
                    ->preload(),
                Filter::make('redeemed_on_event_day')
                    ->label(__('filament.lunch_attendance.event_day_only'))
                    ->toggle()
                    ->query(fn (Builder $query): Builder => $query->whereDate('redeemed_at', now())),
            ]);
    }

    /**
     * Whether lunch came from the participant's seminar package or from the
     * LUNCH_ADDON they bought. The service records the redemption either way,
     * but the desk needs to know when an add-on was the reason.
     */
    private static function entitlementLabel(LunchRedemption $record): ?string
    {
        $registration = $record->seminarRegistration;

        if (! $registration) {
            return null;
        }

        if ($registration->seminarPackage?->includes_lunch) {
            return __('filament.lunch_attendance.entitlement_package');
        }

        return __('filament.lunch_attendance.entitlement_addon');
    }
}
