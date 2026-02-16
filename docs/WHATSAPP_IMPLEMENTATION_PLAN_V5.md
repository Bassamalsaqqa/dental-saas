# WhatsApp Implementation Plan (v5 - Review Ready)

Date: 2026-02-15
Status: Draft for final architecture review
Scope: Tenant plane + Control plane + delivery/webhook infrastructure

## 1) Executive Summary
This v5 plan supersedes v4 for review. It keeps the same architecture direction and integrates all mandatory additions from the latest audit:
- BSUID support in schema and webhook lookup path
- Explicit retry exhaustion behavior
- Explicit default quota seed values
- Explicit `SendEmailNotificationsJob` deprecation/replacement path
- Provider facts governance file with verification date

The plan remains aligned to current repo constraints and fail-closed SaaS guardrails.

## 2) Codebase-Verified Baseline (Current State)

1. Channel governance exists and already supports `whatsapp` channel type.
- `app/Database/Migrations/2026-01-15-203050_CreateClinicNotificationChannelsTable.php`
- `app/Models/ClinicNotificationChannelModel.php`
- `app/Views/settings/channels.php`

2. Notification ledger has enum drift vs dispatcher behavior.
- Schema status enum: `pending|blocked|failed`
  - `app/Database/Migrations/2026-01-15-203054_CreateNotificationsTable.php`
  - `democa_dental_saas_schema_superadmin.sql`
- Dispatcher writes `status='sent'`
  - `app/Services/NotificationService.php`

3. Email dispatch currently runs as tenant job.
- Job: `app/Jobs/SendEmailNotificationsJob.php`
- Runner: `app/Commands/RunTenantJob.php`

4. Plan gates are JSON-key based in PlanGuard.
- `features_json` / `limits_json` in `plans`
- Feature checks: `app/Services/PlanGuard.php`

5. `/api/v1` cannot host Meta webhooks (requires bearer + `X-Clinic-Id`).
- `app/Filters/ApiV1AuthFilter.php`
- `app/Config/Routes.php`

6. Tenant models are fail-closed by default.
- `app/Models/TenantAwareModel.php`

7. Global settings are possible (`clinic_id = null`) but plaintext values.
- `app/Models/SettingsModel.php`
- Secret fields must use encrypted store (not plain settings values).

8. Control-plane audit service exists and will be reused.
- `app/Services/ControlPlaneAuditService.php`
- `app/Models/ControlPlaneAuditModel.php`

## 3) Final Governance Model

A channel dispatch is allowed only if ALL pass:
1. Plan feature: `features.notifications.<channel>.enabled`
2. Superadmin channel gate: `enabled_by_superadmin = 1`
3. Tenant channel configured: `configured_by_clinic = 1`
4. Tenant channel validated: `validated = 1`
5. Runtime checks: pause/opt-out/policy/quota/retry

Combinations supported:
- Email ON, WhatsApp OFF
- Email OFF, WhatsApp ON
- Both ON
- Both OFF

Control-plane plan and channel gates can block tenant-level enablement.

## 4) Phase 0 (Mandatory Hardening Before WhatsApp)

1. Expand `notifications.status` enum to real lifecycle:
- `pending`, `processing`, `blocked`, `sent`, `delivered`, `read`, `failed`, `rejected`

2. Add lock fields on `notifications`:
- `locked_at DATETIME NULL`
- `locked_by VARCHAR(64) NULL`

3. Update:
- Migration(s)
- `app/Models/NotificationModel.php` allowed fields
- `democa_dental_saas_schema_superadmin.sql`

4. Verification in Phase 0:
- No invalid enum writes during email dispatch
- Existing email dispatch still works after schema change
- `NotificationModel` persists `locked_at` and `locked_by` (allowlist updated and verified)
- Migration uses `MODIFY COLUMN` for enum expansion (safe superset update in MySQL 8)

## 5) New Data Model (WhatsApp + Robust Dispatch)

## 5.1 `notification_attempts`
Purpose: per-attempt transport history + retries

Fields:
- `id`
- `clinic_id`
- `notification_id`
- `channel_type`
- `status` (`queued|sending|sent|delivered|read|failed|rate_limited|failed_permanent`)
- `provider_message_id`
- `error_code`
- `error_message`
- `idempotency_key` (unique)
- `retry_count` (default 0)
- `max_retries` (default 3)
- `next_retry_at` (nullable)
- `created_at`

## 5.2 `whatsapp_delivery_events`
Purpose: idempotent raw webhook event journal

Fields:
- `id`
- `provider_event_id`
- `event_type`
- `payload_json`
- `processed_at`
- unique key `(provider_event_id, event_type)` (required)

