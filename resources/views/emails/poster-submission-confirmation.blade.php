<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ trans('seminar.email_poster_submission_confirmation_subject') }}</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { text-align: center; padding: 20px 0; border-bottom: 2px solid #4E397C; margin-bottom: 20px; }
        .content { padding: 20px 0; }
        .footer { text-align: center; padding: 20px 0; border-top: 1px solid #ddd; margin-top: 20px; font-size: 12px; color: #666; }
        .details { background: #f9f9f9; padding: 15px; border-radius: 5px; margin: 15px 0; }
        .detail-row { display: flex; padding: 8px 0; border-bottom: 1px solid #eee; gap: 24px; }
        .detail-label { font-weight: bold; width: 220px; flex-shrink: 0; }
        .detail-value { flex: 1; }
    </style>
</head>
<body>
    <div class="header">
        <img src="{{ asset('assets/images/JADE_PDGI_LightBG.webp') }}" alt="JADE" style="max-height: 144px; margin: 0 auto; display: block;">
    </div>

    <div class="content">
        <h2>{{ trans('seminar.email_poster_submission_confirmation_subject') }}</h2>
        <p>{{ trans('seminar.email_poster_submission_received') }}</p>

        <div class="details">
            <h3>{{ trans('seminar.poster_details_section') }}</h3>
            <div class="detail-row">
                <span class="detail-label">{{ trans('seminar.poster_title_label') }}</span>
                <span class="detail-value">{{ $submission->title }}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">{{ trans('seminar.poster_category_label') }}</span>
                <span class="detail-value">{{ $submission->category?->name ?? '-' }}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">{{ trans('seminar.poster_topic_label') }}</span>
                <span class="detail-value">{{ $submission->topic?->name ?? '-' }}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">{{ trans('seminar.presenter_name') }}</span>
                <span class="detail-value">{{ $submission->presenter_name }}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">{{ trans('seminar.poster_authors') }}</span>
                <span class="detail-value">{{ $submission->author_names }}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">{{ trans('seminar.affiliation') }}</span>
                <span class="detail-value">{{ $submission->affiliation }}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">{{ trans('seminar.poster_submitted_status') }}</span>
                <span class="detail-value">{{ $submission->status }}</span>
            </div>
        </div>

        <p>{{ trans('seminar.email_poster_submission_closing') }}</p>
        <p>{!! trans('seminar.email_attendance_confirmation_signature') !!}</p>
    </div>

    <div class="footer">
        <p>{{ trans('seminar.automated_email') }}</p>
        <p>{{ trans('seminar.email_footer') }}</p>
    </div>
</body>
</html>
