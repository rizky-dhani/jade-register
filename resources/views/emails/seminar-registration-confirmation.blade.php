<!DOCTYPE html>
<html lang="{{ $registration->language ?? 'en' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ trans('seminar.email_registration_confirmation_subject', ['code' => $registration->registration_code]) }}</title>
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
        .package-list { padding-left: 20px; margin: 10px 0; }
        .package-list li { margin: 5px 0; }
        .schedule-box { background: #f0f8ff; padding: 15px; border-radius: 5px; margin: 15px 0; border: 1px solid #b3d9ff; }
        .schedule-box p { margin: 8px 0; }
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
        {{-- Thank You Message + Registration Code --}}
        <h2>{{ trans('seminar.email_thank_you_title', ['name' => $registration->name]) }}</h2>
        <p>{{ trans('seminar.email_registration_received') }}</p>

        <div class="registration-code">
            {{ $registration->registration_code }}
        </div>

        {{-- Selected Package Details --}}
        <div class="details">
            <h3>{{ trans('seminar.selected_package') }}</h3>
            <ul class="package-list">
                @php
                    $seminar = \App\Models\Seminar::where('code', $registration->selected_seminar)->first();
                @endphp
                @if($seminar)
                    <li>
                        {{ $seminar->name }} ({{ $seminar->label }})
                        @if($seminar->isEarlyBirdActive() && $seminar->discounted_price)
                            - <span style="text-decoration: line-through; color: #999;">{{ $seminar->formatted_original_price }}</span>
                            <strong>{{ $seminar->formatted_discounted_price }}</strong>
                        @else
                            - <strong>{{ $seminar->formatted_current_price }}</strong>
                        @endif
                    </li>
                @else
                    <li>{{ $registration->selected_seminar_label }}</li>
                @endif
            </ul>
        </div>

        {{-- Registrant Information --}}
        <div class="details">
            <h3>{{ trans('seminar.registrant_information') }}</h3>
            @if($registration->country->name !== 'Indonesia')
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

        {{-- Hands On Sessions --}}
        @if($registration->wants_hands_on && $registration->handsOnRegistrations->isNotEmpty())
            <div class="details">
                <h3>{{ trans('seminar.email_hands_on_sessions_title') }}</h3>
                @foreach($registration->handsOnRegistrations as $hoReg)
                    <div style="padding: 10px 0; {{ ! $loop->last ? 'border-bottom: 1px solid #eee;' : '' }}">
                        <p style="margin: 0 0 8px 0;"><strong>{{ $hoReg->handsOn->name }}</strong></p>
                        <div class="detail-row">
                            <span class="detail-label">{{ trans('seminar.email_hands_on_doctor_label') }}</span>
                            <span class="detail-value">{{ $hoReg->handsOn->doctor_name ?? '-' }}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">{{ trans('seminar.email_hands_on_date_label') }}</span>
                            <span class="detail-value">{{ $hoReg->handsOn->event_date->format('d F Y') }}</span>
                        </div>
                        @if($hoReg->handsOn->description)
                            <div class="detail-row">
                                <span class="detail-label">{{ trans('seminar.email_hands_on_description_label') }}</span>
                                <span class="detail-value">{{ $hoReg->handsOn->description }}</span>
                            </div>
                        @endif
                        <div class="detail-row">
                            <span class="detail-label">{{ trans('seminar.email_hands_on_price_label') }}</span>
                            <span class="detail-value">
                                @if($hoReg->handsOn->isEarlyBirdActive() && $hoReg->handsOn->discounted_price)
                                    <span style="text-decoration: line-through; color: #999;">{{ $hoReg->handsOn->formatted_original_price }}</span>
                                    <strong>{{ $hoReg->handsOn->formatted_discounted_price }}</strong>
                                @else
                                    <strong>{{ $hoReg->handsOn->formatted_original_price }}</strong>
                                @endif
                            </span>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        {{-- Event Schedule and Venue Info --}}
        <h2 style="text-align: center; color: #4E397C; margin: 30px 0 20px 0;">
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

        {{-- QR Code Section --}}
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

        {{-- WhatsApp Group Section for Indonesian Participants --}}
        @if($registration->country?->is_indonesia)
        <div style="text-align: center; margin: 20px 0; padding: 20px; background: #e8f5e9; border-radius: 8px; border: 2px solid #4caf50;">
            <div style="display: flex; align-items: center; justify-content: center; margin-bottom: 10px;">
                <svg style="width: 32px; height: 32px; color: #25d366; margin-right: 10px;" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/>
                </svg>
                <h3 style="margin: 0; color: #2e7d32;">{{ trans('seminar.email_whatsapp_group_title') }}</h3>
            </div>
            <p style="margin: 10px 0 15px 0; color: #555;">{{ trans('seminar.email_whatsapp_group_description') }}</p>
            <a href="https://chat.whatsapp.com/KtELLi4Q22VHqJWFavOwhQ?mode=hq1tcla" target="_blank" style="display: inline-block; padding: 12px 24px; background-color: #25d366; color: #ffffff; text-decoration: none; border-radius: 6px; font-size: 14px; font-weight: 600;">
                <svg style="width: 16px; height: 16px; vertical-align: middle; margin-right: 5px;" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/>
                </svg>
                {{ trans('seminar.email_whatsapp_group_button') }}
            </a>
        </div>
        @endif

        {{-- Notes Section --}}
        <div class="notes">
            <h3>{{ trans('seminar.email_attendance_confirmation_notes_title') }}</h3>
            <ol>
                <li>{!! trans('seminar.email_attendance_confirmation_note_1') !!}</li>
                <li>{!! trans('seminar.email_attendance_confirmation_note_2') !!}</li>
                <li>{!! trans('seminar.email_attendance_confirmation_note_3') !!}</li>
            </ol>
        </div>

        {{-- SKP Section for Indonesian Participants --}}
        @if($registration->country?->is_indonesia)
        <div class="important">
            <h3>{{ trans('seminar.email_attendance_confirmation_skp_title') }}</h3>
            <ol>
                <li>{!! trans('seminar.email_attendance_confirmation_skp_1') !!}</li>
                <li>{!! trans('seminar.email_attendance_confirmation_skp_2') !!}</li>
                <li>{!! trans('seminar.email_attendance_confirmation_skp_3') !!}</li>
                <li>{!! trans('seminar.email_attendance_confirmation_skp_4') !!}</li>
            </ol>
        </div>
        @endif

        {{-- Closing and Signature --}}
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
        <p>{{ trans('seminar.email_footer') }}</p>
    </div>
</body>
</html>
