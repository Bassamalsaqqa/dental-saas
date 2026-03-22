# WhatsApp Provider Facts and Implementation Context (Audit-Grade)

Last updated: 2026-03-22
Owner: Engineering
Audience: Implementation team, security reviewers, architecture auditors
Status: Canonical provider/context registry for WhatsApp feature work
Supersedes ad-hoc notes in: `docs/PLAN_AUDIT_V4_FINAL.md`
Related implementation plan: `docs/WHATSAPP_IMPLEMENTATION_PLAN_V5.md`

## 1) Purpose
This file removes ambiguity before implementation by centralizing:
1. Current codebase status (what exists vs what does not)
2. Provider facts with confidence level and source type
3. Locked implementation decisions derived from facts
4. Verification commands and expected evidence
5. File map and stack context for end-to-end auditability

This file is intentionally explicit. If this file and a transient chat comment disagree, this file wins.

## 2) Source Quality Model
Every provider fact is labeled:
- `P1-Primary`: official provider documentation directly accessible during verification.
- `P2-Secondary`: reputable partner/BSP documentation or indirect citation of official docs.
- `P3-Pending`: unverified claim requiring future confirmation from official provider docs/changelog.

Engineering rule:
- Only `P1` and stable `P2` facts may shape default behavior.
- `P3` facts are tracked, not hardcoded.

## 3) Tech Stack and Runtime Context (Repo-Verified)

Backend:
- PHP `^8.1` from `composer.json`
- CodeIgniter 4 project skeleton (`codeigniter4/framework`) from `composer.json`
- DB driver default `MySQLi` from `app/Config/Database.php`

Frontend/tooling:
- Tailwind CSS build pipeline from `package.json`
- PostCSS/autoprefixer from `package.json`

Security/filter posture:
- Global HTTPS force is enabled (`$forceGlobalSecureRequests = true`) in `app/Config/App.php`
- API v1 uses custom `apiv1` auth filter in `app/Config/Filters.php`

SaaS governance baseline:
- Program source-of-truth: `docs/SaaS/SAAS_PROGRAM.md`
- Tenant fail-closed model active through `TenantAwareModel`

## 4) Current WhatsApp/Notification Codebase Status (Repo-Verified)

## 4.1 What already exists
1. Channel registry supports `email|sms|whatsapp`:
- `app/Database/Migrations/2026-01-15-203050_CreateClinicNotificationChannelsTable.php`
- `app/Models/ClinicNotificationChannelModel.php`

2. Per-clinic encrypted channel config exists (`config_encrypted`):
- `app/Models/ClinicNotificationChannelModel.php`

3. Notification ledger table exists:
- `app/Database/Migrations/2026-01-15-203054_CreateNotificationsTable.php`
- `democa_dental_saas_schema_superadmin.sql`

4. Email dispatch exists:
- Service: `app/Services/NotificationService.php`
- Tenant job: `app/Jobs/SendEmailNotificationsJob.php`
- Job runner: `app/Commands/RunTenantJob.php`

5. Plan checks are JSON-key based:
- `app/Services/PlanGuard.php`
- `plans.features_json` and `plans.limits_json` in migration/schema

6. Control plane audit pipeline exists:
- `app/Services/ControlPlaneAuditService.php`
- `app/Models/ControlPlaneAuditModel.php`
- `app/Database/Migrations/2026-01-16-220000_CreateControlPlaneAudits.php`

## 4.2 Current implemented WhatsApp foundation (repo-verified as of 2026-03-20)
The following entities now exist in code and migrations:
- `notification_attempts`
- `whatsapp_delivery_events`
- `patient_whatsapp`
- `whatsapp_optouts`
- `whatsapp_tenant_policies`
- `platform_secrets`

The following routes/commands now exist:
- `GET /webhooks/whatsapp`
- `POST /webhooks/whatsapp`
- `notifications:dispatch`
- `whatsapp:ops-check`
- `whatsapp:seed-local-test`

