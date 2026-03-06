<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Welcome to Jakarta Dental Exhibition 2026!</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { text-align: center; padding: 20px 0; border-bottom: 2px solid #0066cc; margin-bottom: 20px; }
        .logo { font-size: 24px; font-weight: bold; color: #0066cc; }
        .content { padding: 20px 0; }
        .footer { text-align: center; padding: 20px 0; border-top: 1px solid #ddd; margin-top: 20px; font-size: 12px; color: #666; }
        .details { background: #f9f9f9; padding: 15px; border-radius: 5px; margin: 15px 0; }
        .detail-row { display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid #eee; }
        .detail-label { font-weight: bold; }
    </style>
</head>
<body>
    <div class="header">
        <div class="logo">Jakarta Dental Exhibition 2026</div>
    </div>
    
    <div class="content">
        <h2>Welcome, {{ $visitor->name }}!</h2>
        
        <p>Thank you for registering to visit the Jakarta Dental Exhibition 2026. We are excited to have you join us!</p>
        
        <div class="details">
            <div class="detail-row">
                <span class="detail-label">Registration ID:</span>
                <span>VIS-{{ $visitor->id }}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Preferred Visit Date:</span>
                <span>{{ $visitor->formatted_visit_date }}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Profession:</span>
                <span>{{ $visitor->profession }}</span>
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
            <li>Business cards (optional)</li>
        </ul>
        
        <p>We look forward to seeing you at the event!</p>
        
        <p>Best regards,<br>
        <strong>Jakarta Dental Exhibition 2026</strong></p>
    </div>
    
    <div class="footer">
        <p>This is an automated email. Please do not reply to this message.</p>
        <p>Jakarta Dental Exhibition 2026 | www.jakartadentalexhibition.com</p>
    </div>
</body>
</html>
