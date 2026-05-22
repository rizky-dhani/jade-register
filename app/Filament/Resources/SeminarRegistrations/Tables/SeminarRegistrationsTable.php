<?php

namespace App\Filament\Resources\SeminarRegistrations\Tables;

use App\Models\SeminarRegistration;
use App\Services\RegistrationService;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
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
            ->modifyQueryUsing(fn (Builder $query) => $query
                ->with(['country', 'seminarPackage', 'addonRegistrations.addon', 'handsOnRegistrations.handsOn']))
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
                TextColumn::make('selected_seminar_label')
                    ->label(__('seminar.seminar_package'))
                    ->state(function (SeminarRegistration $record): string {
                        $seminar = $record->seminarPackage;
                        if (! $seminar) {
                            return $record->selected_seminar ?? 'N/A';
                        }

                        // Check if early bird was active at the time of registration
                        $wasEarlyBirdActive = $seminar->early_bird_deadline !== null
                            && $record->created_at < $seminar->early_bird_deadline;

                        $price = $wasEarlyBirdActive && $seminar->discounted_price
                            ? $seminar->formatted_discounted_price.' (Early Bird)'
                            : $seminar->formatted_original_price;

                        return "{$seminar->name} ({$price})";
                    })
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->where(function (Builder $q) use ($search) {
                            $q->whereHas('seminarPackage', function (Builder $sq) use ($search) {
                                $sq->where('name', 'like', "%{$search}%")
                                    ->orWhere('code', 'like', "%{$search}%");
                            })
                                ->orWhere('selected_seminar', 'like', "%{$search}%");
                        });
                    })
                    ->sortable(query: function (Builder $query, bool $isAscending): Builder {
                        return $query->whereHas('seminarPackage', function (Builder $sq) use ($isAscending) {
                            $sq->orderBy('name', $isAscending ? 'asc' : 'desc');
                        });
                    }),
                TextColumn::make('registered_hands_on')
                    ->label(__('seminar.registered_hands_on'))
                    ->badge()
                    ->color('primary')
                    ->state(fn (SeminarRegistration $record): array => $record->handsOnRegistrations
                        ->map(function ($reg) use ($record): ?string {
                            $handsOn = $reg->handsOn;
                            if (! $handsOn) {
                                return null;
                            }

                            // Check if early bird was active at the time of registration
                            $wasEarlyBirdActive = $handsOn->early_bird_deadline !== null
                                && $record->created_at < $handsOn->early_bird_deadline;

                            $price = $wasEarlyBirdActive && $handsOn->discounted_price
                                ? $handsOn->formatted_discounted_price.' (Early Bird)'
                                : $handsOn->formatted_original_price;

                            return "{$handsOn->ho_code} ({$price})";
                        })
                        ->filter()
                        ->values()
                        ->toArray()),
                TextColumn::make('payment_status')
                    ->label(__('seminar.payment_status'))
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => ucfirst($state))
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'verified' => 'success',
                        default => 'gray',
                    })
                    ->searchable(),
                TextColumn::make('payment_method')
                    ->label(__('seminar.payment_method'))
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'bank_transfer' => __('seminar.bank_transfer'),
                        'qris' => __('seminar.qris'),
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'bank_transfer' => 'info',
                        'qris' => 'primary',
                        default => 'gray',
                    })
                    ->sortable(),
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
                TextColumn::make('addons')
                    ->label(__('seminar.available_addons'))
                    ->badge()
                    ->color('primary')
                    ->state(function (SeminarRegistration $record): array {
                        $addons = $record->addonRegistrations->loadMissing('addon');

                        if ($addons->isEmpty()) {
                            return [];
                        }

                        return $addons->map(function ($addonRegistration): string {
                            $addon = $addonRegistration->addon;
                            if (! $addon) {
                                return 'Unknown';
                            }

                            $price = $addon->currency === 'USD'
                                ? '$'.number_format($addon->price, 2)
                                : 'Rp '.number_format($addon->price, 0, ',', '.');

                            return "{$addon->name} ({$price})";
                        })->toArray();
                    }),
            ])
            ->filters([
                SelectFilter::make('payment_status')
                    ->label(__('seminar.payment_status'))
                    ->options([
                        'pending' => __('seminar.pending'),
                        'verified' => __('seminar.verified'),
                    ]),
                SelectFilter::make('payment_method')
                    ->label(__('seminar.payment_method'))
                    ->options([
                        'bank_transfer' => __('seminar.bank_transfer'),
                        'qris' => __('seminar.qris'),
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
                ViewAction::make(),
                EditAction::make()
                    ->visible(fn (): bool => auth()->user()?->can('update seminar registrations') ?? false),
                Action::make('uploadPaymentProof')
                    ->label(__('seminar.upload_payment_proof'))
                    ->icon('heroicon-o-arrow-up-tray')
                    ->color('success')
                    ->visible(fn (SeminarRegistration $record): bool => $record->payment_proof_path === null)
                    ->modalHeading(__('seminar.upload_payment_proof'))
                    ->schema([
                        FileUpload::make('payment_proof_path')
                            ->label(__('seminar.payment_proof'))
                            ->previewable()
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
                    ->label(__('seminar.view_payment_proof_seminar'))
                    ->icon('heroicon-o-photo')
                    ->color('info')
                    ->visible(fn (SeminarRegistration $record): bool => $record->payment_proof_path !== null)
                    ->modalHeading(__('seminar.view_payment_proof_seminar'))
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
                Action::make('viewHandsOnPaymentProof')
                    ->label(__('seminar.view_payment_proof_hands_on'))
                    ->icon('heroicon-o-photo')
                    ->color('info')
                    ->visible(function (SeminarRegistration $record): bool {
                        return $record->handsOnRegistrations->contains(
                            fn ($reg) => $reg->payment_proof_path !== null
                        );
                    })
                    ->modalHeading(__('seminar.view_payment_proof_hands_on'))
                    ->slideOver()
                    ->modalContent(function (SeminarRegistration $record) {
                        $handsOnReg = $record->handsOnRegistrations
                            ->first(fn ($reg) => $reg->payment_proof_path !== null);

                        if (! $handsOnReg) {
                            return '';
                        }

                        $url = asset('storage/'.$handsOnReg->payment_proof_path);
                        $extension = pathinfo($handsOnReg->payment_proof_path, PATHINFO_EXTENSION);

                        return view('components.payment-proof-modal', [
                            'url' => $url,
                            'extension' => strtolower($extension),
                        ]);
                    })
                    ->extraModalFooterActions(function (SeminarRegistration $record): array {
                        $handsOnReg = $record->handsOnRegistrations
                            ->first(fn ($reg) => $reg->payment_status === 'pending' && $reg->payment_proof_path !== null);

                        if (! $handsOnReg) {
                            return [];
                        }

                        return [
                            Action::make('verifyHandsOnPayment')
                                ->label(__('seminar.verify_payment'))
                                ->icon('heroicon-o-check-circle')
                                ->color('warning')
                                ->requiresConfirmation()
                                ->action(function () use ($handsOnReg): void {
                                    $handsOnReg->update([
                                        'payment_status' => 'verified',
                                        'verified_at' => now(),
                                    ]);
                                }),
                        ];
                    }),
                Action::make('resendEmailConfirmation')
                    ->label(__('seminar.resend_email_confirmation'))
                    ->icon('heroicon-o-envelope')
                    ->color('primary')
                    ->requiresConfirmation()
                    ->modalHeading(__('seminar.resend_email_confirmation'))
                    ->modalDescription(__('seminar.resend_email_confirmation_description'))
                    ->modalSubmitActionLabel(__('seminar.resend_email_confirmation'))
                    ->action(function (SeminarRegistration $record, RegistrationService $registrationService): void {
                        $registrationService->sendAttendanceConfirmation($record);
                    })
                    ->successNotificationTitle(__('seminar.email_confirmation_resent')),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('bulkVerifyPayment')
                        ->label(__('seminar.bulk_verify_payment'))
                        ->icon('heroicon-o-check-circle')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->deselectRecordsAfterCompletion()
                        ->successNotificationTitle(__('seminar.bulk_payment_verified'))
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
                    DeleteBulkAction::make()
                        ->visible(fn (): bool => auth()->user()?->can('delete seminar registrations') ?? false),
                ]),
            ]);
    }
}
