<?php

namespace App\Filament\Resources\SeminarRegistrations\Tables;

use App\Models\SeminarRegistration;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class SeminarRegistrationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('email')
                    ->searchable(),
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('name_license')
                    ->label('Name on License')
                    ->searchable(),
                TextColumn::make('nik')
                    ->label('NIK')
                    ->searchable(),
                TextColumn::make('npa')
                    ->label('NPA')
                    ->searchable(),
                TextColumn::make('pdgi_branch')
                    ->label('PDGI Branch')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('phone')
                    ->searchable(),
            ])
            ->filters([
                SelectFilter::make('payment_status')
                    ->label('Payment Status')
                    ->options([
                        'pending' => 'Pending',
                        'verified' => 'Verified',
                    ]),
                TernaryFilter::make('has_payment_proof')
                    ->label('Has Payment Proof')
                    ->trueLabel('Yes')
                    ->falseLabel('No')
                    ->nullable(),
            ])
            ->recordActions([
                Action::make('viewPaymentProof')
                    ->label('View Payment Proof')
                    ->icon('heroicon-o-photo')
                    ->visible(fn (SeminarRegistration $record): bool => $record->payment_proof_path !== null)
                    ->url(fn (SeminarRegistration $record): string => route('payment-proofs.preview', $record)),
                Action::make('verifyPayment')
                    ->label('Verify Payment')
                    ->icon('heroicon-o-check-circle')
                    ->visible(fn (SeminarRegistration $record): bool => $record->payment_status === 'pending')
                    ->requiresConfirmation()
                    ->action(function (SeminarRegistration $record): void {
                        $record->update([
                            'payment_status' => 'verified',
                            'verified_by' => auth()->id(),
                            'verified_at' => now(),
                        ]);
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
