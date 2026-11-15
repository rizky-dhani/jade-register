---
paths:
  - 'database/**'
---

# Database

## Manual registration insert on jade production
Manual (paid) seminar registration on production jade must mirror the Filament create flow, not raw SQL:
- registration_code via SeminarRegistration::generateRegistrationCode(), proof file at payment-proofs/{last 6 digits}.jpeg on the `public` disk, registration_type='manual', payment_status='verified', verified_by=admin user id, verified_at=now(), language derived from country (Indonesia -> id).
- Registration row alone leaves the participant without a QR: also call QrTokenService::generate() and dispatch CompleteSeminarRegistration.
- No queue worker runs continuously; routes/console.php schedules `queue:work --stop-when-empty` every minute, so the confirmation email lands ~1 min after dispatch. Verify with confirmation_email_sent_at.
- Seminar packages: 1 = Seminar (Rp600.000), 2 = Seminar + Lunch (Rp900.000), 3 = International (USD). selected_seminar stores the plain name, e.g. "Seminar".
