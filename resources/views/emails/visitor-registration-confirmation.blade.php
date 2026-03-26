<!DOCTYPE html>
<html lang="{{ $visitor->language ?? 'en' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('seminar.email_visitor_title_welcome') }} Jakarta Dental Exhibition 2026!</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px; }
        .logo { display: flex; align-items: center; justify-content: center; gap: 20px; padding-bottom: 20px; border-bottom: 2px solid #0066cc; margin-bottom: 20px; }
        .logo img { max-height: 144px; width: auto; }
        .content { padding: 20px 0; }
        .footer { text-align: center; padding: 20px 0; border-top: 1px solid #ddd; margin-top: 20px; font-size: 12px; color: #666; }
        .details { background: #f9f9f9; padding: 15px; border-radius: 5px; margin: 15px 0; }
        .detail-row { display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid #eee; }
        .detail-label { font-weight: bold; }
        .qr-section { background: #f0f8ff; padding: 20px; border-radius: 8px; margin: 20px 0; text-align: center; border: 2px solid #0066cc; }
        .qr-section h3 { color: #0066cc; margin-bottom: 15px; }
        .qr-button { display: inline-block; padding: 12px 24px; background: #0066cc; color: white; text-decoration: none; border-radius: 5px; font-weight: bold; margin: 10px 0; }
        .qr-code { margin: 15px 0; }
        .qr-code img { max-width: 200px; height: auto; }
    </style>
</head>
<body>
    <div class="logo">
        <img src="{{ asset('assets/images/JADE_PDGI_LightBG.webp') }}" alt="JADE" style="max-height: 144px; margin: 0 auto; display: block;">
    </div>
    
    <div class="content">
        <h2>{{ __('seminar.email_visitor_welcome') }}, {{ $visitor->name }}!</h2>
        
        <p>{{ __('seminar.email_visitor_thank_you') }}</p>
        
        <div class="details">
            <div class="detail-row">
                <span class="detail-label">{{ __('seminar.email_visitor_registration_id') }}:</span>
                <span>VIS-{{ $visitor->id }}</span>
            </div>
        </div>
        
        @php
            $qrTokenService = app(\App\Services\VisitorQrTokenService::class);
            $qrUrl = $qrTokenService->getQrUrl($visitor);
        @endphp

        @if($qrUrl)
        <div class="qr-section">
            <h3>{{ trans('seminar.email_payment_verified_qr_title') }}</h3>
            <p style="margin: 0 0 15px 0; color: #666;">{{ trans('seminar.visitor_qr_code_description') }}</p>
            <a href="{{ $qrUrl }}" class="qr-button">
                {{ trans('seminar.email_payment_verified_view_qr') }}
            </a>
            <p style="margin: 10px 0 0 0; font-size: 12px; color: #999;">
                {{ trans('seminar.email_payment_verified_or_copy') }}:<br>
                <code style="font-size: 11px; word-break: break-all;">{{ $qrUrl }}</code>
            </p>
        </div>
        @endif

        <h3>{{ __('seminar.email_visitor_event_details') }}</h3>
        <p><strong>{{ __('seminar.email_visitor_dates') }}:</strong> 13-15 November 2026</p>
        <p><strong>{{ __('seminar.email_visitor_venue') }}:</strong> Jakarta Convention Center</p>
        <p><strong>{{ __('seminar.email_visitor_time') }}:</strong> 09:00 - 17:00 WIB</p>
        
        <h3>{{ __('seminar.email_visitor_what_to_bring') }}</h3>
        <ul>
            <li>{{ __('seminar.email_visitor_bring_item_1') }}</li>
            <li>{{ __('seminar.email_visitor_bring_item_2') }}</li>
            <li>{{ __('seminar.email_visitor_bring_item_3') }}</li>
        </ul>
        
        <p>{{ __('seminar.email_visitor_looking_forward') }}</p>
        
        <p>Best regards,<br>
        <strong>Jakarta Dental Exhibition 2026</strong></p>
    </div>
    
    <div class="footer">
        <p>{{ trans('seminar.automated_email') }}</p>
        <p>Jakarta Dental Exhibition 2026 | https://jakartadentalexhibitions.id</p>
    </div>
</body>
</html>
