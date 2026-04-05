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
        .package-list { padding-left: 20px; margin: 10px 0; }
        .package-list li { margin: 5px 0; }
        .qr-section { text-align: center; margin: 20px 0; padding: 20px; background: #f0f4ff; border-radius: 8px; }
    </style>
</head>
<body>
    <div class="header">
        <img src="{{ asset('assets/images/JADE_PDGI_LightBG.webp') }}" alt="JADE" style="max-height: 144px; margin: 0 auto; display: block;">
    </div>
    
    <div class="content">
        <h2>{{ trans('seminar.email_thank_you_title', ['name' => $registration->name]) }}</h2>
        <p>{{ trans('seminar.email_registration_received') }}</p>
        
        <div class="registration-code">
            {{ $registration->registration_code }}
        </div>

        {{-- Selected Package Section --}}
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
        
        <p>{{ trans('seminar.email_best_regards') }}<br>
        <strong>Jakarta Dental Exhibition 2026</strong></p>
    </div>
    
    <div class="footer">
        <p>{{ trans('seminar.automated_email') }}</p>
        <p>Jakarta Dental Exhibition 2026 | https://jakartadentalexhibitions.id</p>
    </div>
</body>
</html>