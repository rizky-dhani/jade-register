<?php

namespace App\Filament\Resources\PosterSubmissions\Actions;

use App\Models\PosterSubmission;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DownloadPosterAction extends Action
{
    public static function getDefaultName(): ?string
    {
        return 'downloadPoster';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label(__('filament.poster_submissions.download_poster'))
            ->icon(Heroicon::OutlinedArrowDownTray)
            ->color('gray')
            ->visible(fn (PosterSubmission $record): bool => static::hasPosterFile($record)
                && (auth()->user()?->can('view', $record) ?? false))
            ->action(function (PosterSubmission $record): ?StreamedResponse {
                $path = $record->poster_file_path;

                if (! static::hasPosterFile($record)) {
                    Notification::make()
                        ->title(__('filament.poster_submissions.poster_file_missing'))
                        ->danger()
                        ->send();

                    return null;
                }

                $disk = Storage::disk('public');
                $filename = 'poster-'.$record->getKey().'-'.Str::slug($record->title).'.'.pathinfo($path, PATHINFO_EXTENSION);

                return $disk->download($path, $filename, [
                    'Content-Type' => $disk->mimeType($path) ?: 'application/pdf',
                ]);
            });
    }

    public static function hasPosterFile(PosterSubmission $record): bool
    {
        if (blank($record->poster_file_path)) {
            return false;
        }

        return Storage::disk('public')->exists($record->poster_file_path);
    }
}
