<!DOCTYPE html>
<html lang="{{ $registration->language ?? 'en' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('seminar.email_payment_verified_title') }}</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { text-align: center; padding: 20px 0; border-bottom: 2px solid #4E397C; margin-bottom: 20px; }
        .logo { display: flex; align-items: center; justify-content: center; gap: 20px; }
        .logo img { max-height: 144px; width: auto; }
        .content { padding: 20px 0; }
        .footer { text-align: center; padding: 20px 0; border-top: 1px solid #ddd; margin-top: 20px; font-size: 12px; color: #666; }
        .details { background: #f9f9f9; padding: 15px; border-radius: 5px; margin: 15px 0; }
        .detail-row { display: flex; padding: 8px 0; border-bottom: 1px solid #eee; }
        .detail-label { font-weight: bold; width: 200px; flex-shrink: 0; }
        .detail-value { flex: 1; }
        .registration-code { background: #4E397C; color: white; padding: 15px; text-align: center; font-size: 24px; font-weight: bold; border-radius: 5px; margin: 20px 0; }
        .success-box { background: #d4edda; padding: 15px; border-radius: 5px; margin: 15px 0; border-left: 4px solid #28a745; }
        .package-list { padding-left: 20px; margin: 10px 0; }
        .package-list li { margin: 5px 0; }
    </style>
</head>
<body>
    <div class="header">
        <img src="{{ asset('assets/images/JADE_PDGI_LightBG.webp') }}" alt="JADE" style="max-height: 144px; margin: 0 auto; display: block;">
    </div>
    
    <div class="content">
        <h2>{{ __('seminar.email_payment_verified_title') }}</h2>
        <p>{{ __('seminar.email_payment_verified_greeting') }}, {{ $registration->name }}! {{ __('seminar.email_payment_verified_message') }}</p>
        
        <div class="registration-code">
            {{ $registration->registration_code }}
        </div>

        @php
            $qrTokenService = app(\App\Services\QrTokenService::class);
            $qrUrl = $qrTokenService->getQrUrl($registration);
        @endphp

        @if($qrUrl)
            <div style="text-align: center; margin: 20px 0; padding: 20px; background: #f0f4ff; border-radius: 8px;">
                <h3 style="margin: 0 0 10px 0; color: #4E397C;">{{ __('seminar.email_payment_verified_qr_title') }}</h3>
                <p style="margin: 0 0 15px 0; color: #666;">{{ __('seminar.email_payment_verified_qr_description') }}</p>
                <a href="{{ $qrUrl }}" style="display: inline-block; padding: 12px 24px; background: #4E397C; color: white; text-decoration: none; border-radius: 5px; font-weight: bold;">
                    {{ __('seminar.email_payment_verified_view_qr') }}
                </a>
                <p style="margin: 10px 0 0 0; font-size: 12px; color: #888;">
                    {{ __('seminar.email_payment_verified_or_copy') }}:<br>
                    <code style="font-size: 11px; word-break: break-all;">{{ $qrUrl }}</code>
                </p>
            </div>
        @endif

        <div class="success-box">
            <strong>{{ __('seminar.email_payment_verified_confirmed') }}</strong><br>
            {{ __('seminar.email_payment_verified_spot_secured') }}
        </div>
        
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
        
        <h3>{{ __('seminar.email_payment_verified_event_details') }}</h3>
        <p><strong>{{ __('seminar.email_payment_verified_dates') }}:</strong> 13-15 November 2026</p>
        <p><strong>{{ __('seminar.email_payment_verified_venue') }}:</strong> Jakarta Convention Center</p>
        <p><strong>{{ __('seminar.email_payment_verified_time') }}:</strong> 09:00 - 17:00 WIB</p>
        
        <h3>{{ __('seminar.email_payment_verified_what_to_bring') }}</h3>
        <ul>
            <li>{{ __('seminar.email_payment_verified_bring_item_1') }}</li>
            <li>{{ __('seminar.email_payment_verified_bring_item_2') }}</li>
            <li>{{ __('seminar.email_payment_verified_bring_item_3') }}: {{ $registration->registration_code }}</li>
        </ul>
        
        <p>{{ __('seminar.email_payment_verified_excited') }}</p>
        
        <p>{{ trans('seminar.email_best_regards') }}<br>
        <strong>Jakarta Dental Exhibition 2026</strong></p>
    </div>
    
    <div class="footer">
        <p>{{ trans('seminar.automated_email') }}</p>
        <p>Jakarta Dental Exhibition 2026 | https://jakartadentalexhibitions.id</p>
    </div>
</body>
</html>