The following implementation surfaces now exist:
- outbound transport client: `app/Services/WhatsAppClient.php`
- WhatsApp dispatcher driver: `app/Services/NotificationDrivers/WhatsAppDriver.php`
- unified dispatcher: `app/Services/DispatchService.php`
- signed webhook controller: `app/Controllers/Webhooks/WhatsAppWebhook.php`
- tenant settings readiness/config surfaces
- patient WhatsApp identity UI
- control-plane WhatsApp operations surface

## 4.3 Remaining gaps (still not fully implemented)
- template-first business workflow wiring now exists for appointment confirmations, queued appointment reminders, completed-exam follow-up, no-show follow-up, and invoice payment reminders, but broader coverage across recalls/campaign-style workflows is still incomplete
- Phase 6 alerting/runbook automation is still lighter than a production-grade monitored rollout
- planned explicit helper classes were not split out as separate files:
  - `WhatsAppQuotaGuard`
  - `WhatsAppPolicy`
  - `WhatsAppNumberResolver`

## 4.3 Known schema drift bug (must fix first)
- `notifications.status` enum currently: `pending|blocked|failed`
  - `app/Database/Migrations/2026-01-15-203054_CreateNotificationsTable.php`
- Dispatcher writes `status='sent'`
  - `app/Services/NotificationService.php`

This is a pre-existing correctness defect and blocks safe channel expansion.

## 5) Routing and Auth Constraints (Repo-Verified)

1. `/api/v1/*` is behind `apiv1` filter:
- `app/Config/Routes.php`
- `app/Config/Filters.php`

2. `ApiV1AuthFilter` requires both:
- Bearer auth header
- `X-Clinic-Id` header
- `app/Filters/ApiV1AuthFilter.php`

Implication:
- Meta webhook callbacks cannot be placed inside `/api/v1/*`.
- Required design: dedicated route group e.g. `GET/POST /webhooks/whatsapp` outside `apiv1`.

## 6) Provider Facts Registry (with confidence)

## 6.1 Security and webhook validation

Fact WF-001
- Statement: Meta webhooks include SHA-256 signature in `X-Hub-Signature-256` and validation uses app secret HMAC over raw payload.
- Confidence: `P1-Primary` (upgraded from P2 on 2026-02-15)
- Rationale: Meta Community Forums include official Graph Webhooks signature language and field naming aligned with implementation guidance.
- Source:
  - Meta Community Forums (official Graph Webhooks quote): https://communityforums.atmeta.com/discussions/dev-general/how-to-verify-a-webhook-request-sign/1171086
  - Supporting reference: https://stackoverflow.com/questions/36620841/what-is-the-best-practice-to-secure-your-facebook-chatbot-webhook
- Engineering decision: Treat as required. Implement signature verification with raw body and app secret. Keep kill switch to temporarily bypass only in non-production diagnostics.

Fact WF-002
- Statement: Payload must be validated against raw body bytes (not parsed/reformatted JSON).
- Confidence: `P1-Primary` (upgraded from P2 on 2026-02-15)
- Source:
  - Same as WF-001 and standard HMAC webhook guidance.
- Engineering decision: In CI4 webhook controller, compute hash using `$this->request->getBody()` before JSON decoding.

## 6.2 Rate limits and throttling

Fact RL-001
- Statement: Pair rate limit for same recipient is 1 message per 6 seconds; error code `131056` indicates pair limit exceeded.
- Confidence: `P1-Primary`
- Source:
  - Meta docs mirror page: https://meta-preview.mintlify.io/docs/whatsapp/cloud-api/overview
- Engineering decision:
  - Classify 131056 as retryable
  - Minimum retry backoff for 131056: 60 seconds

Fact RL-002
- Statement: Standard throughput reference around 80 messages/sec appears in provider docs and partner implementations.
- Confidence: `P2-Secondary`
- Sources:
  - Postman/partner materials observed in audits
  - Meta preview references throughput constraints contextually
- Engineering decision: keep configurable, do not hardcode fixed global throughput assumptions.

## 6.3 Error semantics

Fact ER-001
- Statement: `131026` (message undeliverable) has multiple possible causes (not only invalid/non-WhatsApp number).
- Confidence: `P2-Secondary`
- Sources:
  - Wati: https://support.wati.io/en/articles/11463474-resolving-message-undeliverable-when-sending-template-messages
  - Qiscus: https://support.qiscus.com/hc/en-us/articles/1741669014390-Why-I-Got-This-Error-131026-Message-Undeliverable
