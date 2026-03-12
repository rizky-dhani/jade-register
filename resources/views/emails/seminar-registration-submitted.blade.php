<!DOCTYPE html>
<html lang="{{ $registration->language ?? 'en' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ trans('seminar.success_title') }}</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { text-align: center; padding: 20px 0; border-bottom: 2px solid #4E397C; margin-bottom: 20px; }
        .logo img { max-width: 300px; height: auto; }
        .content { padding: 20px 0; }
        .footer { text-align: center; padding: 20px 0; border-top: 1px solid #ddd; margin-top: 20px; font-size: 12px; color: #666; }
        .details { background: #f9f9f9; padding: 15px; border-radius: 5px; margin: 15px 0; }
        .detail-row { display: flex; padding: 8px 0; border-bottom: 1px solid #eee; }
        .detail-label { font-weight: bold; width: 200px; flex-shrink: 0; }
        .detail-value { flex: 1; }
        .registration-code { background: #4E397C; color: white; padding: 15px; text-align: center; font-size: 24px; font-weight: bold; border-radius: 5px; margin: 20px 0; }
        .payment-info { background: #fff3cd; padding: 15px; border-radius: 5px; margin: 15px 0; border-left: 4px solid #ffc107; }
        .package-list { padding-left: 20px; margin: 10px 0; }
        .package-list li { margin: 5px 0; }
    </style>
</head>
<body>
    <div class="header">
        <div class="logo">
            <img src="{{ asset('assets/images/Jade_Logo.webp') }}" alt="JADE">
        </div>
    </div>
    
    <div class="content">
        @if($registration->language == 'id')
            <h2>Terima kasih telah mendaftar, {{ $registration->name }}!</h2>
            <p>Registrasi seminar Anda telah diterima. Silakan selesaikan pembayaran untuk mengkonfirmasi tempat Anda.</p>
        @else
            <h2>Thank you for registering, {{ $registration->name }}!</h2>
            <p>Your seminar registration has been received. Please complete your payment to confirm your spot.</p>
        @endif
        
        <div class="registration-code">
            {{ $registration->registration_code }}
        </div>
        
        {{-- Selected Package Section --}}
        <div class="details">
            <h3>{{ trans('seminar.selected_package') }}</h3>
            <ul class="package-list">
                <li>{{ $registration->pricing_tier_label }} ({{ $registration->formatted_amount }})</li>
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
                    <span class="detail-label">{{ trans('seminar.name') }}:</span>
                    <span class="detail-value">{{ $registration->name }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">{{ trans('seminar.email') }}:</span>
                    <span class="detail-value">{{ $registration->email }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">{{ trans('seminar.whatsapp_number') }}:</span>
                    <span class="detail-value">{{ $registration->phone }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">{{ trans('seminar.status') }}:</span>
                    <span class="detail-value">{{ $registration->status }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">{{ trans('seminar.country') }}:</span>
                    <span class="detail-value">{{ $registration->country->name }}</span>
                </div>
            @else
                {{-- Indonesia registrant - Indonesian labels --}}
                <div class="detail-row">
                    <span class="detail-label">{{ trans('seminar.name_str') }}:</span>
                    <span class="detail-value">{{ $registration->name }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">{{ trans('seminar.name_plataran') }}:</span>
                    <span class="detail-value">{{ $registration->name_license }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">{{ trans('seminar.email_plataran') }}:</span>
                    <span class="detail-value">{{ $registration->email }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">{{ trans('seminar.whatsapp_number') }}:</span>
                    <span class="detail-value">{{ $registration->phone }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">{{ trans('seminar.nik') }}:</span>
                    <span class="detail-value">{{ $registration->nik }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">{{ trans('seminar.pdgi_branch') }}:</span>
                    <span class="detail-value">{{ $registration->pdgi_branch }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">{{ trans('seminar.competency') }}:</span>
                    <span class="detail-value">{{ $registration->kompetensi }}</span>
                </div>
            @endif
        </div>
        
        <div class="payment-info">
            <h3>{{ trans('seminar.payment_information') }}</h3>
            <p><strong>{{ trans('seminar.bank') }}:</strong> {{ config('settings.bank_name', 'Bank Central Asia (BCA)') }}</p>
            <p><strong>{{ trans('seminar.account_name') }}:</strong> {{ config('settings.bank_account_name', 'PT Jakarta Dental Exhibition') }}</p>
            <p><strong>{{ trans('seminar.account_number') }}:</strong> {{ config('settings.bank_account_number', '1234567890') }}</p>
            <p><strong>{{ trans('seminar.amount_to_transfer') }}:</strong> {{ $registration->formatted_amount }}</p>
        </div>
        
        @if($registration->language == 'id')
            <h3>Langkah Selanjutnya</h3>
            <ol>
                <li>Transfer sesuai jumlah yang tertera ke rekening di atas</li>
                <li>Upload bukti pembayaran melalui akun pendaftaran Anda</li>
                <li>Tunggu verifikasi pembayaran (1-2 hari kerja)</li>
                <li>Terima email konfirmasi setelah diverifikasi</li>
            </ol>
            <p><strong>Catatan:</strong> Pendaftaran Anda akan dibatalkan jika pembayaran tidak diterima dalam 7 hari.</p>
            <p>Salam hangat,<br>
            <strong>Jakarta Dental Exhibition 2026</strong></p>
        @else
            <h3>Next Steps</h3>
            <ol>
                <li>Transfer the exact amount to the bank account above</li>
                <li>Upload your payment proof through your registration account</li>
                <li>Wait for payment verification (1-2 business days)</li>
                <li>Receive confirmation email once verified</li>
            </ol>
            <p><strong>Note:</strong> Your registration will be cancelled if payment is not received within 7 days.</p>
            <p>Best regards,<br>
            <strong>Jakarta Dental Exhibition 2026</strong></p>
        @endif
    </div>
    
    <div class="footer">
        <p>{{ trans('seminar.automated_email') }}</p>
        <p>Jakarta Dental Exhibition 2026 | www.jakartadentalexhibition.id</p>
    </div>
</body>
</html>
