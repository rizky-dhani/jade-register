---
paths:
  - 'resources/views/**'
  - 'resources/views/**/*.blade.php'
---

# Views

## Nullable price accessors: guard with `!== null`, never truthiness — the string "0" is falsy
A nullable formatted-price accessor returning number_format(null-coerced-to-0) yields the string "0", which PHP treats as FALSY. So `@if ($this->formattedBundlePrice)` silently hides the line and the bug looks correct — until someone refactors to `!== null` and 'IDR 0' ships. Write the guard explicitly as `!== null`. Also: a test asserting on a price must use amounts that cannot coincide with any constant in the code (e.g. 1375000, not 1199000), otherwise a hardcoded price passes the test.

## Only use Tailwind utilities present in the deployed stylesheet
jade (cPanel) has no node, so `public/build` is the shipped artifact and cannot be rebuilt there. A utility missing from the deployed CSS renders as white-on-white (invisible button) with no error.

- Before adding a class, check it exists in the host's `public/build/assets/app-*.css`.
- Do NOT ship a local build: `resources/css/app.css` has `@source '../../storage/framework/views/*.php'`, so a local build is missing ~178 utilities that only exist in the host's compiled views (Filament panel). Verified: local app.css 875 classes vs deployed 1050.
- Precedent: commit 021b41e fixed this once; commit 58af321 reintroduced `bg-gray-700` on the per-day check-out button and it went invisible again.
- Shipped palette with hover variants: bg-blue-600/700, bg-green-600/700, bg-purple-600/700. `bg-gray-700`, `bg-gray-800`, `hover:bg-gray-800` are absent.