- Engineering decision:
  - Do not auto-fallback prefix based on 131026 alone
  - Mark resolution ambiguous and require explicit staff action

## 6.4 Messaging limit model

Fact ML-001
- Statement: New/unverified portfolios start with low daily unique conversation limits (commonly cited as 250 / rolling 24h).
- Confidence: `P2-Secondary`
- Sources:
  - Multiple BSP docs (Wati, Infobip, others)
- Engineering decision:
  - Initialize conservative platform cap at 200/day (configurable)
  - Use rolling 24h queries in quota guard

Fact ML-002
- Statement: Portfolio-level behavior (shared limit across numbers) is widely reported post-2025 updates.
- Confidence: `P2-Secondary`
- Source examples:
  - Wati update note: https://support.wati.io/en/articles/11463211-how-to-increase-your-whatsapp-messaging-limit-without-meta-business-verification
- Engineering decision:
  - Keep global cap independent of per-tenant caps
  - Avoid hardcoding tier ladder logic; model as configurable constraints

Fact ML-003
- Statement: "2K/10K removed and instant 100K" claims are circulating for 2026.
- Confidence: `P3-Pending`
- Source pattern: partner blogs/news posts, no directly verifiable primary source captured here.
- Engineering decision:
  - Do not encode this as deterministic business logic
  - Track in this registry until official provider release note is available

## 6.5 Username / BSUID model

Fact ID-001
- Statement: 2026 username model introduces BSUID-style identifier in business integrations.
- Confidence: `P1-Primary`
- Sources:
  - Meta Business Messaging docs: https://developers.facebook.com/documentation/business-messaging/whatsapp/business-scoped-user-ids
- Engineering decision:
  - Add `bsuid` field from v1 in `patient_whatsapp`
  - Support webhook resolution path by `provider_message_id` then `wa_number` then `bsuid`
  - Store BSUID whenever present
  - Support `parent_user_id`/parent BSUID storage alongside BSUID
  - Treat BSUID/parent BSUID as dedicated identifiers, not phone numbers
  - Outbound requests must use Meta's dedicated `recipient` field for BSUID-style identifiers

Fact ID-002
- Statement: Meta webhooks can now include `user_id`, `parent_user_id`, and `username`, and `wa_id` may be absent when username mode is active.
- Confidence: `P1-Primary`
- Sources:
  - Meta Business Messaging docs: https://developers.facebook.com/documentation/business-messaging/whatsapp/business-scoped-user-ids
- Engineering decision:
  - Webhook ingestion must persist `user_id`, `parent_user_id`, and `username` when available
  - Lookup/update flows must not assume `wa_id` is always present
  - Add support for `user_id_update` webhooks to keep stored identifiers current

Fact ID-003
- Statement: exact rollout milestones/countries and final deadlines can change.
- Confidence: `P3-Pending`
- Engineering decision:
  - Keep date/country assumptions out of code
  - Maintain readiness now by supporting both identifiers

## 7) Locked Implementation Decisions (Derived)

These decisions are fixed for implementation unless superseded by explicit design review:

1. Phase 0 hardening is mandatory before WhatsApp (status enum + lock columns).
2. Unified dispatcher model replaces parallel email-only dispatch schedules.
3. `SendEmailNotificationsJob` must be deprecated once unified dispatcher is enabled.
4. Webhook route must be outside `/api/v1`.
5. Webhook signature check uses app secret and raw request body.
6. Retry model must include exhaustion terminal behavior.
7. 131026 is treated as ambiguous for number resolution.
8. BSUID support is included from initial schema version.
9. Secrets are stored encrypted in `platform_secrets`, not plaintext settings.
10. Platform and tenant quotas are both enforced (global + per-tenant + per-patient).

## 8) Required Defaults and Config Keys (No Guessing)

Tenant policy defaults (`whatsapp_tenant_policies`):
- `daily_limit = 50`
- `monthly_limit = 1000`
- `per_patient_daily_limit = 3`
- `per_hour_limit = 20`
- `suspended = 0`

