<?php

namespace App\Filament\Resources\SeminarRegistrations\Tables;

use App\Models\SeminarRegistration;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Forms\Components\FileUpload;
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
                    ->label(__('seminar.name_plataran'))
                    ->searchable(),
                TextColumn::make('nik')
                    ->label('NIK')
                    ->searchable(),
                TextColumn::make('pdgi_branch')
                    ->label(__('seminar.pdgi_branch'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('phone')
                    ->searchable(),
            ])
            ->filters([
                SelectFilter::make('payment_status')
                    ->label(__('seminar.payment_status'))
                    ->options([
                        'pending' => __('seminar.pending'),
                        'verified' => __('seminar.verified'),
                    ]),
                TernaryFilter::make('has_payment_proof')
                    ->label(__('seminar.payment_proof'))
                    ->trueLabel(__('seminar.yes'))
                    ->falseLabel(__('seminar.no'))
                    ->nullable(),
            ])
            ->recordActions([
                Action::make('uploadPaymentProof')
                    ->label(__('seminar.upload_payment_proof'))
                    ->icon('heroicon-o-arrow-up-tray')
                    ->visible(fn (SeminarRegistration $record): bool => $record->payment_proof_path === null)
                    ->modalHeading(__('seminar.upload_payment_proof'))
                    ->schema([
                        FileUpload::make('payment_proof_path')
                            ->label(__('seminar.payment_proof'))
                            ->image()
                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'application/pdf'])
                            ->maxSize(5120)
                            ->directory('payment-proofs')
                            ->visibility('public')
                            ->required(),
                    ])
                    ->action(function (array $data, SeminarRegistration $record): void {
                        $record->update([
                            'payment_proof_path' => $data['payment_proof_path'],
                        ]);
                    }),
                Action::make('viewPaymentProof')
                    ->label(__('seminar.view_payment_proof'))
                    ->icon('heroicon-o-photo')
                    ->visible(fn (SeminarRegistration $record): bool => $record->payment_proof_path !== null)
                    ->modalHeading(__('seminar.view_payment_proof'))
                    ->modalContent(function (SeminarRegistration $record) {
                        $url = asset('storage/'.$record->payment_proof_path);

                        return view('components.payment-proof-modal', [
                            'url' => $url,
                        ]);
                    }),
                Action::make('verifyPayment')
                    ->label(__('seminar.verify_payment'))
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
