<!DOCTYPE html>
<html lang="{{ $registration->language ?? 'en' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Payment Verified</title>
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
        <img src="{{ asset('assets/images/JADE_PDGI_Light.webp') }}" alt="JADE" style="max-height: 144px; margin: 0 auto; display: block;">
    </div>
    
    <div class="content">
        @if($registration->language == 'id')
            <h2>Pembayaran Terverifikasi! Sampai Jumpa di Acara! 🎉</h2>
            <p>Kabar baik, {{ $registration->name }}! Pembayaran Anda telah diverifikasi dan registrasi Anda terkonfirmasi.</p>
        @else
            <h2>Payment Verified! See You at the Event! 🎉</h2>
            <p>Great news, {{ $registration->name }}! Your payment has been verified and your registration is confirmed.</p>
        @endif
        
        <div class="registration-code">
            {{ $registration->registration_code }}
        </div>
        
        <div class="success-box">
            <strong>✓ {{ $registration->language == 'id' ? 'Pembayaran Terverifikasi' : 'Payment Confirmed' }}</strong><br>
            {{ $registration->language == 'id' ? 'Tempat Anda di Jakarta Dental Exhibition 2026 telah dijamin!' : 'Your spot at the Jakarta Dental Exhibition 2026 is secured!' }}
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
        
        @if($registration->language == 'id')
            <h3>Detail Acara</h3>
            <p><strong>Tanggal:</strong> 13-15 November 2026</p>
            <p><strong>Lokasi:</strong> Jakarta Convention Center</p>
            <p><strong>Waktu:</strong> 09:00 - 17:00 WIB</p>
            
            <h3>Apa yang Dibawa</h3>
            <ul>
                <li>Email konfirmasi ini (cetak atau digital)</li>
                <li>KTP / Paspor yang berlaku</li>
                <li>Kode Registrasi: {{ $registration->registration_code }}</li>
            </ul>
            
            <p>Kami tidak sabar untuk bertemu dengan Anda di acara!</p>
            
            <p>Salam hangat,<br>
            <strong>Jakarta Dental Exhibition 2026</strong></p>
        @else
            <h3>Event Details</h3>
            <p><strong>Dates:</strong> 13-15 November 2026</p>
            <p><strong>Venue:</strong> Jakarta Convention Center</p>
            <p><strong>Time:</strong> 09:00 - 17:00 WIB</p>
            
            <h3>What to Bring</h3>
            <ul>
                <li>This confirmation email (printed or digital)</li>
                <li>Valid ID Card / Passport</li>
                <li>Registration Code: {{ $registration->registration_code }}</li>
            </ul>
            
            <p>We can't wait to see you at the event!</p>
            
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