Platform defaults (`settings`, clinic_id null, non-secret):
- `wa_global_daily_limit = 200`
- `wa_global_monthly_limit = 5000`
- `wa_abuse_threshold_per_hour = 100`
- `wa_rolling_window_hours = 24`

Secret keys (`platform_secrets`, encrypted):
- `wa_access_token`
- `wa_app_secret`
- `wa_phone_number_id`
- `wa_business_account_id`
- `wa_verify_token` (required for webhook registration challenge; value can be random)

## 9) File Structure Map (Feature-Relevant)

Authoritative existing files:
- `app/Services/NotificationService.php`
- `app/Jobs/SendEmailNotificationsJob.php`
- `app/Commands/RunTenantJob.php`
- `app/Models/NotificationModel.php`
- `app/Models/ClinicNotificationChannelModel.php`
- `app/Models/SettingsModel.php`
- `app/Services/PlanGuard.php`
- `app/Filters/ApiV1AuthFilter.php`
- `app/Config/Routes.php`
- `app/Services/ControlPlaneAuditService.php`
- `app/Database/Migrations/2026-01-15-203054_CreateNotificationsTable.php`
- `app/Database/Migrations/2026-01-15-203050_CreateClinicNotificationChannelsTable.php`
- `app/Database/Migrations/2026-01-16-220000_CreateControlPlaneAudits.php`
- `democa_dental_saas_schema_superadmin.sql`

Planned new files (from v5 plan):
- `app/Services/DispatchService.php`
- `app/Services/NotificationDrivers/EmailDriver.php`
- `app/Services/NotificationDrivers/WhatsAppDriver.php`
- `app/Libraries/WhatsApp/WhatsAppClient.php`
- `app/Libraries/WhatsApp/WhatsAppQuotaGuard.php`
- `app/Libraries/WhatsApp/WhatsAppPolicy.php`
- `app/Libraries/WhatsApp/WhatsAppNumberResolver.php`
- `app/Controllers/Webhooks/WhatsAppWebhook.php`
- `app/Commands/DispatchNotifications.php`
- models + migrations for all new WhatsApp tables

## 10) Verification Procedure (Repo + Provider)

## 10.1 Repo verification commands
1. Confirm enum drift:
```powershell
rg -n "constraint' => \['pending', 'blocked', 'failed'\]" app\Database\Migrations\2026-01-15-203054_CreateNotificationsTable.php
rg -n "'status' => 'sent'" app\Services\NotificationService.php
```

2. Confirm API v1 webhook incompatibility:
```powershell
rg -n "X-Clinic-Id|Bearer" app\Filters\ApiV1AuthFilter.php
rg -n "group\('v1'.*apiv1" app\Config\Routes.php
```

3. Confirm existing email dispatch job:
```powershell
rg -n "class SendEmailNotificationsJob|dispatchPendingEmails" app\Jobs\SendEmailNotificationsJob.php app\Services\NotificationService.php
```

4. Confirm encryption pattern for channel config:
```powershell
rg -n "config_encrypted|encrypter->encrypt|encrypter->decrypt" app\Models\ClinicNotificationChannelModel.php
```

## 10.2 Provider re-verification checklist (manual)
1. Re-check official WhatsApp docs for:
- webhook signature header/algorithm details
- error code behavior updates
- throttling/throughput notes
- messaging limit/tier policy changes

2. Update this file:
- bump `Last updated`
- add a dated section under `Appendix A: Fact Revision Log`
- move impacted facts between `P1/P2/P3`

## 11) Current Implementation State (as of this document)
This file now serves two purposes:
- provider/context registry for stable design facts
- implementation-state checkpoint for reviewers returning to the codebase later

Verified implementation status at this revision:
- WhatsApp implementation exists through Phase 5
- Phase 6 has partial rollout hardening:
  - ops-readiness command
  - live pilot evidence
  - template-aware settings/runtime fallback support
  - template-first workflow enqueue service + queue command for appointment/payment reminder flows
  - appointment/examination/finance product hooks enqueue template-aware ledger notifications
