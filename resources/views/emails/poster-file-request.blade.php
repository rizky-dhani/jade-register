<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ trans('seminar.email_poster_file_request_subject') }}</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { text-align: center; padding: 20px 0; border-bottom: 2px solid #4E397C; margin-bottom: 20px; }
        .content { padding: 20px 0; }
        .footer { text-align: center; padding: 20px 0; border-top: 1px solid #ddd; margin-top: 20px; font-size: 12px; color: #666; }
        .details { background: #f9f9f9; padding: 15px; border-radius: 5px; margin: 15px 0; }
        .detail-row { display: flex; padding: 8px 0; border-bottom: 1px solid #eee; gap: 24px; }
        .detail-label { font-weight: bold; width: 220px; flex-shrink: 0; }
        .detail-value { flex: 1; }
        .notice { background: #FEF3C7; border-left: 4px solid #D97706; padding: 12px 15px; border-radius: 4px; margin: 15px 0; }
        .requirements { margin: 8px 0 0; padding-left: 20px; }
        .cta { text-align: center; margin: 24px 0; }
        .cta a { background: #4E397C; color: #ffffff; text-decoration: none; padding: 12px 28px; border-radius: 5px; display: inline-block; font-weight: bold; }
    </style>
</head>
<body>
    <div class="header">
        <img src="{{ asset('assets/images/JADE_PDGI_LightBG.webp') }}" alt="JADE" style="max-height: 144px; margin: 0 auto; display: block;">
    </div>

    <div class="content">
        <h2>{{ trans('seminar.email_poster_file_request_subject') }}</h2>
        <p>{{ trans('seminar.email_poster_file_request_greeting', ['name' => $submission->presenter_name]) }}</p>
        <p>{{ trans('seminar.email_poster_file_request_intro') }}</p>

        <div class="details">
            <h3>{{ trans('seminar.poster_details_section') }}</h3>
            <div class="detail-row">
                <span class="detail-label">{{ trans('seminar.poster_title_label') }}</span>
                <span class="detail-value">{{ $submission->title }}</span>
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
        </div>

        <div class="notice">
            <strong>{{ trans('seminar.email_poster_file_request_notice_title') }}</strong>
            <p style="margin: 6px 0 0;">{{ trans('seminar.email_poster_file_request_notice_body') }}</p>
        </div>

        <h3>{{ trans('seminar.email_poster_file_request_requirements_heading') }}</h3>
        <ul class="requirements">
            <li>{{ trans('seminar.email_poster_file_request_requirement_format') }}</li>
            <li>{{ trans('seminar.email_poster_file_request_requirement_size') }}</li>
            <li>{{ trans('seminar.email_poster_file_request_requirement_naming') }}</li>
        </ul>

        <div class="cta">
            <a href="{{ route('poster.submit') }}">{{ trans('seminar.email_poster_file_request_cta') }}</a>
        </div>

        <p>{{ trans('seminar.email_poster_file_request_closing') }}</p>
        <p>{!! trans('seminar.email_attendance_confirmation_signature') !!}</p>
    </div>

    <div class="footer">
        <p>{{ trans('seminar.automated_email') }}</p>
        <p>{{ trans('seminar.email_footer') }}</p>
    </div>
</body>
</html>
