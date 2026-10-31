---
paths:
  - 'resources/views/**'
---

# Views

## Nullable price accessors: guard with `!== null`, never truthiness — the string "0" is falsy
A nullable formatted-price accessor returning number_format(null-coerced-to-0) yields the string "0", which PHP treats as FALSY. So `@if ($this->formattedBundlePrice)` silently hides the line and the bug looks correct — until someone refactors to `!== null` and 'IDR 0' ships. Write the guard explicitly as `!== null`. Also: a test asserting on a price must use amounts that cannot coincide with any constant in the code (e.g. 1375000, not 1199000), otherwise a hardcoded price passes the test.
