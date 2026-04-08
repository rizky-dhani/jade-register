<?php

namespace App\Filament\Resources\SeminarRegistrations\Tables;

use App\Models\SeminarRegistration;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Forms\Components\FileUpload;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class SeminarRegistrationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with('country'))
            ->defaultSort('registration_code', 'desc')
            ->columns([
                TextColumn::make('registration_code')
                    ->label(__('seminar.registration_code'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('name_license')
                    ->label(__('seminar.name_plataran'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('country.name')
                    ->label(__('seminar.country'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('participant_type')
                    ->label(__('seminar.participant_type'))
                    ->badge()
                    ->state(function (SeminarRegistration $record): string {
                        $isIndonesia = $record->country?->is_indonesia ?? true;

                        return $isIndonesia ? __('seminar.local') : __('seminar.international');
                    })
                    ->color(fn (string $state): string => match ($state) {
                        __('seminar.local') => 'success',
                        __('seminar.international') => 'warning',
                        default => 'gray',
                    })
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        $searchLower = strtolower($search);

                        if (str_contains($searchLower, 'local')) {
                            return $query->whereHas('country', fn ($q) => $q->where('is_indonesia', true))
                                ->orWhereDoesntHave('country');
                        }
                        if (str_contains($searchLower, 'international')) {
                            return $query->whereHas('country', fn ($q) => $q->where('is_indonesia', false));
                        }

                        return $query;
                    }),
                TextColumn::make('status')
                    ->label(__('seminar.status'))
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'Dentist' => __('seminar.dentist'),
                        'Student' => __('seminar.student'),
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'Dentist' => 'primary',
                        'Student' => 'success',
                        default => 'gray',
                    })
                    ->searchable(),
                TextColumn::make('phone')
                    ->searchable(),
                TextColumn::make('email')
                    ->searchable(),
                TextColumn::make('nik')
                    ->label(__('filament.seminar_registrations.nik'))
                    ->searchable(),
                TextColumn::make('pdgi_branch')
                    ->label(__('seminar.pdgi_branch'))
                    ->searchable()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('payment_status')
                    ->label(__('seminar.payment_status'))
                    ->options([
                        'pending' => __('seminar.pending'),
                        'verified' => __('seminar.verified'),
                    ]),
                SelectFilter::make('participant_type')
                    ->label(__('seminar.participant_type'))
                    ->options([
                        'local' => __('seminar.local'),
                        'international' => __('seminar.international'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        if (! $data['value']) {
                            return $query;
                        }

                        if ($data['value'] === 'local') {
                            return $query->whereHas('country', fn ($q) => $q->where('is_indonesia', true))
                                ->orWhereDoesntHave('country');
                        }

                        if ($data['value'] === 'international') {
                            return $query->whereHas('country', fn ($q) => $q->where('is_indonesia', false));
                        }

                        return $query;
                    }),
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
                    ->color('success')
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
                    ->color('info')
                    ->visible(fn (SeminarRegistration $record): bool => $record->payment_proof_path !== null)
                    ->modalHeading(__('seminar.view_payment_proof'))
                    ->slideOver()
                    ->modalContent(function (SeminarRegistration $record) {
                        $url = asset('storage/'.$record->payment_proof_path);
                        $extension = pathinfo($record->payment_proof_path, PATHINFO_EXTENSION);

                        return view('components.payment-proof-modal', [
                            'url' => $url,
                            'extension' => strtolower($extension),
                        ]);
                    })
                    ->extraModalFooterActions(function (SeminarRegistration $record): array {
                        if ($record->payment_status === 'pending') {
                            return [
                                Action::make('verifyPayment')
                                    ->label(__('seminar.verify_payment'))
                                    ->icon('heroicon-o-check-circle')
                                    ->color('warning')
                                    ->requiresConfirmation()
                                    ->action(function (SeminarRegistration $record): void {
                                        $record->update([
                                            'payment_status' => 'verified',
                                            'verified_by' => auth()->id(),
                                            'verified_at' => now(),
                                        ]);
                                    }),
                            ];
                        }

                        return [];
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('bulkVerifyPayment')
                        ->label(__('seminar.bulk_verify_payment'))
                        ->icon('heroicon-o-check-circle')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->deselectRecordsAfterCompletion()
                        ->action(function (Collection $records): void {
                            foreach ($records as $record) {
                                if ($record instanceof SeminarRegistration && $record->payment_status === 'pending') {
                                    $record->update([
                                        'payment_status' => 'verified',
                                        'verified_by' => auth()->id(),
                                        'verified_at' => now(),
                                    ]);
                                }
                            }
                        }),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
