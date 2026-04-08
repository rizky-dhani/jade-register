# Missing Translation Strings Report

## Summary
Found **47** hardcoded text strings across 13 view files that should be translated.

---

## Resources/Views/Emails/

### 1. `seminar-payment-verified.blade.php`
- Line 6: `<title>{{ __('seminar.email_payment_verified_title') }}</title>` ✓
- Line 30: `🎉` - Consider if emoji needs to be translatable
- Lines 73-75: **HARDCODED** - Dates, venue, time
  - `"13-15 November 2026"`
  - `"Jakarta Convention Center"`
  - `"09:00 - 17:00 WIB"`
- Line 87-88: **HARDCODED** - Signature
  - `"Jakarta Dental Exhibition 2026"` in footer

### 2. `seminar-registration-confirmation.blade.php` (combined template)
- Line ~147: **HARDCODED** - QRIS alt text
  - `alt="QRIS Code"`
- Line ~176, ~187: **HARDCODED** - Signature
  - `"Jakarta Dental Exhibition 2026"`
- Line ~193-194: **HARDCODED** - Footer
  - `"Jakarta Dental Exhibition 2026 | https://jakartadentalexhibitions.id"`

### 3. `verify-email.blade.php`
- Line 2: **HARDCODED** - HTML lang
  - `lang="en"` (should use dynamic locale)
- Line 6: `<title>Verify Your Email Address</title>` **HARDCODED**
- Lines 47-48: **HARDCODED** - Signature
  - `"Best regards,"`
  - `"Jakarta Dental Exhibition 2026 Team"`
- Line 53: **HARDCODED** - Footer
  - `"Jakarta Dental Exhibition 2026 | www.jakartadentalexhibition.id"`

### 4. `visitor-registration-confirmation.blade.php`
- Line 2: **HARDCODED** - Missing lang attribute
  - `<html>` should be `<html lang="{{ $visitor->language ?? 'en' }}">`
- Line 6: `<title>Welcome to Jakarta Dental Exhibition 2026!</title>` **HARDCODED**
- Lines 36-38: **HARDCODED** - Event details
  - `"13-15 November 2026"`
  - `"Jakarta Convention Center"`
  - `"09:00 - 17:00 WIB"`
- Lines 49-50: **HARDCODED** - Signature
  - `"Best regards,"`
  - `"Jakarta Dental Exhibition 2026"`
- Line 55: **HARDCODED** - Footer
  - `"Jakarta Dental Exhibition 2026 | www.jakartadentalexhibition.com"`

### 5. `seminar-payment-rejected.blade.php`
- Line 66: **HARDCODED** - Signature
  - `"Jakarta Dental Exhibition 2026"` (in email body)
- Line 71: **HARDCODED** - Footer
  - `"Jakarta Dental Exhibition 2026 | www.jakartadentalexhibition.id"`

---

## Resources/Views/Auth/

### 6. `verify-email.blade.php`
- Line 2: **HARDCODED** - HTML lang
  - `lang="en"` (should be dynamic)
- Line 6: `<title>Verify Your Email - JADE 2026</title>` **HARDCODED**

---

## Resources/Views/Livewire/

### 7. `attendance-qr-code.blade.php`
- Line 5: **HARDCODED** - Page title
  - `"Your QR Code"`
- Line 29: **HARDCODED** - Section header
  - `"Participant Details"`
- Lines 35, 39, 43, 47: **HARDCODED** - Labels
  - `"Name"`
  - `"Email"`
  - `"Registration Code"`
  - `"Payment Status"`
- Line 56: **HARDCODED** - Section header
  - `"Hands-On Sessions"`
- Line 69: **HARDCODED** - Instruction
  - `"Scan this QR code at the venue"`
- Line 77: **HARDCODED** - Label
  - `"Valid until:"`
- Lines 14-15, 22-23: **HARDCODED** - Error messages
  - `"QR Code Expired"`
  - `"This QR code has expired. Please contact the event organizers if you need assistance."`
  - `"Invalid QR Code"`
  - `"This QR code is not valid. Please check your link or contact the event organizers."`

### 8. `attendance-verify.blade.php`
✓ All text is properly translated using `__('seminar.xxx')`