## 5.3 `patient_whatsapp`
Purpose: tenant-scoped recipient identity and resolution state

Fields:
- `id`
- `clinic_id`
- `patient_id`
- `wa_number` (nullable)
- `bsuid VARCHAR(128) NULL`  <-- required now
- `resolution_status ENUM('pending','verified','ambiguous','failed')`
- `verified_at`
- `last_error_code`
- `created_at`, `updated_at`

Indexes:
- unique `(clinic_id, patient_id)`
- index `(clinic_id, wa_number)`
- index `(clinic_id, bsuid)`

## 5.4 `whatsapp_optouts`
Purpose: tenant-scoped STOP/opt-out registry

Fields:
- `id`
- `clinic_id`
- `patient_id` (nullable)
- `wa_number` (nullable)
- `bsuid` (nullable)
- `source` (`webhook|manual`)
- `created_at`

Indexes:
- index `(clinic_id, wa_number)`
- index `idx_bsuid (clinic_id, bsuid)` (required)

## 5.5 `whatsapp_tenant_policies`
Purpose: tenant operational controls (separate from channel registry)

Required defaults:
- `daily_limit INT UNSIGNED NOT NULL DEFAULT 50`
- `monthly_limit INT UNSIGNED NOT NULL DEFAULT 1000`
- `per_patient_daily_limit INT UNSIGNED NOT NULL DEFAULT 3`
- `per_hour_limit INT UNSIGNED NOT NULL DEFAULT 20`
- `suspended TINYINT(1) NOT NULL DEFAULT 0`
- `suspended_reason VARCHAR(255) NULL`
- trigger toggles JSON + reminder lead time

## 5.6 `platform_secrets`
Purpose: encrypted platform credentials + rotation trail

Fields:
- `id`
- `secret_key` (`wa_access_token`, `wa_app_secret`, etc.)
- `secret_encrypted`
- `rotated_at`
- `rotated_by`
- `rotation_note`
- `created_at`, `updated_at`

Rule:
- Never store secrets in plain `settings.setting_value`.

## 6) Services and Runtime Architecture

1. Governance + enqueue remains in `NotificationService`.
2. New `DispatchService` handles claim/lock, routing, retries, terminal transitions.
3. Drivers:
- `EmailDriver`
- `WhatsAppDriver`
4. Client:
- `WhatsAppClient` (HTTP calls, response parsing, retryability mapping)
5. Policy/guards:
- `WhatsAppQuotaGuard`
- `WhatsAppPolicy`
- `WhatsAppNumberResolver`

## 7) Dispatch, Retry, and Exhaustion Rules

1. Claim rule (optimistic lock):
- transition `pending -> processing` only if row unlocked or stale lock

2. Retryable failures:
- set `status=rate_limited|failed` on attempt as applicable
- increment `retry_count`
- set `next_retry_at`
- parent notification remains non-terminal while retries remain

3. Exhaustion rule (mandatory):
- if `retry_count >= max_retries`:
  - attempt -> `failed_permanent`
  - parent `notifications.status = 'failed'`
  - set terminal failure reason/context on parent
  - write `ERROR` log with `clinic_id`, `notification_id`, `error_code`
  - emit control-plane audit event for operational visibility

4. Retry guidance:
- 131056 (pair-rate): minimum 60s backoff
- 130429 (throughput): short exponential backoff
- 5xx/network: bounded retries
- permanent validation/template/auth errors: no retry

## 8) Webhook Design (Outside api/v1)

Routes:
- `GET /webhooks/whatsapp` (challenge)
- `POST /webhooks/whatsapp` (events)

Security:
- verify signature using app secret over raw request body
- reject invalid signature with 403

Event handling order:
1. persist raw event idempotently
2. resolve tenant/patient context by priority:
   - `provider_message_id` -> `notification_attempts`
   - `wa_number` -> `patient_whatsapp.wa_number`
   - `bsuid` -> `patient_whatsapp.bsuid`
3. update attempts + parent notification state
4. process STOP/UNSUBSCRIBE into `whatsapp_optouts` using number or BSUID
5. if BSUID appears, persist/update it on `patient_whatsapp`

## 9) Number Resolution Policy

1. Normalize candidates.
2. Try primary prefix first.
3. If ambiguous failure class (including 131026), do not auto-fallback.
4. Mark `resolution_status='ambiguous'` and require explicit staff action to test alternate path.
5. First successful transactional message can be treated as verification.

## 10) Quota and Anti-Abuse Order

Pre-send checks (and re-check right before provider call):
1. Global emergency pause
2. Plan feature gate
3. Channel registry gates
4. Tenant policy suspended?
5. Opt-out exists?
6. Per-patient cap
7. Per-tenant hourly/daily/monthly caps
8. Global rolling window cap