- tenant-level per-flow enable/disable toggles stored in `whatsapp_tenant_policies.trigger_toggles_json`
- production reminder automation requires host cron in this order:
  1. `php spark whatsapp:queue-product-flows`
  2. `php spark notifications:dispatch --channel whatsapp`

Historical note:
- Earlier revisions of this file were written before implementation started.
- Any statement in older revisions implying that transport/webhooks were not yet implemented is superseded by the current codebase state and verification docs under `docs/SaaS/verification/`.

## 12) What Is In Scope vs Out of Scope

In scope for current planning phase:
- architecture, schema design, policy defaults, route/security model, migration path

Out of scope until fully productized:
- production Meta credential provisioning
- tenant onboarding content/legal template approval
- fully automated template-first workflow coverage across all business events
- external alerting/integration beyond the local ops-readiness command and dashboard views

## 13) Risk Register (Condensed)

R1: Provider policy churn
- Mitigation: keep dynamic values in settings; keep this facts file updated

R2: Double-dispatch during migration
- Mitigation: explicit old job deprecation gate before new scheduler enablement

R3: Identifier mismatch after username rollout
- Mitigation: implement BSUID + number dual-path from day one

R4: Quota overrun on shared portfolio
- Mitigation: enforce global cap + tenant caps + per-patient caps + emergency pause

R5: Secret leakage
- Mitigation: encrypted secret store + rotation audit events

## 14) Mandatory Pre-Implementation Checklist
Before extending WhatsApp further, reviewers must confirm:
1. Current implementation state is reviewed against:
   - `docs/SaaS/verification/WHATSAPP-CHECKPOINT-2026-02-26.md`
   - `docs/SaaS/verification/WHATSAPP-PHASE5-UI-OPS.md`
   - `docs/SaaS/verification/WHATSAPP-PHASE6-OPS-READINESS-2026-03-01.md`
   - `docs/SaaS/verification/WHATSAPP-PHASE6-LIVE-PILOT-2026-03-01.md`
   - `docs/SaaS/verification/WHATSAPP-TEMPLATES-SETTINGS-2026-03-01.md`
2. Provider facts in this file are still acceptable for current rollout decisions.
3. Security review still accepts webhook signature model and secret handling.
4. Product review accepts template-first delivery for out-of-session messaging.
5. Workflow wiring changes preserve fail-closed tenant isolation and auditable notification ledger behavior.
6. The latest workflow verification doc is reviewed:
   - `docs/SaaS/verification/WHATSAPP-PHASE6-WORKFLOWS-2026-03-20.md`

## Appendix A: Fact Revision Log
- 2026-02-15: Initial version created. Consolidated codebase status + provider fact confidence model.
- 2026-02-15 (revision): Upgraded WF-001 and WF-002 from `P2` to `P1`; clarified `wa_verify_token` as required for webhook registration challenge.
- 2026-03-20: Updated implementation-state sections to reflect the repo after Phases 1-5 and partial Phase 6 rollout hardening.
- 2026-03-20 (revision): Added workflow wiring status for appointment, follow-up, and finance reminders; aligned readiness endpoint expectations and verification references.

## Appendix B: External Sources Referenced
Primary/official-ish accessible in this environment:
- Meta docs mirror (pair rate limit and 131056 mention):
  - https://meta-preview.mintlify.io/docs/whatsapp/cloud-api/overview
- Meta Community Forums (official Graph Webhooks signature quote):
  - https://communityforums.atmeta.com/discussions/dev-general/how-to-verify-a-webhook-request-sign/1171086

Secondary sources used for non-primary claims:
- Supporting webhook signature citation:
  - https://stackoverflow.com/questions/36620841/what-is-the-best-practice-to-secure-your-facebook-chatbot-webhook
- 131026 multi-cause examples:
  - https://support.wati.io/en/articles/11463474-resolving-message-undeliverable-when-sending-template-messages
  - https://support.qiscus.com/hc/en-us/articles/1741669014390-Why-I-Got-This-Error-131026-Message-Undeliverable
- BSUID/username rollout context:
  - https://www.twilio.com/docs/whatsapp/key-concepts

Note:
- Any claim without accessible primary source is deliberately marked `P2` or `P3` and not treated as immutable logic.
