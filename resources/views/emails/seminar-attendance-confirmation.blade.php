<!DOCTYPE html>
<html lang="{{ $registration->language ?? 'id' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ trans('seminar.email_attendance_confirmation_subject', ['code' => $registration->registration_code]) }}</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { text-align: center; padding: 20px 0; border-bottom: 2px solid #4E397C; margin-bottom: 20px; }
        .content { padding: 20px 0; }
        .footer { text-align: center; padding: 20px 0; border-top: 1px solid #ddd; margin-top: 20px; font-size: 12px; color: #666; }
        .registration-code { background: #4E397C; color: white; padding: 15px; text-align: center; font-size: 24px; font-weight: bold; border-radius: 5px; margin: 20px 0; }
        .success-box { background: #d4edda; padding: 15px; border-radius: 5px; margin: 15px 0; border-left: 4px solid #28a745; }
        .details { background: #f9f9f9; padding: 15px; border-radius: 5px; margin: 15px 0; }
        .package-list { padding-left: 20px; margin: 10px 0; }
        .package-list li { margin: 5px 0; }
        .schedule-box { background: #f0f8ff; padding: 15px; border-radius: 5px; margin: 15px 0; border: 1px solid #b3d9ff; }
        .schedule-box p { margin: 8px 0; }
        .google-maps-btn { display: inline-block; padding: 8px 16px; background: #4285f4; color: white; text-decoration: none; border-radius: 4px; font-size: 14px; margin-top: 5px; }
        .notes { background: #fff8e1; padding: 15px; border-radius: 5px; margin: 20px 0; border-left: 4px solid #ff9800; }
        .notes h3 { color: #e65100; margin-top: 0; }
        .notes ol { margin: 10px 0; padding-left: 20px; }
        .notes li { margin-bottom: 8px; }
        .important { background: #ffebee; padding: 15px; border-radius: 5px; margin: 20px 0; border-left: 4px solid #f44336; }
        .important h3 { color: #c62828; margin-top: 0; }
        .important ol { margin: 10px 0; padding-left: 20px; }
        .important li { margin-bottom: 8px; }
        .contact-info { background: #e8f5e9; padding: 15px; border-radius: 5px; margin: 20px 0; }
        .contact-info h4 { margin-top: 0; color: #2e7d32; }
        .contact-info ul { list-style: none; padding-left: 0; }
        .contact-info li { margin-bottom: 5px; }
        .contact-info a { color: #2e7d32; text-decoration: none; }
    </style>
</head>
<body>
    <div class="header">
        <img src="{{ asset('assets/images/JADE_PDGI_LightBG.webp') }}" alt="JADE" style="max-height: 144px; margin: 0 auto; display: block;">
    </div>
    
    <div class="content">
        <h2 style="text-align: center; color: #4E397C; margin-bottom: 20px;">
            {{ trans('seminar.email_attendance_confirmation_title') }}
        </h2>
        
        <p>{{ trans('seminar.email_attendance_confirmation_greeting') }}</p>
        
        <p>{!! trans('seminar.email_attendance_confirmation_message') !!}</p>
        
        <p style="margin-top: 20px;"><strong>{{ trans('seminar.email_attendance_confirmation_registered_message') }}</strong></p>
        
        <div class="schedule-box">
            <p><strong>{{ trans('seminar.email_attendance_confirmation_date') }}:</strong> {{ trans('seminar.email_attendance_confirmation_date_value') }}</p>
            <p><strong>{{ trans('seminar.email_attendance_confirmation_venue') }}:</strong> {{ trans('seminar.email_attendance_confirmation_venue_value') }}</p>
            <p><a href="https://maps.google.com/?q=Jakarta+International+Convention+Center+JICC+Senayan+Jakarta" style="display: inline-block; padding: 10px 20px; background-color: #4285f4; color: #ffffff; text-decoration: none; border-radius: 6px; font-size: 14px; font-weight: 600; margin-top: 8px;" target="_blank">{{ trans('seminar.email_attendance_confirmation_google_maps') }}</a></p>
            <p><strong>{{ trans('seminar.email_attendance_confirmation_registration_time') }}:</strong> {{ trans('seminar.email_attendance_confirmation_registration_time_value') }}</p>
            <p><strong>{{ trans('seminar.email_attendance_confirmation_dresscode') }}:</strong> {{ trans('seminar.email_attendance_confirmation_dresscode_value') }}</p>
        </div>
        
        <p>{{ trans('seminar.email_attendance_confirmation_show_email_instruction') }}</p>
        
        @php
            $qrTokenService = app(\App\Services\QrTokenService::class);
            $qrUrl = $qrTokenService->getQrUrl($registration);
        @endphp

        @if($qrUrl)
        <div style="text-align: center; margin: 20px 0; padding: 20px; background: #f0f4ff; border-radius: 8px;">
            <h3 style="margin: 0 0 10px 0; color: #4E397C;">{{ trans('seminar.email_attendance_confirmation_qr_title') }}</h3>
            <p style="margin: 0 0 15px 0; color: #666;">{{ trans('seminar.email_attendance_confirmation_qr_description') }}</p>
            <a href="{{ $qrUrl }}" style="display: inline-block; padding: 12px 24px; background-color: #4E397C; color: #ffffff; text-decoration: none; border-radius: 6px; font-size: 14px; font-weight: 600;" target="_blank">{{ trans('seminar.email_attendance_confirmation_view_qr') }}</a>
            <p style="margin: 15px 0 0 0; font-size: 12px; color: #888;">
                {{ trans('seminar.email_attendance_confirmation_or_copy') }}<br>
                <code style="font-size: 11px; word-break: break-all;">{{ $qrUrl }}</code>
            </p>
        </div>
        @endif
        
        {{-- Notes Section --}}
        <div class="notes">
            <h3>{{ trans('seminar.email_attendance_confirmation_notes_title') }}</h3>
            <ol>
                <li>{!! trans('seminar.email_attendance_confirmation_note_1') !!}</li>
                <li>{!! trans('seminar.email_attendance_confirmation_note_2') !!}</li>
                <li>{!! trans('seminar.email_attendance_confirmation_note_3') !!}</li>
                <li>{!! trans('seminar.email_attendance_confirmation_note_4') !!}</li>
                <li>{!! trans('seminar.email_attendance_confirmation_note_5') !!}</li>
            </ol>
        </div>

        {{-- Important SKP Section --}}
        <div class="important">
            <h3>{{ trans('seminar.email_attendance_confirmation_skp_title') }}</h3>
            <ol>
                <li>{!! trans('seminar.email_attendance_confirmation_skp_1') !!}</li>
                <li>{!! trans('seminar.email_attendance_confirmation_skp_2') !!}</li>
                <li>{!! trans('seminar.email_attendance_confirmation_skp_3') !!}</li>
                <li>{!! trans('seminar.email_attendance_confirmation_skp_4') !!}</li>
            </ol>
        </div>

        <p>{{ trans('seminar.email_attendance_confirmation_closing') }}</p>
        <p>{!! trans('seminar.email_attendance_confirmation_signature') !!}</p>

        {{-- Contact Information --}}
        <div class="contact-info">
            <h4>{{ trans('seminar.email_attendance_confirmation_contact_title') }}</h4>
            <ul>
                <li>{!! trans('seminar.email_attendance_confirmation_contact_eka') !!}</li>
                <li>{!! trans('seminar.email_attendance_confirmation_contact_helani') !!}</li>
                <li>{!! trans('seminar.email_attendance_confirmation_contact_fitri') !!}</li>
                <li>{!! trans('seminar.email_attendance_confirmation_contact_annisa') !!}</li>
            </ul>
        </div>
    </div>
    
    <div class="footer">
        <p>{{ trans('seminar.automated_email') }}</p>
        <p>Jakarta Dental Exhibition 2026 | https://jakartadentalexhibitions.id</p>
    </div>
</body>
</html>