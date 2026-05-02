# Phase 0 Execution Checklist - Notifications Hardening

Date: 2026-02-15
Owner: Engineering
Status: Ready to execute
Related docs:
- `docs/WHATSAPP_IMPLEMENTATION_PLAN_V5.md`
- `docs/SaaS/whatsapp_provider_facts.md`
- `docs/SaaS/SAAS_PROGRAM.md`

## 1) Scope (Phase 0 Only)
This checklist covers only pre-WhatsApp hardening work required before any new channel transport is implemented.

In scope:
1. Fix notification status enum drift.
2. Add notification dispatch lock fields.
3. Keep existing email dispatch working.
4. Update SQL dump and capture evidence.

Out of scope:
1. WhatsApp API client implementation.
2. Webhook routes/controllers.
3. New WhatsApp tables (`notification_attempts`, `patient_whatsapp`, etc.).

## 2) Current Defect to Fix (Verified)
Current code writes `status='sent'` in `NotificationService`, but notifications schema only allows `pending|blocked|failed`.

Evidence commands:
```powershell
rg -n "'status' => 'sent'|dispatchPendingEmails" app\Services\NotificationService.php
rg -n "constraint' => \['pending', 'blocked', 'failed'\]" app\Database\Migrations\2026-01-15-203054_CreateNotificationsTable.php
```

Expected: both commands return matches.

## 3) Preconditions
1. Repo root is `C:\xampp\htdocs\dental-saas`.
2. DB container `mysql8-dental` is running.
3. You can run migrations in local environment.
4. Working tree review completed (`git status -sb`).

Commands:
```powershell
git status -sb
docker ps --format "table {{.Names}}\t{{.Status}}" | findstr /I "mysql8-dental"
```

## 4) File Change Plan
Modify/create only these for Phase 0:
1. New migration file under `app/Database/Migrations/` to alter `notifications`:
- Expand enum to include: `pending`, `processing`, `blocked`, `sent`, `delivered`, `read`, `failed`, `rejected`
- Add columns:
  - `locked_at DATETIME NULL`
  - `locked_by VARCHAR(64) NULL`

2. Update `app/Models/NotificationModel.php`:
- Add `locked_at`, `locked_by` to `$allowedFields`.

3. Keep `app/Services/NotificationService.php` behavior intact for email dispatch in this phase.

4. Regenerate dump:
- `democa_dental_saas_schema_superadmin.sql`

## 5) Implementation Steps

## Step A: Create migration
Create a migration similar to:
- `YYYY-MM-DD-HHMMSS_HardenNotificationsPhase0.php`

Migration requirements:
1. Alter `notifications.status` enum with full lifecycle list.
2. Add `locked_at` and `locked_by` columns.
3. Down migration restores previous schema exactly:
- status enum back to `pending|blocked|failed`
- remove `locked_at`, `locked_by`

## Step B: Update model allowlist
File: `app/Models/NotificationModel.php`
1. Add `locked_at` and `locked_by` to `$allowedFields`.
2. Do not change tenant scoping behavior.

## Step C: Run migration
```powershell
php spark migrate
```

Expected:
- Migration completes successfully.
- No SQL errors.

## Step D: Schema verification
Verify table structure after migration:
```powershell
docker exec -i mysql8-dental mysql -uroot -proot democa_dental_saas -e "SHOW COLUMNS FROM notifications;"
docker exec -i mysql8-dental mysql -uroot -proot democa_dental_saas -e "SHOW CREATE TABLE notifications\G"
```

Pass conditions:
1. `status` enum includes full lifecycle values.
2. `locked_at` column exists and is nullable.
3. `locked_by` column exists and is nullable.

## Step E: Email dispatch smoke test
Select a clinic that is known to have email channel configured and validated.
Do not assume `clinic_id = 1` in every environment.

Discovery query:
```powershell
docker exec -i mysql8-dental mysql -uroot -proot democa_dental_saas -e "SELECT id, name FROM clinics ORDER BY id ASC LIMIT 10;"
```

Run existing tenant email dispatch job:
```powershell
php spark tenant:run-job SendEmailNotificationsJob --clinic-id <CLINIC_ID_WITH_EMAIL_CONFIG>
```

Pass conditions:
1. Job runs without fatal enum write errors.
2. Command prints dispatch summary.
3. Notifications can still transition to `sent`/`failed`/`blocked` as expected.

Optional ledger check:
```powershell
docker exec -i mysql8-dental mysql -uroot -proot democa_dental_saas -e "SELECT id,clinic_id,channel_type,status,failure_reason,created_at FROM notifications ORDER BY id DESC LIMIT 20;"
```

## Step F: Guardrails and route sanity
```powershell
bash scripts/ci/saas_guardrails.sh
php spark routes | findstr /I "notifications ledger api/v1"
```

Pass conditions:
1. Guardrails script passes.
2. No unexpected route breakage.

## Step G: Refresh SQL dump (required)
Before running the dump command, confirm exclusion intent for:
- `control_plane_invoices`
- `control_plane_invoice_payments`

These exclusions are historical and should remain only if they intentionally protect live billing data from version-controlled dumps.

Run from repo root exactly:
```powershell
cmd /c "docker exec mysql8-dental mysqldump -u root -proot --default-character-set=utf8mb4 --routines --triggers --events --single-transaction --skip-lock-tables --set-charset --no-create-db democa_dental_saas --ignore-table=democa_dental_saas.control_plane_invoices --ignore-table=democa_dental_saas.control_plane_invoice_payments > democa_dental_saas_schema_superadmin.sql"
```

Pass conditions:
1. Dump file updated.
2. Dump contains updated `notifications` table definition.

Check:
```powershell
rg -n "CREATE TABLE `notifications`|`status` enum|`locked_at`|`locked_by`" democa_dental_saas_schema_superadmin.sql
```

## 6) Evidence Package (Required for Audit)
Record and attach outputs for:
1. Pre-check evidence commands from Section 2.
2. Migration execution output.
3. `SHOW CREATE TABLE notifications` output.
4. Email dispatch smoke output.
5. Guardrails output.
6. Dump verification `rg` output.
7. Final `git status -sb`.

Store evidence under:
- `docs/SaaS/verification/PHASE0-NOTIFICATIONS-HARDENING.md`

## 7) Rollback Plan
If migration causes regression:
1. Run rollback:
```powershell
php spark migrate:rollback
```

2. Re-verify `notifications` schema is restored.
3. Re-run email dispatch smoke test.
4. Important: `migrate:rollback` is schema-only. If data regression occurred, restore DB from known local dump/backup.
5. If rollback or restore fails, stop execution and document incident with evidence.

## 8) Exit Criteria (Phase 0 Complete)
Phase 0 is complete only when all are true:
1. Enum drift is fixed (`sent` is valid in schema).
2. Lock columns exist in schema and model allowlist.
3. Existing email dispatch job still runs successfully.
4. Guardrails pass.
5. SQL dump updated and committed.
6. Verification report with evidence is added.

## 9) Commit Guidance
Suggested commit sequence:
1. `feat(phase0): harden notifications schema for dispatch lifecycle`
2. `chore(db): refresh superadmin schema dump after phase0 hardening`
3. `docs(verification): add phase0 notifications hardening evidence`

Do not combine Phase 0 with Phase 1+ WhatsApp implementation commits.
