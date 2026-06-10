# Settings: Fixed Configuration System

## Problem

Settings are currently managed as arbitrary CRUD — any user can create, edit, or delete any setting with any key, value, or type. This lacks guardrails and makes the system fragile. There's no single source of truth for what settings exist.

## Solution

Transition from a free-form CRUD settings system to a **fixed, pre-defined configuration** model. Settings are defined in `config/settings.php` and the Filament UI only allows editing values — not creating or deleting settings.

## Known Settings

| Key                            | Label                            | Type              | Default |
| ------------------------------ | -------------------------------- | ----------------- | ------- |
| `registration_open`              | Seminar Registration Open/Close  | boolean           | `true`    |
| `max_participants`               | Seminar Max Participants         | integer           | `500`     |
| `hands_on_registration_open`     | Hands On Registration Open/Close | boolean           | `true`    |
| `seminar_registration_opens_at`  | Seminar Registration Opens At    | string (datetime) | `null`    |
| `hands_on_registration_opens_at` | Hands On Registration Opens At   | string (datetime) | `null`    |

## Architecture

### Config File (`config/settings.php`)
Single source of truth. Each setting defined with: key, label, type, default, description.

### Database
- New nullable `label` column on `settings` table
- Existing columns unchanged
- Backward-compatible: all existing `Setting::create([...])` calls work without `label`

### Model (`Setting.php`)
- `$fillable` gains `label`
- New `defined(): Collection` static method that reads from `config/settings.php`
- `get()` method unchanged — still reads from DB

### Filament UI
- Table: shows all pre-defined settings, synced from config. `label` as primary display column.
- Form: only `value` field editable. `key`, `type`, `label` shown as read-only.
- No Create/Delete actions — settings are pre-defined.

### Seeder
- Idempotent: syncs from `config/settings.php`
- Creates missing settings, updates labels/descriptions on existing

### Auto-Close Logic
- `SeminarRegistration::booted()` → `created` event checks if `total_registrations >= max_participants`
- If so, auto-updates `registration_open` to `false`

### Rename
- `registration_opens_at` key → `seminar_registration_opens_at` across the codebase

## Affected Files

| File | Change |
|------|--------|
| `config/settings.php` | **Create** — new config file |
| `database/migrations/2026_XX_XX_XXXXXX_add_label_to_settings_table.php` | **Create** — add label column |
| `app/Models/Setting.php` | **Modify** — add label fillable, defined() method |
| `app/Models/SeminarRegistration.php` | **Modify** — add booted() auto-close, rename key |
| `app/Filament/Resources/Settings/SettingResource.php` | **Modify** — no create/delete, sync from config |
| `app/Filament/Resources/Settings/Schemas/SettingForm.php` | **Modify** — add label field, make key/type read-only |
| `app/Filament/Resources/Settings/Tables/SettingsTable.php` | **Modify** — add label column |
| `database/seeders/SettingSeeder.php` | **Modify** — sync from config |
| `lang/en/filament.php` | **Modify** — add label translations |
| `lang/id/filament.php` | **Modify** — add label translations |
| `app/Livewire/SeminarRegistration.php` | **Modify** — rename key reference |