### 9. `poster-submission.blade.php`
- Line 108: **HARDCODED** - Date
  - `"February 15, 2026"` (hardcoded deadline date)

### 10. `seminar-registration.blade.php`
- Lines 392-396: **HARDCODED** - Contact persons (names + WhatsApp numbers)
  - `"Drg Eka:"`, `"Drg Helani:"`, `"Drg Fitri:"`, `"Drg Annisa:"`
- Lines 417-418: **HARDCODED** - Payment method name
  - `"QRIS"` (may need translation or keep as brand name)
- Lines 447-454: **HARDCODED** - QRIS section
  - `"Scan QRIS untuk Pembayaran"` (Indonesian text)
  - `alt="QRIS Code"`
  - `"Download QRIS Code"`
- Line 41: **HARDCODED** - WhatsApp group link in success message
  - URL hardcoded in text

### 11. `visitor-registration.blade.php`
✓ All text is properly translated

---

## Resources/Views/Livewire/Partials/

### 12. `hands-on-selection.blade.php`
✓ All text is properly translated

---

## Recommended Translation Keys to Add

### Event Information
```php
'seminar.event_dates' => '13-15 November 2026',
'seminar.event_venue' => 'Jakarta Convention Center',
'seminar.event_time' => '09:00 - 17:00 WIB',
'seminar.event_year' => 'Jakarta Dental Exhibition 2026',
'seminar.poster_deadline_date' => 'February 15, 2026',
```

### QR Code Page
```php
'seminar.qr_page_title' => 'Your QR Code',
'seminar.participant_details_title' => 'Participant Details',
'seminar.label_name' => 'Name',
'seminar.label_email' => 'Email',
'seminar.label_registration_code' => 'Registration Code',
'seminar.label_payment_status' => 'Payment Status',
'seminar.label_hands_on_sessions' => 'Hands-On Sessions',
'seminar.qr_scan_instruction' => 'Scan this QR code at the venue',
'seminar.valid_until_label' => 'Valid until:',
'seminar.qr_expired_title' => 'QR Code Expired',
'seminar.qr_expired_message' => 'This QR code has expired. Please contact the event organizers if you need assistance.',
'seminar.qr_invalid_title' => 'Invalid QR Code',
'seminar.qr_invalid_message' => 'This QR code is not valid. Please check your link or contact the event organizers.',
```

### Email Templates
```php
'seminar.email_signature_best_regards' => 'Best regards,',
'seminar.email_signature_team' => 'Jakarta Dental Exhibition 2026 Team',
'seminar.email_footer' => 'Jakarta Dental Exhibition 2026 | www.jakartadentalexhibition.id',
'seminar.email_visitor_welcome_title' => 'Welcome to Jakarta Dental Exhibition 2026!',
'seminar.email_verify_page_title' => 'Verify Your Email - JADE 2026',
'seminar.email_verify_title' => 'Verify Your Email Address',
```

### Payment Section
```php
'seminar.payment_qris_title' => 'Scan QRIS untuk Pembayaran',
'seminar.payment_qris_alt' => 'QRIS Code',
'seminar.payment_download_qris' => 'Download QRIS Code',
'seminar.payment_method_qris' => 'QRIS',
```

### Contact Section
```php
'seminar.contact_person_eka' => 'Drg Eka',
'seminar.contact_person_helani' => 'Drg Helani',
'seminar.contact_person_fitri' => 'Drg Fitri',
'seminar.contact_person_annisa' => 'Drg Annisa',
```

---

## Files by Priority

### High Priority (User-facing content)
1. `attendance-qr-code.blade.php` - 15 missing strings
2. `verify-email.blade.php` (emails) - 5 missing strings
3. `visitor-registration-confirmation.blade.php` - 6 missing strings
4. `seminar-registration.blade.php` - 8 missing strings

### Medium Priority (Email templates)
5. `seminar-payment-verified.blade.php` - 3 missing strings
6. `seminar-registration-confirmation.blade.php` - 3 missing strings
7. `seminar-payment-rejected.blade.php` - 2 missing strings
8. `verify-email.blade.php` (auth) - 2 missing strings

### Low Priority (Content)
9. `poster-submission.blade.php` - 1 missing string
