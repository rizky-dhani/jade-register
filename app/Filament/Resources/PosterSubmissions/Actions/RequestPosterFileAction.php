<?php

namespace App\Filament\Resources\PosterSubmissions\Actions;

use App\Models\PosterSubmission;
use App\Services\RegistrationService;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;

class RequestPosterFileAction extends Action
{
    public static function getDefaultName(): ?string
    {
        return 'requestPosterFile';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label(__('filament.poster_submissions.request_poster_file'))
            ->icon(Heroicon::OutlinedEnvelope)
            ->color('warning')
            ->visible(fn (PosterSubmission $record): bool => ! DownloadPosterAction::hasPosterFile($record)
                && (auth()->user()?->can('view', $record) ?? false))
            ->requiresConfirmation()
            ->modalHeading(__('filament.poster_submissions.request_poster_file'))
            ->modalDescription(fn (PosterSubmission $record): string => __('filament.poster_submissions.request_poster_file_confirmation', [
                'emails' => $record->author_emails,
            ]))
            ->modalSubmitActionLabel(__('filament.poster_submissions.request_poster_file_send'))
            ->action(function (PosterSubmission $record): void {
                $sent = app(RegistrationService::class)->sendPosterFileRequest($record);

                if ($sent === 0) {
                    Notification::make()
                        ->title(__('filament.poster_submissions.request_poster_file_no_recipient'))
                        ->danger()
                        ->send();

                    return;
                }

                Notification::make()
                    ->title(__('filament.poster_submissions.request_poster_file_sent', ['count' => $sent]))
                    ->success()
                    ->send();
            });
    }
}
