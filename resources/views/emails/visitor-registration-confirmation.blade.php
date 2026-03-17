<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Welcome to Jakarta Dental Exhibition 2026!</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px; }
        .logo { display: flex; align-items: center; justify-content: center; gap: 20px; padding-bottom: 20px; border-bottom: 2px solid #0066cc; margin-bottom: 20px; }
        .logo img { max-height: 144px; width: auto; }
        .content { padding: 20px 0; }
        .footer { text-align: center; padding: 20px 0; border-top: 1px solid #ddd; margin-top: 20px; font-size: 12px; color: #666; }
        .details { background: #f9f9f9; padding: 15px; border-radius: 5px; margin: 15px 0; }
        .detail-row { display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid #eee; }
        .detail-label { font-weight: bold; }
    </style>
</head>
<body>
    <div class="logo">
        <img src="{{ asset('assets/images/JADE_PDGI_Light.webp') }}" alt="JADE" style="max-height: 144px; margin: 0 auto; display: block;">
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
        <p>Jakarta Dental Exhibition 2026 | www.jakartadentalexhibition.com</p>
    </div>
</body>
</html>
