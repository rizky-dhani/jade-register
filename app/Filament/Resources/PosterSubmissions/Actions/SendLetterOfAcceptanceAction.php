<?php

namespace App\Filament\Resources\PosterSubmissions\Actions;

use App\Models\PosterSubmission;
use App\Services\RegistrationService;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TagsInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Section;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Str;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class SendLetterOfAcceptanceAction extends Action
{
    public static function getDefaultName(): ?string
    {
        return 'sendLetterOfAcceptance';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label(__('filament.poster_submissions.send_loa'))
            ->icon(Heroicon::OutlinedEnvelope)
            ->color('success')
            ->visible(fn (): bool => auth()->user()?->can('update poster submissions') ?? false)
            ->modalHeading(__('filament.poster_submissions.send_loa_heading'))
            ->modalDescription(__('filament.poster_submissions.send_loa_description'))
            ->modalSubmitActionLabel(__('filament.poster_submissions.loa_submit'))
            ->schema([
                Section::make(__('filament.poster_submissions.loa_record_info'))
                    ->description(__('filament.poster_submissions.loa_record_info_description'))
                    ->columns(2)
                    ->schema([
                        TextEntry::make('title')
                            ->label(__('seminar.poster_title_label'))
                            ->state(fn (PosterSubmission $record): string => $record->title ?? '—')
                            ->copyable(),
                        TextEntry::make('presenter_name')
                            ->label(__('filament.poster_submissions.presenter_name'))
                            ->state(fn (PosterSubmission $record): string => $record->presenter_name ?? '—')
                            ->copyable(),
                        TextEntry::make('author_names')
                            ->label(__('filament.poster_submissions.author_names'))
                            ->state(fn (PosterSubmission $record): string => $record->author_names ?? '—')
                            ->copyable(),
                        TextEntry::make('author_emails')
                            ->label(__('filament.poster_submissions.author_emails'))
                            ->state(fn (PosterSubmission $record): string => $record->author_emails ?? '—')
                            ->copyable(),
                        TextEntry::make('category')
                            ->label(__('filament.poster_submissions.category'))
                            ->state(fn (PosterSubmission $record): string => $record->category?->name ?? '—')
                            ->copyable(),
                        TextEntry::make('topic')
                            ->label(__('filament.poster_submissions.topic'))
                            ->state(fn (PosterSubmission $record): string => $record->topic?->name ?? '—')
                            ->copyable(),
                        TextEntry::make('affiliation')
                            ->label(__('seminar.affiliation'))
                            ->state(fn (PosterSubmission $record): string => $record->affiliation ?? '—')
                            ->copyable(),
                        TextEntry::make('submission_number')
                            ->label(__('filament.poster_submissions.loa_submission_number'))
                            ->state(fn (PosterSubmission $record): string => 'POSTER-2026-'.$record->getKey())
                            ->copyable(),
                    ]),
                TagsInput::make('to')
                    ->label(__('filament.poster_submissions.loa_to'))
                    ->helperText(__('filament.poster_submissions.loa_emails_helper'))
                    ->default(fn (PosterSubmission $record): array => static::emailsFrom($record->author_emails))
                    ->required()
                    ->nestedRecursiveRules(['email']),
                TagsInput::make('cc')
                    ->label(__('filament.poster_submissions.loa_cc'))
                    ->helperText(__('filament.poster_submissions.loa_emails_helper'))
                    ->nestedRecursiveRules(['email']),
                TagsInput::make('bcc')
                    ->label(__('filament.poster_submissions.loa_bcc'))
                    ->helperText(__('filament.poster_submissions.loa_emails_helper'))
                    ->nestedRecursiveRules(['email']),
                FileUpload::make('letter_path')
                    ->label(__('filament.poster_submissions.loa_attachment'))
                    ->helperText(__('filament.poster_submissions.loa_attachment_helper'))
                    ->disk('local')
                    ->directory('loa-letters')
                    ->acceptedFileTypes(['application/pdf'])
                    ->maxSize(10240)
                    ->required()
                    ->getUploadedFileNameForStorageUsing(
                        fn (TemporaryUploadedFile $file, PosterSubmission $record): string => 'loa-'.$record->getKey().'-'.Str::slug($record->title).'.'.$file->getClientOriginalExtension(),
                    ),
            ])
            ->action(function (array $data, PosterSubmission $record): void {
                $letterPath = $data['letter_path'];

                $sent = app(RegistrationService::class)->sendPosterLetterOfAcceptance(
                    $record,
                    $data['to'] ?? [],
                    $data['cc'] ?? [],
                    $data['bcc'] ?? [],
                    $letterPath,
                    'LoA-JADE-2026-'.Str::slug($record->presenter_name ?: $record->title).'.'.pathinfo($letterPath, PATHINFO_EXTENSION),
                );

                if ($sent === 0) {
                    Notification::make()
                        ->title(__('filament.poster_submissions.loa_no_recipient'))
                        ->danger()
                        ->send();

                    return;
                }

                Notification::make()
                    ->title(__('filament.poster_submissions.loa_sent', ['count' => $sent]))
                    ->success()
                    ->send();
            });
    }

    /**
     * @return array<int, string>
     */
    public static function emailsFrom(?string $emails): array
    {
        return array_values(array_filter(array_map('trim', explode(',', (string) $emails))));
    }
}
