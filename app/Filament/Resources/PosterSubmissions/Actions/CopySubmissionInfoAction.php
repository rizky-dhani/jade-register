<?php

namespace App\Filament\Resources\PosterSubmissions\Actions;

use App\Models\PosterSubmission;
use Filament\Actions\Action;
use Illuminate\Support\Js;

class CopySubmissionInfoAction extends Action
{
    public static function getDefaultName(): ?string
    {
        return 'copySubmissionInfo';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label(__('filament.poster_submissions.copy_submission_info'))
            ->icon('heroicon-o-clipboard-document')
            ->color('gray');

        $this->alpineClickHandler(function (PosterSubmission $record): string {
            $textJs = Js::from($this->buildSubmissionText($record));
            $messageJs = Js::from(__('filament.poster_submissions.copy_submission_info_copied'));

            return <<<JS
                window.navigator.clipboard.writeText({$textJs})
                \$tooltip({$messageJs}, {
                    theme: \$store.theme,
                    timeout: 2000,
                })
                JS;
        });
    }

    public function buildSubmissionText(PosterSubmission $record): string
    {
        $record->loadMissing(['category', 'topic']);

        $lines = [];
        $lines[] = __('filament.poster_submissions.copy_heading');
        $lines[] = __('seminar.poster_title_label').': '.($record->title ?? '—');
        $lines[] = __('filament.poster_submissions.category').': '.($record->category?->name ?? '—');
        $lines[] = __('filament.poster_submissions.topic').': '.($record->topic?->name ?? '—');
        $lines[] = __('filament.poster_submissions.author_names').': '.($record->author_names ?? '—');
        $lines[] = __('filament.poster_submissions.author_emails').': '.($record->author_emails ?? '—');
        $lines[] = __('seminar.affiliation').': '.($record->affiliation ?? '—');
        $lines[] = __('filament.poster_submissions.presenter_name').': '.($record->presenter_name ?? '—');

        return implode(PHP_EOL, $lines);
    }
}
