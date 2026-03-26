<?php

namespace App\Filament\Pages;

use App\Filament\Navigation\NavigationGroup;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\File;

class DatabaseBackups extends Page implements \Filament\Tables\Contracts\HasTable
{
    use \Filament\Tables\Concerns\InteractsWithTable;

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-circle-stack';

    protected static \UnitEnum|string|null $navigationGroup = NavigationGroup::SETTINGS->value;

    protected string $view = 'filament.pages.database-backups';

    protected static ?int $navigationSort = 100;

    public static function canAccess(): bool
    {
        return auth()->user()?->hasRole('Super Admin') ?? false;
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('createBackup')
                ->label('Create Backup')
                ->icon('heroicon-o-plus')
                ->color('primary')
                ->requiresConfirmation()
                ->modalHeading('Create Database Backup')
                ->modalDescription('This will create a backup of the current database.')
                ->modalSubmitActionLabel('Create Backup')
                ->action(function (): void {
                    $this->createBackup();
                }),
        ];
    }

    private function createBackup(): void
    {
        $databasePath = database_path('database.sqlite');
        $backupDir = storage_path('app/backups');

        if (! File::exists($databasePath)) {
            Notification::make()
                ->title('Database file not found')
                ->danger()
                ->send();

            return;
        }

        $timestamp = now()->format('Y-m-d_H-i-s');
        $backupFilename = "backup_{$timestamp}.sqlite";
        $backupPath = "{$backupDir}/{$backupFilename}";

        try {
            File::copy($databasePath, $backupPath);

            Notification::make()
                ->title('Backup created successfully')
                ->body("Backup file: {$backupFilename}")
                ->success()
                ->send();
        } catch (\Exception $e) {
            Notification::make()
                ->title('Failed to create backup')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function table(Table $table): Table
    {
        $backupDir = storage_path('app/backups');
        $files = [];

        if (File::isDirectory($backupDir)) {
            $files = collect(File::files($backupDir))
                ->filter(fn ($file) => $file->getExtension() === 'sqlite')
                ->map(fn ($file) => [
                    'id' => $file->getFilename(),
                    'filename' => $file->getFilename(),
                    'size' => $this->formatBytes($file->getSize()),
                    'size_bytes' => $file->getSize(),
                    'created_at' => $file->getCTime(),
                ])
                ->sortByDesc('created_at')
                ->values()
                ->toArray();
        }

        return $table
            ->records(fn () => $files)
            ->columns([
                TextColumn::make('filename')
                    ->label('Filename')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('size')
                    ->label('Size')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime('Y-m-d H:i:s')
                    ->sortable(),
            ])
            ->actions([
                Action::make('download')
                    ->label('Download')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('success')
                    ->action(fn ($record) => $this->downloadBackup($record['filename'])),
                Action::make('restore')
                    ->label('Restore')
                    ->icon('heroicon-o-arrow-path')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->modalHeading('Restore Database')
                    ->modalDescription('This will replace the current database with this backup. This action cannot be undone!')
                    ->modalSubmitActionLabel('Restore Database')
                    ->action(function ($record): void {
                        $this->restoreBackup($record['filename']);
                    }),
                Action::make('delete')
                    ->label('Delete')
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->action(function ($record): void {
                        $this->deleteBackup($record['filename']);
                    }),
            ])
            ->emptyStateHeading('No backups found')
            ->emptyStateDescription('Create a backup to get started.');
    }

    private function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, $precision).' '.$units[$i];
    }

    private function downloadBackup(string $filename): void
    {
        $backupPath = storage_path("app/backups/{$filename}");

        if (! File::exists($backupPath)) {
            Notification::make()
                ->title('Backup file not found')
                ->danger()
                ->send();

            return;
        }

        $this->js("window.location.href = '".route('database-backups.download', ['filename' => $filename])."'");
    }

    private function deleteBackup(string $filename): void
    {
        $backupPath = storage_path("app/backups/{$filename}");

        if (! File::exists($backupPath)) {
            Notification::make()
                ->title('Backup file not found')
                ->danger()
                ->send();

            return;
        }

        try {
            File::delete($backupPath);

            Notification::make()
                ->title('Backup deleted successfully')
                ->success()
                ->send();
        } catch (\Exception $e) {
            Notification::make()
                ->title('Failed to delete backup')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    private function restoreBackup(string $filename): void
    {
        $backupPath = storage_path("app/backups/{$filename}");
        $databasePath = database_path('database.sqlite');

        if (! File::exists($backupPath)) {
            Notification::make()
                ->title('Backup file not found')
                ->danger()
                ->send();

            return;
        }

        try {
            // Create a safety backup of current database before restoring
            $timestamp = now()->format('Y-m-d_H-i-s');
            $safetyBackup = storage_path("app/backups/safety_backup_before_restore_{$timestamp}.sqlite");

            if (File::exists($databasePath)) {
                File::copy($databasePath, $safetyBackup);
            }

            // Restore the backup
            File::copy($backupPath, $databasePath);

            Notification::make()
                ->title('Database restored successfully')
                ->body("Restored from: {$filename}")
                ->success()
                ->send();
        } catch (\Exception $e) {
            Notification::make()
                ->title('Failed to restore backup')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }
}
