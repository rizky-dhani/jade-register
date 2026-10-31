---
paths:
  - 'app/Filament/Resources/{SeminarRegistrations,HandsOnRegistrations}/**'
---

# Seminar Registrations Hands On Registrations

## Participant identity is owned by the seminar registration
`seminar_registrations.phone` is NOT NULL (empty string, not null, when absent) while `hands_on_registrations.phone`/`name`/`name_license` are nullable and often empty. Resolve participant name/phone/email as `seminarRegistration?->x ?: own x` — never assume the hands-on row carries them. Check `blank()`, not `=== null`, for "no phone".
