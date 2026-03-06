<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Payment Verification Issue</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { text-align: center; padding: 20px 0; border-bottom: 2px solid #dc3545; margin-bottom: 20px; }
        .logo { font-size: 24px; font-weight: bold; color: #dc3545; }
        .content { padding: 20px 0; }
        .footer { text-align: center; padding: 20px 0; border-top: 1px solid #ddd; margin-top: 20px; font-size: 12px; color: #666; }
        .details { background: #f9f9f9; padding: 15px; border-radius: 5px; margin: 15px 0; }
        .detail-row { display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid #eee; }
        .detail-label { font-weight: bold; }
        .registration-code { background: #dc3545; color: white; padding: 15px; text-align: center; font-size: 24px; font-weight: bold; border-radius: 5px; margin: 20px 0; }
        .issue-box { background: #f8d7da; padding: 15px; border-radius: 5px; margin: 15px 0; border-left: 4px solid #dc3545; }
    </style>
</head>
<body>
    <div class="header">
        <div class="logo">Jakarta Dental Exhibition 2026</div>
    </div>
    
    <div class="content">
        <h2>Payment Verification Issue</h2>
        
        <p>Dear {{ $registration->name }},</p>
        
        <p>Unfortunately, we were unable to verify your payment for your seminar registration. Please see the details below:</p>
        
        <div class="registration-code">
            {{ $registration->registration_code }}
        </div>
        
        <div class="issue-box">
            <strong>Reason for Rejection:</strong><br>
            {{ $registration->rejection_reason }}
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
        </div>
        
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
    </div>
    
    <div class="footer">
        <p>This is an automated email. Please do not reply to this message.</p>
        <p>Jakarta Dental Exhibition 2026 | www.jakartadentalexhibition.com</p>
    </div>
</body>
</html>
