# WHATSAPP-DISPATCH-UNIFIED-INTEGRATION

Date: 2026-02-24
Status: PASS
Scope: Unified notification dispatcher integration (CLI/job/service/drivers), stale-lock recovery hardening, and SaaS guardrail remediation + Windows Git Bash guardrail compatibility fix

## Summary

This verification captures the post-Phase-0 / post-Phase-1 dispatcher integration work and supporting guardrail remediations:
- Added unified dispatcher command/job/service/driver layer
- Delegated legacy email dispatch path to unified dispatcher
- Fixed stale `processing` lock recovery and unexpected-exception lock leaks
- Added `external` recipient email support in `EmailDriver`
- Fixed SaaS guardrail script behavior on Windows Git Bash (`rg --path-separator`)
- Removed newly surfaced DOM sink and raw controller query guardrail violations

## Files Added / Updated

### Dispatcher Integration
- `app/Commands/DispatchNotifications.php`
- `app/Jobs/DispatchNotificationsJob.php`
- `app/Jobs/SendEmailNotificationsJob.php`
- `app/Services/DispatchService.php`
- `app/Services/NotificationDrivers/EmailDriver.php`
- `app/Services/NotificationDrivers/WhatsAppDriver.php`
- `app/Services/NotificationService.php`

### Guardrail Compatibility + Remediation
- `scripts/ci/saas_guardrails.sh`
- `app/Views/control_plane/users/show.php`
- `app/Views/odontogram/index.php`
- `app/Controllers/Auth.php`
- `app/Controllers/ControlPlane/Operations.php`
- `app/Controllers/Finance.php`
- `app/Models/ClinicUserModel.php`
- `app/Models/ProviderPayoutModel.php`

## Preconditions Verified

### 1) Phase 0 and Phase 1 are committed in git history

Command:

```powershell
git log --oneline -n 8
```

Observed:
- `59c07e9 feat(phase0): harden notifications lifecycle schema and locks`
- `1fec3a7 feat(whatsapp phase1): add schema foundation and models`

### 2) Local DB migrations applied through Phase 1 fix

Command:

```powershell
php spark migrate:status
```

Observed:
- `HardenNotificationsPhase0` applied (batch 44)
- `CreateWhatsAppPhase1Tables` applied (batch 45)
- `FixWhatsAppOptoutsPatientForeignKey` applied (batch 46)

### 3) Local DB schema prerequisites exist

Command:

```powershell
docker exec -i mysql8-dental mysql -uroot -proot democa_dental_saas -e "SHOW COLUMNS FROM notifications; SHOW TABLES LIKE 'notification_attempts'; SHOW TABLES LIKE 'whatsapp_delivery_events'; SHOW TABLES LIKE 'patient_whatsapp'; SHOW TABLES LIKE 'whatsapp_optouts'; SHOW TABLES LIKE 'whatsapp_tenant_policies'; SHOW TABLES LIKE 'platform_secrets'; SHOW CREATE TABLE notification_attempts\G"
```

Observed highlights:
- `notifications.status` enum includes:
  - `pending`, `processing`, `blocked`, `sent`, `delivered`, `read`, `failed`, `rejected`
- `notifications.locked_at` and `notifications.locked_by` exist
- `notification_attempts` table exists with expected status enum and indexes
- All Phase 1 foundation tables exist

## Dispatcher Static Verification

### 1) Command registration and help

Commands:

```powershell
php spark list | findstr /I "notifications:dispatch"
php spark notifications:dispatch --help
```

Observed:
- `notifications:dispatch` is registered and callable
- Usage/options rendered correctly (`--clinic-id`, `--channel`, `--limit`, `--dispatcher-id`)

### 2) PHP syntax checks

Command:

```powershell
Get-ChildItem app\Commands\DispatchNotifications.php, app\Jobs\DispatchNotificationsJob.php, app\Services\DispatchService.php, app\Services\NotificationDrivers\*.php | ForEach-Object { php -l $_.FullName }
```

Observed:
- No syntax errors in all dispatcher/driver files

### 3) Correctness fixes (stale lock + exception safety + external recipients)

Static evidence commands:

```powershell
rg -n "status', 'processing'|DISPATCH_RUNTIME_EXCEPTION|DispatchService unexpected failure|recipient_address" app\Services\DispatchService.php app\Services\NotificationDrivers\EmailDriver.php
```

Verified:
- Stale `processing` rows are included in candidate selection and claim predicate
- Unexpected runtime errors are fail-closed and lock-clearing (`DISPATCH_RUNTIME_EXCEPTION`)
- `EmailDriver` resolves `recipient_type=external` via `recipient_address`

## Dispatcher Runtime Verification (Safe + Controlled)

## A) Safe no-op smoke tests (valid clinic context, no pending rows)

Commands:

```powershell
php spark notifications:dispatch --clinic-id 19 --limit 5
php spark tenant:run-job SendEmailNotificationsJob --clinic-id 19
php spark tenant:run-job DispatchNotificationsJob --clinic-id 19
```

Observed:
- All commands executed successfully
- Summaries returned `Processed: 0` for no-op paths
- Legacy `SendEmailNotificationsJob` displayed deprecation message and delegated cleanly

## B) Controlled fixture test for stale lock recovery (local DB only)

