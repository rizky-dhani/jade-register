<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PosterSubmission extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'seminar_registration_id',
        'poster_category_id',
        'poster_topic_id',
        'title',
        'abstract_text',
        'author_names',
        'author_emails',
        'affiliation',
        'presenter_name',
        'poster_file_path',
        'status',
        'rejection_reason',
        'total_score',
        'rank',
        'submitted_at',
        'finalist_announced_at',
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
        'finalist_announced_at' => 'datetime',
        'total_score' => 'integer',
        'rank' => 'integer',
    ];

    public const STATUS_DRAFT = 'draft';

    public const STATUS_SUBMITTED = 'submitted';

    public const STATUS_UNDER_REVIEW = 'under_review';

    public const STATUS_ACCEPTED = 'accepted';

    public const STATUS_FINALIST = 'finalist';

    public const STATUS_WINNER = 'winner';

    public const STATUS_REJECTED = 'rejected';

    public const MIN_EVALUATIONS_FOR_ACCEPTED = 2;

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function seminarRegistration(): BelongsTo
    {
        return $this->belongsTo(SeminarRegistration::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(PosterCategory::class, 'poster_category_id');
    }

    public function topic(): BelongsTo
    {
        return $this->belongsTo(PosterTopic::class, 'poster_topic_id');
    }

    public function evaluations(): HasMany
    {
        return $this->hasMany(PosterEvaluation::class);
    }

    public function canSubmit(): bool
    {
        return $this->seminarRegistration?->payment_status === 'verified';
    }

    public function getAverageScoreAttribute(): ?int
    {
        $evaluations = $this->evaluations;

        if ($evaluations->isEmpty()) {
            return null;
        }

        return (int) round($evaluations->avg('total_score'));
    }

    public function syncTotalScoreFromEvaluations(): void
    {
        $this->total_score = $this->average_score;
        $this->autoUpdateStatus();
        $this->saveQuietly();
    }

    protected function autoUpdateStatus(): void
    {
        $evaluationCount = $this->evaluations()->count();

        if ($evaluationCount > 0 && $this->status === self::STATUS_SUBMITTED) {
            $this->status = self::STATUS_UNDER_REVIEW;
        }

        if ($evaluationCount >= self::MIN_EVALUATIONS_FOR_ACCEPTED
            && $this->status === self::STATUS_UNDER_REVIEW) {
            $this->status = self::STATUS_ACCEPTED;
        }

        if ($evaluationCount === 0 && $this->status === self::STATUS_UNDER_REVIEW) {
            $this->status = self::STATUS_SUBMITTED;
        }
    }
}
