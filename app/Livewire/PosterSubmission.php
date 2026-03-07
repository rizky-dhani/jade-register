<?php

namespace App\Livewire;

use App\Models\PosterCategory;
use App\Models\PosterSubmission as PosterSubmissionModel;
use App\Models\PosterTopic;
use Livewire\Component;
use Livewire\WithFileUploads;

class PosterSubmission extends Component
{
    use WithFileUploads;

    protected static string $view = 'livewire.poster-submission';

    public ?PosterSubmissionModel $submission = null;

    public string $title = '';

    public ?int $poster_category_id = null;

    public ?int $poster_topic_id = null;

    public string $abstract_text = '';

    public string $author_names = '';

    public string $author_emails = '';

    public string $affiliation = '';

    public string $presenter_name = '';

    public $poster_file = null;

    public bool $isSuccess = false;

    public bool $canSubmit = false;

    protected $rules = [
        'title' => 'required|string|max:255',
        'poster_category_id' => 'required|integer|exists:poster_categories,id',
        'poster_topic_id' => 'required|integer|exists:poster_topics,id',
        'abstract_text' => 'required|string|max:1500',
        'author_names' => 'required|string|max:500',
        'author_emails' => 'required|string|max:500',
        'affiliation' => 'required|string|max:255',
        'presenter_name' => 'required|string|max:255',
        'poster_file' => 'nullable|file|mimes:pdf|max:10240',
    ];

    protected $messages = [
        'abstract_text.max' => 'Abstract must not exceed 1500 characters (approximately 300 words).',
        'poster_file.max' => 'Poster file must not exceed 10MB.',
    ];

    public function mount(): void
    {
        $this->checkAccess();
    }

    public function checkAccess(): void
    {
        $user = auth()->user();

        if (! $user) {
            $this->canSubmit = false;

            return;
        }

        $registration = $user->seminarRegistrations()
            ->where('wants_poster_competition', true)
            ->where('payment_status', 'verified')
            ->latest()
            ->first();

        $this->canSubmit = $registration !== null;
    }

    public function render()
    {
        $categories = PosterCategory::where('is_active', true)->get();
        $topics = PosterTopic::where('is_active', true)->get();

        return view('livewire.poster-submission', [
            'categories' => $categories,
            'topics' => $topics,
        ]);
    }

    public function submit(): void
    {
        $this->validate();

        $user = auth()->user();

        $registration = $user->seminarRegistrations()
            ->where('wants_poster_competition', true)
            ->where('payment_status', 'verified')
            ->latest()
            ->first();

        if (! $registration) {
            session()->flash('error', 'You must have a verified seminar registration to submit a poster.');

            return;
        }

        $posterFilePath = null;
        if ($this->poster_file) {
            $posterFilePath = $this->poster_file->store('posters', 'public');
        }

        $submission = PosterSubmissionModel::create([
            'user_id' => $user->getKey(),
            'seminar_registration_id' => $registration->getKey(),
            'poster_category_id' => $this->poster_category_id,
            'poster_topic_id' => $this->poster_topic_id,
            'title' => $this->title,
            'abstract_text' => $this->abstract_text,
            'author_names' => $this->author_names,
            'author_emails' => $this->author_emails,
            'affiliation' => $this->affiliation,
            'presenter_name' => $this->presenter_name,
            'poster_file_path' => $posterFilePath,
            'status' => $posterFilePath ? 'submitted' : 'draft',
            'submitted_at' => $posterFilePath ? now() : null,
        ]);

        $this->submission = $submission;
        $this->isSuccess = true;
    }
}
