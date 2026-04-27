<?php

namespace App\Filament\Resources\Users\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('email')
                    ->label(__('filament.users.email'))
                    ->searchable(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make()
                    ->visible(fn (): bool => auth()->user()?->can('update users') ?? false),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->visible(fn (): bool => auth()->user()?->can('delete users') ?? false),
                ]),
            ]);
    }
}
