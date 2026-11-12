---
paths:
  - database/seeders/RolePermissionSeeder.php
---

# Seeders

## Admin role is a desk role with a fixed page allowlist
The `Admin` role may only reach: Attendance group (Kehadiran, Pengunjung), Data group (Visitors, Seminar registrations, Hands On registrations). It keeps CRUD + verify/reject payments + check-in/out on those, but no export and no digital workshop/poster/settings access. The lunch log is gated by its own `view lunch attendances` permission (Super Admin only). Use `syncPermissions` (not `givePermissionTo`) so re-seeding actually revokes stale grants. Resources without a policy (Addons, DigitalWorkshopRegistrationIntents) need an explicit `canAccess()` or they stay visible to every authenticated user.
