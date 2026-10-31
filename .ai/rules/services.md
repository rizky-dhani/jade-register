---
paths:
  - 'app/Services/**'
---

# Services

## Phones are normalised on read, not on write
`phone` is validated only as `required|string|max:20`, so stored values are free-form (`0812…`, `+62812…`, `62812…`, `0812-xxxx`). Never assume a stored value is dialable. Call `PhoneNumber::normalize($phone, $country)` when the number must be dialable (vCards, wa.me links); it returns E.164 and null when no digits exist. Missing country falls back to Indonesia (+62), matching the `is_indonesia ?? true` convention. Do not migrate stored phones — normalization is deliberately read-time so historical rows stay untouched.
