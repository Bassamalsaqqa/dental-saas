# WhatsApp Current State

Last updated: 2026-08-02

Status: Phase 6 pilot rollout and operations hardening; Phases 0-5 are complete.

## Contract

- Product workflows enqueue templates through `NotificationService`; they never call Meta directly.
- `DispatchService` owns email/WhatsApp delivery, locking, retries, attempts, and ledger state.
- Signed Meta webhooks live at `/webhooks/whatsapp`, outside `/api/v1/*`.
- Every dispatch requires plan entitlement, platform gate, tenant configuration, validation, policy/quota allowance, and no opt-out/pause.
- Tenant trigger toggles live in `whatsapp_tenant_policies.trigger_toggles_json`.

## Wired Workflows

- Appointment confirmation on create/update.
- Due appointment reminders.
- Completed-examination follow-up.
- Appointment no-show recovery.
- Invoice payment reminders.

## Operations

```bash
php spark whatsapp:ops-check
php spark whatsapp:queue-product-flows
php spark notifications:dispatch --channel whatsapp
```

Run queueing before dispatch. Current tenant defaults are 50/day, 1000/month, 3/patient/day, and 20/hour; platform limits remain separately enforced.

## Remaining Work

- Production monitoring and alerting.
- Controlled pilot evidence and failure review.
- Broader workflow coverage only through reviewed template-first contracts.

Prior provider research and phase evidence remain available through Git history.
