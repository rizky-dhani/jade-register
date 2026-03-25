<!DOCTYPE html>
<html lang="{{ $registration->language ?? 'en' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('seminar.email_payment_rejected_title') }}</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { text-align: center; padding: 20px 0; border-bottom: 2px solid #4E397C; margin-bottom: 20px; }
        .logo img { max-height: 144px; width: auto; }
        .content { padding: 20px 0; }
        .footer { text-align: center; padding: 20px 0; border-top: 1px solid #ddd; margin-top: 20px; font-size: 12px; color: #666; }
        .details { background: #f9f9f9; padding: 15px; border-radius: 5px; margin: 15px 0; }
        .detail-row { display: flex; padding: 8px 0; border-bottom: 1px solid #eee; }
        .detail-label { font-weight: bold; width: 200px; flex-shrink: 0; }
        .detail-value { flex: 1; }
        .registration-code { background: #4E397C; color: white; padding: 15px; text-align: center; font-size: 24px; font-weight: bold; border-radius: 5px; margin: 20px 0; }
        .issue-box { background: #f8d7da; padding: 15px; border-radius: 5px; margin: 15px 0; border-left: 4px solid #dc3545; }
        .package-list { padding-left: 20px; margin: 10px 0; }
        .package-list li { margin: 5px 0; }
    </style>
</head>
<body>
    <div class="header">
        <img src="{{ asset('assets/images/JADE_PDGI_Light.webp') }}" alt="JADE" style="max-height: 144px; margin: 0 auto; display: block;">
    </div>
    
    <div class="content">
        <h2>{{ __('seminar.email_payment_rejected_title') }}</h2>
        <p>{{ __('seminar.email_payment_rejected_greeting') }} {{ $registration->name }},</p>
        <p>{{ __('seminar.email_payment_rejected_message') }}</p>
        
        <div class="registration-code">
            {{ $registration->registration_code }}
        </div>
        
        <div class="issue-box">
            <strong>{{ __('seminar.email_payment_rejected_reason') }}:</strong><br>
            {{ $registration->rejection_reason }}
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
        
        <h3>{{ __('seminar.email_payment_rejected_next_steps_title') }}</h3>
        <ol>
            <li>{{ __('seminar.email_payment_rejected_step_1') }}</li>
            <li>{{ __('seminar.email_payment_rejected_step_2') }}</li>
            <li>{{ __('seminar.email_payment_rejected_step_3') }}</li>
            <li>{{ __('seminar.email_payment_rejected_step_4') }}</li>
        </ol>
        
        <p><strong>{{ __('seminar.email_payment_rejected_note') }}:</strong> {{ __('seminar.email_payment_rejected_note_text') }}</p>
        
        <p>{{ __('seminar.email_payment_rejected_contact') }} info@jakartadentalexhibition.com</p>
        
        <p>{{ trans('seminar.email_best_regards') }}<br>
        <strong>Jakarta Dental Exhibition 2026</strong></p>
    </div>
    
    <div class="footer">
        <p>{{ trans('seminar.automated_email') }}</p>
        <p>Jakarta Dental Exhibition 2026 | www.jakartadentalexhibition.id</p>
    </div>
</body>
</html>
