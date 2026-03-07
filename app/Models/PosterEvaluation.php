<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PosterEvaluation extends Model
{
    use HasFactory;

    protected $fillable = [
        'poster_submission_id',
        'judge_id',
        'content_score',
        'creativity_score',
        'visual_score',
        'presentation_score',
        'total_score',
        'comments',
        'evaluated_at',
    ];

    protected $casts = [
        'evaluated_at' => 'datetime',
        'content_score' => 'integer',
        'creativity_score' => 'integer',
        'visual_score' => 'integer',
        'presentation_score' => 'integer',
        'total_score' => 'integer',
    ];

    public const MAX_CONTENT_SCORE = 40;

    public const MAX_CREATIVITY_SCORE = 20;

    public const MAX_VISUAL_SCORE = 20;

    public const MAX_PRESENTATION_SCORE = 20;

    public function submission(): BelongsTo
    {
        return $this->belongsTo(PosterSubmission::class, 'poster_submission_id');
    }

    public function judge(): BelongsTo
    {
        return $this->belongsTo(User::class, 'judge_id');
    }

    protected static function boot(): void
    {
        parent::boot();

        static::saving(function (self $evaluation) {
            $evaluation->total_score = ($evaluation->content_score ?? 0)
                + ($evaluation->creativity_score ?? 0)
                + ($evaluation->visual_score ?? 0)
                + ($evaluation->presentation_score ?? 0);

            $evaluation->evaluated_at ??= now();
        });

        static::saved(function (self $evaluation) {
            $evaluation->submission?->syncTotalScoreFromEvaluations();
        });

        static::deleted(function (self $evaluation) {
            $evaluation->submission?->syncTotalScoreFromEvaluations();
        });
    }
}