Platform initial defaults (settings, superadmin-editable):
- `wa_global_daily_limit = 200`
- `wa_global_monthly_limit = 5000`
- `wa_abuse_threshold_per_hour = 100`

Rolling windows:
- daily = last 24h (not calendar day)
- monthly = rolling 30d (preferred) or calendar month with explicit note

## 11) Command/Job Transition (Double-Dispatch Prevention)

Current state:
- email dispatch via `SendEmailNotificationsJob`

Target state:
- unified dispatcher command/job uses `DispatchService` for all channels

Migration rule:
1. Introduce new dispatcher (`DispatchNotifications` command/job).
2. Route email path through `EmailDriver` in new dispatcher.
3. Mark `SendEmailNotificationsJob` deprecated.
4. Disable/remove old email-only schedule before enabling new dispatcher.
5. Remove legacy scheduler entry from `app/Config/Scheduler.php` (or equivalent external cron definition) before enabling the new dispatcher schedule.
6. Never run both in parallel.

## 12) Control Plane + Tenant UI Scope

Tenant plane:
- WhatsApp config/validation in channel settings
- patient WhatsApp status badge and manual alternate-prefix action

Control plane:
- WhatsApp ops dashboard (attempts, failures, opt-outs, queue health)
- secret rotation UI (writes `platform_secrets` + `control_plane_audits`)
- emergency pause + global caps

## 13) External Provider Facts Governance

Create and maintain:
- `docs/SaaS/whatsapp_provider_facts.md`

Contents required:
- `Last verified: 2026-02-15` (update on every provider re-verification)
- error codes mapping reviewed
- throughput/pair-limit assumptions reviewed
- tier/portfolio behavior reviewed
- BSUID/username rollout notes reviewed

Rule:
- application code reads configurable settings/policies
- provider-specific assumptions are documented here and periodically re-verified
- if this plan and the facts file conflict, the facts file is authoritative for provider-behavior assumptions

## 14) Files Map (Implementation)

Modify existing:
- `app/Services/NotificationService.php`
- `app/Models/NotificationModel.php`
- `app/Config/Routes.php`
- `app/Controllers/Settings.php`
- `app/Controllers/NotificationLedger.php`
- `app/Views/settings/channels.php`
- `app/Services/PlanGuard.php` (feature key checks only)
- `democa_dental_saas_schema_superadmin.sql`

Add new:
- `app/Services/DispatchService.php`
- `app/Services/NotificationDrivers/EmailDriver.php`
- `app/Services/NotificationDrivers/WhatsAppDriver.php`
- `app/Libraries/WhatsApp/WhatsAppClient.php`
- `app/Libraries/WhatsApp/WhatsAppQuotaGuard.php`
- `app/Libraries/WhatsApp/WhatsAppPolicy.php`
- `app/Libraries/WhatsApp/WhatsAppNumberResolver.php`
- `app/Controllers/Webhooks/WhatsAppWebhook.php`
- `app/Commands/DispatchNotifications.php` (or job equivalent, one unified dispatcher)
- models/migrations for new tables
- `docs/SaaS/whatsapp_provider_facts.md`

## 15) Phase Plan

Phase 0: hardening + enum/lock fix + tests
Phase 1: new schema/models + secrets store + policy tables
Phase 2: unified dispatcher + driver framework + old email job deprecation
Phase 3: WhatsApp outbound MVP
Phase 4: webhook ingestion + delivery state + STOP/opt-out + BSUID persistence
Phase 5: control-plane/tenant UI + observability
Phase 6: pilot rollout with conservative caps and progressive expansion

## 16) Verification Checklist

Repo validation commands:
1. `rg -n "dispatchPendingEmails|status\s*=>\s*'sent'" app/Services/NotificationService.php`
2. `rg -n "SendEmailNotificationsJob" app/Jobs docs/SaaS/verification`
3. `rg -n "X-Clinic-Id|Bearer" app/Filters/ApiV1AuthFilter.php`
4. `php spark routes | findstr /I "webhooks/whatsapp api/v1"`

Functional tests:
1. Email-only clinic dispatches via new unified dispatcher.
2. Plan-disabled WhatsApp blocks enqueue/dispatch with auditable reason.
3. Superadmin-disabled WhatsApp blocks even if tenant configured.
4. STOP webhook blocks next outbound send.
5. Duplicate webhook event does not duplicate state transition.
6. Retry exhaustion produces parent failed state + audit/log.
7. BSUID-only webhook can resolve/store recipient identity path.
