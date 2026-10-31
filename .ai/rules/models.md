---
paths:
  - app/Models/SeminarRegistration.php
---

# Models

## Keep lunch entitlement on relationship data
Lunch coupon eligibility is `seminarPackage.includes_lunch OR addonRegistrations.addon.code === LUNCH_ADDON`; deliberately do not filter addon payment status or seminar payment status. A redemption remains auditable even if entitlement is later edited.
