<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Payment Verified</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { text-align: center; padding: 20px 0; border-bottom: 2px solid #28a745; margin-bottom: 20px; }
        .logo { font-size: 24px; font-weight: bold; color: #28a745; }
        .content { padding: 20px 0; }
        .footer { text-align: center; padding: 20px 0; border-top: 1px solid #ddd; margin-top: 20px; font-size: 12px; color: #666; }
        .details { background: #f9f9f9; padding: 15px; border-radius: 5px; margin: 15px 0; }
        .detail-row { display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid #eee; }
        .detail-label { font-weight: bold; }
        .registration-code { background: #28a745; color: white; padding: 15px; text-align: center; font-size: 24px; font-weight: bold; border-radius: 5px; margin: 20px 0; }
        .success-box { background: #d4edda; padding: 15px; border-radius: 5px; margin: 15px 0; border-left: 4px solid #28a745; }
    </style>
</head>
<body>
    <div class="header">
        <div class="logo">Jakarta Dental Exhibition 2026</div>
    </div>
    
    <div class="content">
        <h2>Payment Verified! See You at the Event! 🎉</h2>
        
        <p>Great news, {{ $registration->name }}! Your payment has been verified and your registration is confirmed.</p>
        
        <div class="registration-code">
            {{ $registration->registration_code }}
        </div>
        
        <div class="success-box">
            <strong>✓ Payment Confirmed</strong><br>
            Your spot at the Jakarta Dental Exhibition 2026 is secured!
        </div>
        
        <div class="details">
            <div class="detail-row">
                <span class="detail-label">Registration Type:</span>
                <span>{{ $registration->registration_type_label }}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Pricing Tier:</span>
                <span>{{ $registration->pricing_tier_label }}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Amount Paid:</span>
                <span>{{ $registration->formatted_amount }}</span>
            </div>
        </div>
        
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
    </div>
    
    <div class="footer">
        <p>This is an automated email. Please do not reply to this message.</p>
        <p>Jakarta Dental Exhibition 2026 | www.jakartadentalexhibition.com</p>
    </div>
</body>
</html>
