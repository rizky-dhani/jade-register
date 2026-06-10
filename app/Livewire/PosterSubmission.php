<?php

namespace App\Livewire;

use App\Models\PosterCategory;
use App\Models\PosterSubmission as PosterSubmissionModel;
use App\Models\PosterTopic;
use App\Models\SeminarRegistration;
use Illuminate\Support\Facades\App;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithFileUploads;

class PosterSubmission extends Component
{
    use WithFileUploads;

    protected static string $view = 'livewire.poster-submission';

    // Verification step
    public string $verificationInput = '';

    public string $step = 'verify'; // 'verify' | 'submit' | 'success'

    public ?SeminarRegistration $verifiedRegistration = null;

    public ?string $verificationError = null;

    // Submission result
    public ?PosterSubmissionModel $submission = null;

    // Poster form fields
    public string $title = '';

    public ?int $poster_category_id = null;

    public ?int $poster_topic_id = null;

    public string $abstract_text = '';

    public string $author_names = '';

    public string $author_emails = '';

    public string $affiliation = '';

    public string $presenter_name = '';

    public mixed $poster_file = null;

    public bool $isSuccess = false;

    #[Url(as: 'lang', keep: true)]
    public string $locale = 'id';

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
        'abstract_text.max' => 'seminar.abstract_max_chars',
        'poster_file.max' => 'seminar.poster_file_max_size',
    ];

    public function mount(): void
    {
        App::setLocale($this->locale);
    }

    public function setLocale(string $locale): void
    {
        if (in_array($locale, ['en', 'id'])) {
            $this->locale = $locale;
            App::setLocale($locale);
            $this->dispatch('locale-changed', locale: $locale);
        }
    }

    public function updatedLocale(): void
    {
        $this->locale = in_array($this->locale, ['en', 'id']) ? $this->locale : 'id';
        App::setLocale($this->locale);
    }

    public function verifyRegistration(): void
    {
        $this->reset('verificationError', 'verifiedRegistration');

        $input = trim($this->verificationInput);

        if ($input === '') {
            $this->verificationError = __('seminar.poster_not_registered');

            return;
        }

        $registration = SeminarRegistration::where('email', $input)
            ->latest()
            ->first();

        if (! $registration) {
            $this->verificationError = __('seminar.poster_not_registered');

            return;
        }

        if ($registration->payment_status === 'verified') {
            $this->verifiedRegistration = $registration;
            $this->step = 'submit';
        } else {
            $this->verificationError = __('seminar.poster_pending_verification');
        }
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

        $posterFilePath = null;
        if ($this->poster_file) {
            $posterFilePath = $this->poster_file->store('posters', 'public');
        }

        $submission = PosterSubmissionModel::create([
            'user_id' => null,
            'seminar_registration_id' => $this->verifiedRegistration->getKey(),
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
        $this->step = 'success';
    }
}
