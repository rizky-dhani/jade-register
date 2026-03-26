<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Verify Your Email Address</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { text-align: center; padding: 20px 0; border-bottom: 2px solid #4E397C; margin-bottom: 20px; }
        .logo img { max-height: 144px; width: auto; }
        .content { padding: 20px 0; }
        .footer { text-align: center; padding: 20px 0; border-top: 1px solid #ddd; margin-top: 20px; font-size: 12px; color: #666; }
        .button { display: inline-block; padding: 14px 28px; background: #ffffff; color: #000000; text-decoration: none; border-radius: 5px; font-weight: bold; margin: 20px 0; border: 2px solid #000000; }
        .button:hover { background: #f0f0f0; }
        .info-box { background: #f9f9f9; padding: 15px; border-radius: 5px; margin: 15px 0; }
        .warning { background: #fff3cd; padding: 15px; border-radius: 5px; margin: 15px 0; border-left: 4px solid #ffc107; }
    </style>
</head>
<body>
    <div class="header">
        <img src="{{ asset('assets/images/JADE_PDGI_LightBG.webp') }}" alt="JADE" style="max-height: 144px; margin: 0 auto; display: block;">
    </div>
    
    <div class="content">
        <h2>{{ __('seminar.email_verify_hello') }}, {{ $user->name }}!</h2>
        
        <p>{{ __('seminar.email_verify_thank_you') }}</p>
        
        <div style="text-align: center;">
            <a href="{{ $url }}" class="button">{{ __('seminar.email_verify_button') }}</a>
        </div>
        
        <p>{{ __('seminar.email_verify_button_not_work') }}</p>
        
        <div class="info-box">
            <p style="word-break: break-all; font-size: 12px; margin: 0;">{{ $url }}</p>
        </div>
        
        <div class="warning">
            <p style="margin: 0;"><strong>{{ __('seminar.email_verify_important') }}:</strong> {{ __('seminar.email_verify_link_expire') }}</p>
        </div>
        
        <p>{{ __('seminar.email_verify_once_verified') }}</p>
        
        <p>{{ __('seminar.email_verify_questions') }}</p>
        
        <p>Best regards,<br>
        <strong>Jakarta Dental Exhibition 2026 Team</strong></p>
    </div>
    
    <div class="footer">
        <p>{{ trans('seminar.automated_email') }}</p>
        <p>Jakarta Dental Exhibition 2026 | https://jakartadentalexhibitions.id</p>
    </div>
</body>
</html>