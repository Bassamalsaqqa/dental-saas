# PHASE0-NOTIFICATIONS-HARDENING

Date: 2026-02-24
Status: PASS (with documented environment limitation for guardrails script)
Scope: Phase 0 notifications schema hardening only (pre-WhatsApp)

## Changes

1. Added migration `app/Database/Migrations/2026-02-24-120000_HardenNotificationsPhase0.php`
   - Expands `notifications.status` enum to:
     - `pending`, `processing`, `blocked`, `sent`, `delivered`, `read`, `failed`, `rejected`
   - Adds nullable lock fields:
     - `locked_at DATETIME NULL`
     - `locked_by VARCHAR(64) NULL`
   - Includes reversible `down()` migration.

2. Updated `app/Models/NotificationModel.php`
   - Added `locked_at`, `locked_by` to `$allowedFields`.

3. Refreshed `democa_dental_saas_schema_superadmin.sql`.

## Pre-Checks (Defect Evidence)

Commands:

```powershell
rg -n "'status' => 'sent'|dispatchPendingEmails" app\Services\NotificationService.php
rg -n "constraint' => \['pending', 'blocked', 'failed'\]" app\Database\Migrations\2026-01-15-203054_CreateNotificationsTable.php
```

Observed evidence:
- `NotificationService::dispatchPendingEmails` writes `status => 'sent'` (`app/Services/NotificationService.php`)
- Original notifications migration enum only allowed `pending|blocked|failed`

## Migration Execution

Command:

```powershell
php spark migrate
```

Result:
- Migration `2026-02-24-120000_HardenNotificationsPhase0` executed successfully.
- `Migrations complete.`

## Schema Verification (DB)

Commands:

```powershell
docker exec -i mysql8-dental mysql -uroot -proot democa_dental_saas -e "SHOW COLUMNS FROM notifications;"
docker exec -i mysql8-dental mysql -uroot -proot democa_dental_saas -e "SHOW CREATE TABLE notifications\G"
```

Pass evidence:
- `status` enum includes:
  - `pending`, `processing`, `blocked`, `sent`, `delivered`, `read`, `failed`, `rejected`
- `locked_at` exists and is nullable
- `locked_by` exists and is nullable

## Email Dispatch Smoke Test

Command run:

```powershell
php spark tenant:run-job SendEmailNotificationsJob --clinic-id 19
```

Result:
- Job completed successfully
- Dispatch summary:
  - Processed: `0`
  - Sent: `0`
  - Failed: `0`
  - Blocked: `0`

Notes:
- No enum write/runtime errors occurred after schema hardening.
- Local DB fixture limitation: no validated email channel rows existed for active clinics (`19`, `21`).
- Historical email channel data and notification rows exist for `clinic_id=1`, but `clinic_id=1` is not present in current `clinics` table in this local DB snapshot.

## Route Sanity

Command:

```powershell
php spark routes | findstr /I "notifications ledger api/v1"
```

Result:
- Notification and ledger routes present
- `/api/v1/*` routes present and unchanged

## Guardrails

Command (Git Bash on Windows):

```powershell
& "C:\Program Files\Git\bin\bash.exe" scripts/ci/saas_guardrails.sh
```

Result:
- Script completed with `SaaS guardrail validation succeeded.`
- Git Bash emitted repeated `rg --path-separator` parsing warnings caused by Windows shell path expansion behavior, but the script still reported all guardrails as passed.

Observed pass lines:
- `Guardrail 'DOM sinks in app/Views' passed`
- `Guardrail 'Group-based auth helpers' passed`
- `Guardrail 'Raw tenant queries in controllers' passed`

## SQL Dump Refresh Verification

Dump command:

```powershell
cmd /c "docker exec mysql8-dental mysqldump -u root -proot --default-character-set=utf8mb4 --routines --triggers --events --single-transaction --skip-lock-tables --set-charset --no-create-db democa_dental_saas --ignore-table=democa_dental_saas.control_plane_invoices --ignore-table=democa_dental_saas.control_plane_invoice_payments > democa_dental_saas_schema_superadmin.sql"
```

Verification commands:

```powershell
rg -n 'CREATE TABLE `notifications`' democa_dental_saas_schema_superadmin.sql
rg -n 'status.*pending.*processing.*blocked.*sent.*delivered.*read.*failed.*rejected' democa_dental_saas_schema_superadmin.sql
rg -n 'locked_at|locked_by' democa_dental_saas_schema_superadmin.sql
```

Observed lines:
- `CREATE TABLE notifications` at line `780`
- Expanded `status` enum at line `788`
- `locked_at` / `locked_by` at lines `790` / `791`

## Final Git Status (Relevant)

Phase 0 changes:
- `app/Database/Migrations/2026-02-24-120000_HardenNotificationsPhase0.php` (new)
- `app/Models/NotificationModel.php` (modified)
- `democa_dental_saas_schema_superadmin.sql` (modified)

Unrelated pre-existing local change:
- `public/assets/css/tailwind.css` (modified before Phase 0 work)
