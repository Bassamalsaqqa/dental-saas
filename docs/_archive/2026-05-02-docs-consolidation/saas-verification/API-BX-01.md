# API-BX-01 Verification

## Summary
- Added plan keys for attachments and contracts features.
- Added plan limit `attachments_max_file_mb` and surfaced in control-plane plan form.
- Added non-enforcing plan reads for UI/state (`getLimitValue`, `isFeatureEnabled`).

## Commands & Evidence

### PHP Lint
```bash
php -l app/Services/PlanGuard.php
```
Output:
```
No syntax errors detected in app/Services/PlanGuard.php
```

```bash
php -l app/Controllers/ControlPlane/Plans.php
```
Output:
```
No syntax errors detected in app/Controllers/ControlPlane/Plans.php
```

```bash
php -l app/Views/control_plane/plan_form.php
```
Output:
```
No syntax errors detected in app/Views/control_plane/plan_form.php
```
