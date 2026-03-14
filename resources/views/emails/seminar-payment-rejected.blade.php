<!DOCTYPE html>
<html lang="{{ $registration->language ?? 'en' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Payment Verification Issue</title>
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
        @if($registration->language == 'id')
            <h2>Masalah Verifikasi Pembayaran</h2>
            <p>Dear {{ $registration->name }},</p>
            <p>Maaf, kami tidak dapat memverifikasi pembayaran Anda untuk registrasi seminar. Silakan lihat detail di bawah:</p>
        @else
            <h2>Payment Verification Issue</h2>
            <p>Dear {{ $registration->name }},</p>
            <p>Unfortunately, we were unable to verify your payment for your seminar registration. Please see the details below:</p>
        @endif
        
        <div class="registration-code">
            {{ $registration->registration_code }}
        </div>
        
        <div class="issue-box">
            <strong>{{ $registration->language == 'id' ? 'Alasan Penolakan:' : 'Reason for Rejection:' }}</strong><br>
            {{ $registration->rejection_reason }}
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
            <h3>Apa yang Harus Dilakukan Selanjutnya</h3>
            <ol>
                <li>Review alasan penolakan di atas</li>
                <li>Pastikan Anda telah mentransfer jumlah yang tepat</li>
                <li>Upload bukti pembayaran yang lebih jelas melalui akun pendaftaran Anda</li>
                <li>Tim kami akan memverifikasi ulang pembayaran Anda</li>
            </ol>
            
            <p><strong>Catatan:</strong> Pastikan bukti pembayaran Anda jelas dan menunjukkan detail transaksi dengan jelas.</p>
            
            <p>Jika Anda memiliki pertanyaan, silakan hubungi kami di info@jakartadentalexhibition.com</p>
            
            <p>Salam hangat,<br>
            <strong>Jakarta Dental Exhibition 2026</strong></p>
        @else
            <h3>What to Do Next</h3>
            <ol>
                <li>Review the rejection reason above</li>
                <li>Ensure you have transferred the exact amount</li>
                <li>Upload a clearer payment proof through your registration account</li>
                <li>Our team will re-verify your payment</li>
            </ol>
            
            <p><strong>Note:</strong> Please ensure your payment proof is clear and shows the transaction details clearly.</p>
            
            <p>If you have any questions, please contact us at info@jakartadentalexhibition.com</p>
            
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
