# WhatsApp Current State

Last updated: 2026-05-02
Current Phase: **Phase 6 — Pilot rollout & operations hardening** (Phases 0-5 completed)

## Architecture Summary

- **Unified dispatcher model** (`DispatchService`) handles all channels (email + WhatsApp)
- **Template-first workflow enqueueing** through `NotificationService` with `message_template_key` / `template_vars`
- **Webhook ingestion** at `GET/POST /webhooks/whatsapp` (outside `/api/v1` — Meta cannot send Bearer + X-Clinic-Id)
- **Signature verification** using app secret over raw request body
- **BSUID support** for Meta's username-based identifier model alongside phone numbers

## Governance Model

A channel dispatch is allowed only if ALL pass:
1. Plan feature: `features.notifications.<channel>.enabled`
2. Superadmin channel gate: `enabled_by_superadmin = 1`
3. Tenant channel configured: `configured_by_clinic = 1`
4. Tenant channel validated: `validated = 1`
5. Runtime checks: pause/opt-out/policy/quota/retry

## Current Implementation (Phases 0-5 completed, Phase 6 partial)

### Implemented
- Notification status enum expansion (`pending|processing|blocked|sent|delivered|read|failed|rejected`)
- Dispatch lock fields (`locked_at`, `locked_by`)
- Tables: `notification_attempts`, `whatsapp_delivery_events`, `patient_whatsapp`, `whatsapp_optouts`, `whatsapp_tenant_policies`, `platform_secrets`
- Outbound transport client: `app/Services/WhatsAppClient.php`
- WhatsApp dispatcher driver: `app/Services/NotificationDrivers/WhatsAppDriver.php`
- Unified dispatcher: `app/Services/DispatchService.php`
- Signed webhook controller: `app/Controllers/Webhooks/WhatsAppWebhook.php`
- Tenant settings readiness/config surfaces
- Patient WhatsApp identity UI
- Control-plane WhatsApp operations surface

### Phase 6 Wired Workflows
Template-first workflow enqueueing is wired for:
- Appointment confirmations on create/update
- Queued due appointment reminders via `whatsapp:queue-product-flows`
- Follow-up messages when examinations transition to `completed`
- No-show recovery messages when appointments marked `no_show`
- Invoice payment reminders on creation and queued due-payment sweeps

### Production Cron Order
1. `php spark whatsapp:queue-product-flows`
2. `php spark notifications:dispatch --channel whatsapp`

### Remaining Phase 6 Work
- Alerting/runbook hardening
- Broader workflow coverage beyond currently wired product events
- Production-grade monitored rollout

## Quota Defaults

Tenant policy (`whatsapp_tenant_policies`):
- `daily_limit = 50`, `monthly_limit = 1000`
- `per_patient_daily_limit = 3`, `per_hour_limit = 20`

Platform defaults (settings, clinic_id null):
- `wa_global_daily_limit = 200`, `wa_global_monthly_limit = 5000`
- `wa_abuse_threshold_per_hour = 100`

## Provider Facts (Confidence-Tiered)

- Pair rate limit (131056): 1 msg/6s, retryable, min 60s backoff — **P1**
- Webhook SHA-256 signature validation using app secret over raw body — **P1**
- Standard throughput ~80 msg/s — **P2**
- 131026 (undeliverable) has multiple causes, do not auto-fallback — **P2**
- BSUID/username identifiers: store user_id, parent_user_id, username from webhooks — **P1**

Provider facts were last verified 2026-03-22. Archived evidence: `docs/_archive/2026-05-02-docs-consolidation/whatsapp-evidence/`

## Key Decisions

- WhatsApp is a commercial plan feature (`notifications.whatsapp.enabled`)
- Tenant workflow toggles in `whatsapp_tenant_policies.trigger_toggles_json`
- Trigger enablement does NOT imply readiness (template mapping required)
- No-show follow-up is a valid toggle; recall/campaign toggles not yet implemented

## DevOps Commands

```bash
php spark whatsapp:ops-check          # Phase 6 readiness monitor
php spark whatsapp:queue-product-flows # Queue product-flow reminders
php spark notifications:dispatch --channel whatsapp  # Dispatch WhatsApp notifications
```