### 1) Insert fixture notifications (one `pending`, one stale `processing`)

Command:

```powershell
docker exec -i mysql8-dental mysql -uroot -proot democa_dental_saas -e "INSERT INTO notifications (clinic_id, channel_type, recipient_type, recipient_address, payload_json, status, created_at) VALUES (19, 'whatsapp', 'external', 'dispatch-fixture@example.invalid', JSON_OBJECT('message','fixture pending'), 'pending', NOW()), (19, 'whatsapp', 'external', 'dispatch-fixture@example.invalid', JSON_OBJECT('message','fixture stale processing'), 'processing', DATE_SUB(NOW(), INTERVAL 10 MINUTE)); UPDATE notifications SET locked_at = DATE_SUB(NOW(), INTERVAL 10 MINUTE), locked_by = 'fixture-stale-lock' WHERE clinic_id = 19 AND recipient_address = 'dispatch-fixture@example.invalid' AND status = 'processing'; SELECT id, clinic_id, channel_type, status, locked_at, locked_by, recipient_address, created_at FROM notifications WHERE clinic_id = 19 AND recipient_address = 'dispatch-fixture@example.invalid' ORDER BY id ASC;"
```

Observed:
- Two fixture rows inserted:
  - one `pending`
  - one stale `processing` with old `locked_at` and `locked_by='fixture-stale-lock'`

### 2) Execute dispatcher

Commands:

```powershell
php spark notifications:dispatch --clinic-id 19 --channel whatsapp --limit 10
php spark tenant:run-job DispatchNotificationsJob --clinic-id 19
```

Observed:
- First run processed 2 candidates, claimed 1, skipped 1 (candidate snapshot + claim race behavior)
- Second run processed remaining eligible row
- No crashes, no stuck locks

### 3) Verify final states and cleanup fixtures

Command:

```powershell
docker exec -i mysql8-dental mysql -uroot -proot democa_dental_saas -e "SELECT id, status, failure_reason, locked_at, locked_by FROM notifications WHERE clinic_id = 19 AND recipient_address = 'dispatch-fixture@example.invalid' ORDER BY id ASC; DELETE FROM notifications WHERE clinic_id = 19 AND recipient_address = 'dispatch-fixture@example.invalid'; SELECT COUNT(*) AS remaining_fixture_rows FROM notifications WHERE clinic_id = 19 AND recipient_address = 'dispatch-fixture@example.invalid';"
```

Observed:
- Both fixture rows finalized as:
  - `status = blocked`
  - `failure_reason = PLAN_FEATURE_DISABLED_AT_DISPATCH`
  - `locked_at = NULL`
  - `locked_by = NULL`
- Cleanup succeeded (`remaining_fixture_rows = 0`)

## Guardrail Verification

## A) Windows Git Bash compatibility issue reproduced and fixed

Problem observed before fix:
- `rg` failed with `error parsing flag --path-separator ... C:/Program Files/Git/`
- Script printed pass lines but results were unreliable (false-green risk)

Fix implemented:
- `scripts/ci/saas_guardrails.sh` now uses shell-aware `RG_PATH_SEPARATOR` (`//` on Git Bash/MSYS/Cygwin/Windows shells)

## B) Newly surfaced violations remediated (not allowlisted)

Resolved violations:
1. DOM sinks
- `app/Views/control_plane/users/show.php`
- `app/Views/odontogram/index.php`

2. Raw controller queries
- `app/Controllers/Auth.php`
- `app/Controllers/ControlPlane/Operations.php`
- `app/Controllers/Finance.php`

Remediation pattern:
- Replaced DOM `innerHTML` writes with DOM-safe APIs (`replaceChildren`, `createElement`, `textContent`)
- Moved controller query logic into model methods:
  - `ClinicUserModel::findClinicAdminEmailForClinic`
  - `ClinicUserModel::getActiveUserDirectoryByClinic`
  - `ProviderPayoutModel::getDataTableByClinic`

## C) Final guardrail execution (trustworthy run)

Command (Windows local):

```powershell
"C:\Program Files\Git\bin\bash.exe" scripts/ci/saas_guardrails.sh
```

Observed:
- `DOM sinks in app/Views` passed (0 matches)
- `Group-based auth helpers` passed (0 matches)
- `Raw tenant queries in controllers` passed (0 matches)
- `SaaS guardrail validation succeeded`

## Commit Split (Session Result)

Commands executed:

```powershell
git commit -m "fix(ci): support saas guardrails on Windows Git Bash"
git commit -m "refactor: remove saas guardrail violations"
git commit -m "feat(dispatch): add unified notification dispatcher"
```

Resulting commits:
1. `523e352` `fix(ci): support saas guardrails on Windows Git Bash`
2. `ffc0f66` `refactor: remove saas guardrail violations`
3. `08ae484` `feat(dispatch): add unified notification dispatcher`

## Out of Scope / Next Steps

This verification does not cover:
- WhatsApp outbound transport implementation (`WhatsAppDriver` remains a fail-closed stub)
- Webhook routes/controller/signature verification (`/webhooks/whatsapp`)
- Retry/backoff scheduling and exhaustion auditing end-to-end
- Control-plane / tenant UI for WhatsApp channel config and policies

