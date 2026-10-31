---
paths:
  - app/Livewire/VisitorVerify.php
  - app/Livewire/AttendanceVerify.php
---

# Livewire

## Visitor check-in writes visitor_attendances, not just scanned_at
Visitors have two attendance surfaces: `visitors.scanned_at` (QR re-scan guard, shown on the verify page) and the `visitor_attendances` table (what the admin "Visitor Attendance" list reads). Confirming a check-in must write both — stamping scanned_at alone leaves the admin list permanently empty. Role check must be `hasRole(['Admin', 'Super Admin'])`: `hasRole('Admin')` excludes the seeded Super Admin, who is allowed to redeem lunch but was silently rejected here.

## Seminar/hands-on check-in is staff-only and must be idempotent by lookup
Check-in is staff-only: `canCheckIn()` gates `checkInSeminar()` and `checkInHandsOn()` and the Blade buttons, because participants get real accounts from `/admin/register` and the verify URL is printed in their confirmation email. Do not gate on `hasRole('Admin')` alone — the seeded Super Admin would be locked out (same bug existed in VisitorVerify). Seminar insert must stay `firstOrCreate` keyed on `(seminar_registration_id, hands_on_registration_id => null, activity_type)`: the `attendance_unique_check` index includes the nullable `hands_on_registration_id`, so NULLs make it non-enforcing and only the lookup prevents duplicate seminar rows.

## Check-out stamps the existing attendance row, never a new one
Check-out writes `checked_out_at` / `checked_out_by` onto the attendance row created by check-in; it never inserts a second row, so any query counting attendances still counts people, not scans.
It is idempotent by design: `AttendanceCheckOutService` ignores a row that already has `checked_out_at`, because the first stamp is evidence of when the participant left. Same gate as check-in (`canCheckIn()`), and check-out is refused when no check-in exists yet.
Seminar rows are identified by `activity_type = seminar` AND `hands_on_registration_id IS NULL` — a hands-on row also carries `seminar_registration_id`.
