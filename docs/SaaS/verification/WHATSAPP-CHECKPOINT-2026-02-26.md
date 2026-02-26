# WhatsApp Implementation Checkpoint (2026-02-26)

## Purpose

Checkpoint snapshot before shifting focus to non-WhatsApp feature improvements. This document records the current implementation state, verification evidence, and the exact resume point for the WhatsApp plan.

## Status Summary

- WhatsApp implementation is functionally complete through **Phase 5 (UI/Ops)**.
- Core outbound send + signed webhooks are implemented and previously validated live (Cloudflare Tunnel + real device).
- Automated test coverage now includes:
  - webhook challenge/signature/idempotency
  - webhook status transitions and STOP opt-out behavior
  - `WhatsAppClient` response/error classification (`131056`, `131026`, 5xx)
  - `DispatchService` stale lock reclaim and exception-safe finalization

## Completed Phases (effective implementation status)

- Phase 0: Notifications hardening
- Phase 1: WhatsApp schema/model foundation
- Phase 2: Unified dispatcher integration
- Phase 3: Outbound `WhatsAppClient` + `WhatsAppDriver` transport mapping (including template support)
- Phase 4: Signed webhook endpoints (`GET/POST /webhooks/whatsapp`)
- Phase 5: Tenant + Control Plane UI/Ops surfaces

## Recent Commit Stack (latest WhatsApp-related progress)

- `6149fd8` `test(whatsapp): cover webhook signature and idempotency`
- `599ded1` `test(whatsapp): cover webhook status promotion mapping`
- `eb0dbb4` `test(whatsapp): add webhook endpoint coverage`
- `65f035d` `chore(test): add composer lockfile and ignore build artifacts`
- `c1fe2ca` `test(whatsapp): verify webhook stop and status callbacks`
- `13455cb` `test(whatsapp): cover failed and duplicate stop webhooks`
- `316a788` `test(whatsapp): cover client and dispatcher failures`

## Verification (latest checkpoint)

### WhatsApp + Dispatcher Test Bundle

Command:

```bat
vendor\bin\phpunit.bat --no-coverage tests\unit\WhatsAppWebhookTest.php tests\unit\WhatsAppClientTest.php tests\unit\DispatchServiceTest.php
```

Result:

- `OK (24 tests, 121 assertions)`

### Notes

- `phpunit.xml.dist` has `failOnWarning=true`; running without `--no-coverage` may fail if no coverage driver (Xdebug/PCOV) is installed.
- `app/Config/Database.php` now defines a `tests` DB group so CIUnitTestCase can run in `ENVIRONMENT=testing`.

## What Is Implemented (high confidence)

- Meta webhook challenge (`wa_verify_token`)
- Raw-body HMAC verification (`wa_app_secret`, `X-Hub-Signature-256`)
- Idempotent event journaling into `whatsapp_delivery_events`
- Webhook-driven status updates for `notification_attempts` and parent `notifications`
- STOP/UNSUBSCRIBE opt-out creation into `whatsapp_optouts`
- Outbound WhatsApp send path via `WhatsAppClient`
- Provider error classification:
  - `131056` retryable, minimum 60s backoff
  - `131026` terminal ambiguous (no auto-retry)
- Template send support in `WhatsAppClient`/`WhatsAppDriver`
- Unified dispatcher stale-lock reclaim and exception-safe finalization
- Tenant settings, patient identity UI, and control-plane WhatsApp ops UI

## Remaining Work in WhatsApp Plan (resume later)

### Phase 6 / Rollout & Hardening (primary remaining work)

1. Pilot rollout execution and operational monitoring
2. Template-first product enqueue flows (business workflows using `template_name`)
3. Alerting/monitoring automation (beyond dashboards)
4. Additional endpoint tests for Phase 5 actions:
   - `Settings::testWhatsAppChannel`
   - patient WhatsApp identity save endpoint
   - control-plane secret rotation + emergency pause/policy actions
5. Documentation/AGENTS phase-state cleanup (some files still mention "Phase 0" as active)

## Resume Checklist (next session)

1. Re-run current WhatsApp regression bundle:
   - `vendor\bin\phpunit.bat --no-coverage tests\unit\WhatsAppWebhookTest.php tests\unit\WhatsAppClientTest.php tests\unit\DispatchServiceTest.php`
2. Choose next WhatsApp slice:
   - Phase 5 endpoint tests
   - Phase 6 pilot rollout/ops runbook execution
   - Template-first enqueue UX/workflow integration
3. Update `docs/SaaS/logs/IMPLEMENTATION_LOG_SaaS.md` with new work before/after changes

## Context Shift Note

The user is intentionally shifting focus to non-WhatsApp feature improvements. This checkpoint is intended to preserve exact WhatsApp progress and reduce reacquisition time when returning to the WhatsApp plan.
