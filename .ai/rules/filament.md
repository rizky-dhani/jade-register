---
paths:
  - 'tests/Feature/Filament/**'
---

# Filament

## fillForm() does not work in Filament tests — use set()
Filament v5 fillForm() silently populates NOTHING when a test is not the first to run in the process — every field reads null and validation reports 'required' errors. Verified against the pre-existing HandsOn Create page too, so it is a harness limitation in this app, not any one form. Use $component->set('data.field', $value) instead; it works reliably regardless of test order. No Filament test in this repo used fillForm before, which is why the limitation was invisible.

## callTableAction data: routes through broken fillForm() — invoke the action closure instead
Two Filament modal/form test paths silently no-op when fillForm() does not populate. (1) callTableAction('name', $record, data: [...]) routes data through fillForm(), so the action fires with an EMPTY form — the action may still succeed while rejecting nothing. (2) A modal form field is often required, so the action no-ops instead of erroring. Assert form-driven actions by invoking the action closure yourself: livewire(Page::class)->instance()->getTable()->getAction('name')->call(['record' => $record, 'data' => [...]]). That exercises the real closure and stays order-independent. See also: fillForm() does not work in Filament tests — use set().
