<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Seminar Registration Received</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { text-align: center; padding: 20px 0; border-bottom: 2px solid #0066cc; margin-bottom: 20px; }
        .logo { font-size: 24px; font-weight: bold; color: #0066cc; }
        .content { padding: 20px 0; }
        .footer { text-align: center; padding: 20px 0; border-top: 1px solid #ddd; margin-top: 20px; font-size: 12px; color: #666; }
        .details { background: #f9f9f9; padding: 15px; border-radius: 5px; margin: 15px 0; }
        .detail-row { display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid #eee; }
        .detail-label { font-weight: bold; }
        .registration-code { background: #0066cc; color: white; padding: 15px; text-align: center; font-size: 24px; font-weight: bold; border-radius: 5px; margin: 20px 0; }
        .payment-info { background: #fff3cd; padding: 15px; border-radius: 5px; margin: 15px 0; border-left: 4px solid #ffc107; }
    </style>
</head>
<body>
    <div class="header">
        <div class="logo">Jakarta Dental Exhibition 2026</div>
    </div>
    
    <div class="content">
        <h2>Thank you for registering, {{ $registration->name }}!</h2>
        
        <p>Your seminar registration has been received. Please complete your payment to confirm your spot.</p>
        
        <div class="registration-code">
            {{ $registration->registration_code }}
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
                <span class="detail-label">Amount:</span>
                <span>{{ $registration->formatted_amount }}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Country:</span>
                <span>{{ $registration->country->name }}</span>
            </div>
        </div>
        
        <div class="payment-info">
            <h3>Payment Instructions</h3>
            <p><strong>Bank:</strong> {{ config('settings.bank_name', 'Bank Central Asia (BCA)') }}</p>
            <p><strong>Account Name:</strong> {{ config('settings.bank_account_name', 'PT Jakarta Dental Exhibition') }}</p>
            <p><strong>Account Number:</strong> {{ config('settings.bank_account_number', '1234567890') }}</p>
            <p><strong>Amount to Transfer:</strong> {{ $registration->formatted_amount }}</p>
        </div>
        
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
    </div>
    
    <div class="footer">
        <p>This is an automated email. Please do not reply to this message.</p>
        <p>Jakarta Dental Exhibition 2026 | www.jakartadentalexhibition.com</p>
    </div>
</body>
</html>
