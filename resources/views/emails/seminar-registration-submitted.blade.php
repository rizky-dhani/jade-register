<!DOCTYPE html>
<html lang="{{ $registration->language ?? 'en' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ trans('seminar.success_title') }}</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { text-align: center; padding: 20px 0; border-bottom: 2px solid #4E397C; margin-bottom: 20px; }
        .logo { display: flex; align-items: center; justify-content: center; gap: 20px; }
        .logo img { max-height: 144px; width: auto; }
        .content { padding: 20px 0; }
        .footer { text-align: center; padding: 20px 0; border-top: 1px solid #ddd; margin-top: 20px; font-size: 12px; color: #666; }
        .details { background: #f9f9f9; padding: 15px; border-radius: 5px; margin: 15px 0; }
        .detail-row { display: flex; padding: 8px 0; border-bottom: 1px solid #eee; gap: 24px; }
        .detail-label { font-weight: bold; width: 220px; flex-shrink: 0; }
        .detail-value { flex: 1; }
        .registration-code { background: #4E397C; color: white; padding: 15px; text-align: center; font-size: 24px; font-weight: bold; border-radius: 5px; margin: 20px 0; }
        .payment-info { background: #fff3cd; padding: 15px; border-radius: 5px; margin: 15px 0; border-left: 4px solid #ffc107; }
        .package-list { padding-left: 20px; margin: 10px 0; }
        .package-list li { margin: 5px 0; }
    </style>
</head>
<body>
    <div class="header">
        <img src="{{ asset('assets/images/JADE_PDGI_LightBG.webp') }}" alt="JADE" style="max-height: 144px; margin: 0 auto; display: block;">
    </div>
    
    <div class="content">
        @if($registration->language == 'id')
            <h2>{{ trans('seminar.email_thank_you_title', ['name' => $registration->name]) }}</h2>
            <p>{{ trans('seminar.email_registration_received') }}</p>
        @else
            <h2>{{ trans('seminar.email_thank_you_title', ['name' => $registration->name]) }}</h2>
            <p>{{ trans('seminar.email_registration_received') }}</p>
        @endif
        
        <div class="registration-code">
            {{ $registration->registration_code }}
        </div>

        @php
            $qrTokenService = app(\App\Services\QrTokenService::class);
            $qrUrl = $qrTokenService->getQrUrl($registration);
        @endphp

        @if($qrUrl)
            <div style="text-align: center; margin: 20px 0; padding: 20px; background: #f0f4ff; border-radius: 8px;">
                <h3 style="margin: 0 0 10px 0; color: #4E397C;">{{ trans('seminar.email_your_qr_code') }}</h3>
                <p style="margin: 0 0 15px 0; color: #666;">{{ trans('seminar.email_qr_code_description') }}</p>
                <a href="{{ $qrUrl }}" style="display: inline-block; padding: 12px 24px; background: #4E397C; color: white; text-decoration: none; border-radius: 5px; font-weight: bold;">
                    {{ trans('seminar.email_view_qr_code') }}
                </a>
                <p style="margin: 10px 0 0 0; font-size: 12px; color: #888;">
                    {{ trans('seminar.email_or_copy_link') }}<br>
                    <code style="font-size: 11px; word-break: break-all;">{{ $qrUrl }}</code>
                </p>
            </div>
        @endif

        {{-- Selected Package Section --}}
        <div class="details">
            <h3>{{ trans('seminar.selected_package') }}</h3>
            <ul class="package-list">
                <li>{{ $registration->selected_seminar_label }} ({{ $registration->formatted_amount }})</li>
                @foreach($registration->handsOnRegistrations as $hoReg)
                    <li>Day {{ $hoReg->handsOn->date->format('j') }}: {{ $hoReg->handsOn->name }} ({{ $hoReg->handsOn->formatted_price }})</li>
                @endforeach
            </ul>
        </div>
        
        {{-- Registrant Information Section --}}
        <div class="details">
            <h3>{{ trans('seminar.registrant_information') }}</h3>
            @if($registration->country->name !== 'Indonesia')
                {{-- International registrant - English labels --}}
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
                {{-- Indonesia registrant - Indonesian labels --}}
                <div class="detail-row">
                    <span class="detail-label">{{ trans('seminar.name_str') }}</span>
                    <span class="detail-value">{{ $registration->name }}</span>
                </div>
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
        
        @if($registration->payment_method === 'qris')
        <div class="payment-info">
            <h3>{{ trans('seminar.payment_information') }}</h3>
            <p style="text-align: center; margin-bottom: 15px;">
                <strong>{{ trans('seminar.email_payment_method') }}:</strong> QRIS
            </p>
            <div style="text-align: center; margin: 15px 0;">
                <img src="{{ asset('assets/images/QRIS_BNI_WKCI.webp') }}" alt="QRIS Code" style="max-width: 300px; width: 100%; height: auto; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
            </div>
            <p style="text-align: center; margin-top: 15px;">
                <strong>{{ trans('seminar.amount_to_transfer') }}:</strong> {{ $registration->formatted_amount }}
            </p>
            <p style="text-align: center; font-size: 12px; color: #666; margin-top: 10px;">
                {{ trans('seminar.email_qris_scan_instruction') }}
            </p>
        </div>
        @else
        <div class="payment-info">
            <h3>{{ trans('seminar.payment_information') }}</h3>
            <p><strong>{{ trans('seminar.bank') }}:</strong> {{ config('settings.bank_name', 'Bank Central Asia (BCA)') }}</p>
            <p><strong>{{ trans('seminar.account_name') }}:</strong> {{ config('settings.bank_account_name', 'PT Jakarta Dental Exhibition') }}</p>
            <p><strong>{{ trans('seminar.account_number') }}:</strong> {{ config('settings.bank_account_number', '1234567890') }}</p>
            <p><strong>{{ trans('seminar.amount_to_transfer') }}:</strong> {{ $registration->formatted_amount }}</p>
        </div>
        @endif
        
        @if($registration->language == 'id')
            <h3>{{ trans('seminar.email_next_steps') }}</h3>
            <ol>
                <li>{{ trans('seminar.email_step_1') }}</li>
                <li>{{ trans('seminar.email_step_2') }}</li>
                <li>{{ trans('seminar.email_step_3') }}</li>
                <li>{{ trans('seminar.email_step_4') }}</li>
            </ol>
            <p><strong>{{ trans('seminar.email_note') }}:</strong> {{ trans('seminar.email_cancellation_warning') }}</p>
            <p>{{ trans('seminar.email_best_regards') }}<br>
            <strong>Jakarta Dental Exhibition 2026</strong></p>
        @else
            <h3>{{ trans('seminar.email_next_steps') }}</h3>
            <ol>
                <li>{{ trans('seminar.email_step_1') }}</li>
                <li>{{ trans('seminar.email_step_2') }}</li>
                <li>{{ trans('seminar.email_step_3') }}</li>
                <li>{{ trans('seminar.email_step_4') }}</li>
            </ol>
            <p><strong>{{ trans('seminar.email_note') }}:</strong> {{ trans('seminar.email_cancellation_warning') }}</p>
            <p>{{ trans('seminar.email_best_regards') }}<br>
            <strong>Jakarta Dental Exhibition 2026</strong></p>
        @endif
    </div>
    
    <div class="footer">
        <p>{{ trans('seminar.automated_email') }}</p>
        <p>Jakarta Dental Exhibition 2026 | https://jakartadentalexhibitions.id</p>
    </div>
</body>
</html>
