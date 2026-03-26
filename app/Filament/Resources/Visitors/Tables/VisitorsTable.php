<?php

namespace App\Filament\Resources\Visitors\Tables;

use App\Models\Visitor;
use App\Services\VisitorQrTokenService;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class VisitorsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('email')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('phone')
                    ->searchable(),
                TextColumn::make('affiliation')
                    ->searchable(),
                IconColumn::make('scanned_at')
                    ->label('Checked In')
                    ->icon(fn (?Visitor $record): string => $record && $record->isScanned() ? Heroicon::SolidCheckCircle : Heroicon::OutlineXCircle)
                    ->color(fn (?Visitor $record): string => $record && $record->isScanned() ? 'success' : 'danger'),
                TextColumn::make('scanned_at')
                    ->label('Checked In At')
                    ->dateTime()
                    ->sortable()
                    ->placeholder('Not checked in'),
                TextColumn::make('created_at')
                    ->sortable()
                    ->dateTime(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
                Action::make('qrCode')
                    ->label('View QR')
                    ->icon(Heroicon::Qrcode)
                    ->color('primary')
                    ->url(fn (Visitor $record): ?string => $record->qr_token ? app(VisitorQrTokenService::class)->getQrUrl($record) : null)
                    ->openUrlInNewTab()
                    ->hidden(fn (Visitor $record): bool => ! $record->qr_token),
                Action::make('confirmAttendance')
                    ->label('Confirm Attendance')
                    ->icon(Heroicon::SolidCheckCircle)
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Confirm Visitor Attendance')
                    ->modalDescription('Are you sure you want to mark this visitor as checked in? This action cannot be undone.')
                    ->modalSubmitActionLabel('Yes, Confirm Attendance')
                    ->action(function (Visitor $record): void {
                        if (! $record->isScanned()) {
                            $record->markAsScanned();
                        }
                    })
                    ->visible(fn (Visitor $record): bool => Auth::user()?->hasRole('Admin') && ! $record->isScanned()),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
