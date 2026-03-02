# Patient DOB Date UX Fix - 2026-03-01

- **Date:** 2026-03-01
- **Scope:** Fix false validation failure on `/patients/create` where valid `d/m/Y` input could be rejected.

## Problem

On patient create/update, users could enter a valid date like `01/01/1963` and still receive:
- `Please review the highlighted fields and try again.`
- `Date of birth must follow d/m/Y.`

Root cause:
- Shared layout JS normalizes `input[data-clinic-date="1"]` to ISO (`Y-m-d`) on submit.
- Backend DOB validation was format-strict (`valid_date[d/m/Y]` or `valid_date[m/d/Y]`), so normalized ISO submissions were rejected.

## Fix Implemented

1. Updated clinic date validation rule builder to accept valid dates regardless of whether submit payload is display-format or ISO:
- `app/Controllers/BaseController.php`
- `getClinicDateValidationRule()` now returns:
  - `required|valid_date`

2. Updated patient DOB validation message to be accurate and non-misleading:
- `app/Controllers/Patient.php`
- `getPatientValidationMessages()` now uses:
  - `Enter a valid date of birth.`

## Why This Is Safe

- Server-side normalization still converts accepted DOB values to ISO (`Y-m-d`) via `normalizePostedDateOnly()`.
- Existing clinic date format UI behavior is unchanged.
- This removes false negatives while preserving invalid-date rejection.

## Verification

### PHP lint

```bat
php -l app/Controllers/BaseController.php
php -l app/Controllers/Patient.php
```

Observed:
- `No syntax errors detected` for both files.
