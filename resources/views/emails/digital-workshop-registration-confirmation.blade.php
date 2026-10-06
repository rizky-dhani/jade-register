<!DOCTYPE html>
<html lang="{{ $registration->language ?? 'en' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ trans('seminar.email_digital_workshop_confirmation_subject', ['code' => $registration->registration_code]) }}</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { text-align: center; padding: 20px 0; border-bottom: 2px solid #4E397C; margin-bottom: 20px; }
        .content { padding: 20px 0; }
        .footer { text-align: center; padding: 20px 0; border-top: 1px solid #ddd; margin-top: 20px; font-size: 12px; color: #666; }
        .registration-code { background: #4E397C; color: white; padding: 15px; text-align: center; font-size: 24px; font-weight: bold; border-radius: 5px; margin: 20px 0; }
        .details { background: #f9f9f9; padding: 15px; border-radius: 5px; margin: 15px 0; }
        .detail-row { display: flex; padding: 8px 0; border-bottom: 1px solid #eee; gap: 24px; }
        .detail-label { font-weight: bold; width: 220px; flex-shrink: 0; }
        .detail-value { flex: 1; }
        .payment-badge { display: inline-block; padding: 4px 12px; background: #fff8e1; border-radius: 4px; font-size: 13px; font-weight: bold; color: #e65100; }
        .bundle-note { background: #e8f5e9; padding: 15px; border-radius: 5px; margin: 20px 0; border-left: 4px solid #4caf50; color: #2e7d32; }
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
        <h2>{{ trans('seminar.email_digital_workshop_confirmation_subject', ['code' => $registration->registration_code]) }}</h2>
        <p>{{ trans('seminar.digital_workshop_success_message') }}</p>

        <div class="registration-code">
            {{ $registration->registration_code }}
        </div>

        <div class="details">
            <h3>{{ trans('seminar.registrant_information') }}</h3>
            @if($registration->country && ! $registration->country->is_indonesia)
                <div class="detail-row">
                    <span class="detail-label">{{ trans('seminar.name') }}</span>
                    <span class="detail-value">{{ $registration->name }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">{{ trans('seminar.email') }}</span>
                    <span class="detail-value">{{ $registration->email }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">{{ trans('seminar.whatsapp_number') }}</span>
                    <span class="detail-value">{{ $registration->phone }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">{{ trans('seminar.status') }}</span>
                    <span class="detail-value">{{ $registration->status }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">{{ trans('seminar.country') }}</span>
                    <span class="detail-value">{{ $registration->country->name }}</span>
                </div>
            @else
                <div class="detail-row">
                    <span class="detail-label">{{ trans('seminar.name_plataran') }}</span>
                    <span class="detail-value">{{ $registration->name_license }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">{{ trans('seminar.email_plataran') }}</span>
                    <span class="detail-value">{{ $registration->email }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">{{ trans('seminar.whatsapp_number') }}</span>
                    <span class="detail-value">{{ $registration->phone }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">{{ trans('seminar.nik') }}</span>
                    <span class="detail-value">{{ $registration->nik }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">{{ trans('seminar.pdgi_branch') }}</span>
                    <span class="detail-value">{{ $registration->pdgi_branch }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">{{ trans('seminar.competency') }}</span>
                    <span class="detail-value">{{ $registration->kompetensi }}</span>
                </div>
            @endif
        </div>

        @if($registration->digitalWorkshop)
            <div class="details">
                <h3>{{ trans('seminar.digital_workshop_title') }}</h3>
                <div class="detail-row">
                    <span class="detail-label">{{ trans('seminar.name') }}</span>
                    <span class="detail-value">{{ $registration->digitalWorkshop->name }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">{{ trans('seminar.hands_on.event_date', [], 'id') }}</span>
                    <span class="detail-value">{{ $registration->digitalWorkshop->event_date->format('d F Y') }}</span>
                </div>
                @if($registration->digitalWorkshop->event_time)
                    <div class="detail-row">
                        <span class="detail-label">{{ trans('seminar.time') }}</span>
                        <span class="detail-value">
                            {{ \Illuminate\Support\Str::substr($registration->digitalWorkshop->event_time, 0, 5) }}
                            @if($registration->digitalWorkshop->event_end_time)
                                - {{ \Illuminate\Support\Str::substr($registration->digitalWorkshop->event_end_time, 0, 5) }}
                            @endif
                        </span>
                    </div>
                @endif
                @if($registration->digitalWorkshop->location)
                    <div class="detail-row">
                        <span class="detail-label">{{ trans('seminar.location') }}</span>
                        <span class="detail-value">{{ $registration->digitalWorkshop->location }}</span>
                    </div>
                @endif
            </div>
        @endif

        <div class="details">
            <h3>{{ trans('seminar.payment_information') }}</h3>
            <div class="detail-row">
                <span class="detail-label">{{ trans('seminar.payment_method') }}</span>
                <span class="detail-value">{{ strtoupper($registration->payment_method) }}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">{{ trans('seminar.total_amount') }}</span>
                <span class="detail-value">IDR {{ number_format($registration->amount, 0, ',', '.') }}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">{{ trans('seminar.payment_status') }}</span>
                <span class="detail-value">
                    <span class="payment-badge">{{ ucfirst($registration->payment_status) }}</span>
                </span>
            </div>
        </div>

        @if($registration->isBundled())
            <div class="bundle-note">
                {{ trans('seminar.digital_workshop_bundle_applied') }}
            </div>
        @endif

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
        <p>{{ trans('seminar.email_footer') }}</p>
    </div>
</body>
</html>
