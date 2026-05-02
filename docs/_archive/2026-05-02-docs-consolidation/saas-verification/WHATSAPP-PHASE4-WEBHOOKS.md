# WHATSAPP-PHASE4-WEBHOOKS

Date: 2026-02-24
Status: PASS
Scope: Phase 4 WhatsApp webhooks (public routes, challenge endpoint, raw-body HMAC signature verification, idempotent event journaling, webhook-driven status transitions, STOP opt-out ingestion)

## Summary

Implemented Phase 4 WhatsApp webhook infrastructure:
- Public webhook routes at `/webhooks/whatsapp` (outside `/api/v1`)
- `GET` challenge verification using `wa_verify_token`
- `POST` events endpoint with raw-body HMAC verification using `wa_app_secret`
- Idempotent journaling to `whatsapp_delivery_events`
- Webhook status event processing to update `notification_attempts` and parent `notifications`
- Inbound STOP/UNSUBSCRIBE processing to `whatsapp_optouts`
- CSRF exclusions for `webhooks/*` to allow Meta callbacks

## Files Added / Updated

- `app/Controllers/Webhooks/WhatsAppWebhook.php`
- `app/Config/Routes.php`
- `app/Config/Filters.php`

## Route / Filter Verification

### 1) Routes exist outside `/api/v1`

Command:

```powershell
php spark routes | findstr /I "webhooks/whatsapp api/v1"
```

Observed:
- `GET webhooks/whatsapp -> Webhooks\WhatsAppWebhook::challenge`
- `POST webhooks/whatsapp -> Webhooks\WhatsAppWebhook::events`
- `/api/v1/*` routes remain separate and continue using `apiv1`

### 2) CSRF exclusions for webhook callbacks

Static evidence command:

```powershell
rg -n "webhooks/\\*" app\Config\Filters.php
```

Verified:
- `csrf` excludes `webhooks/*`
- `csrfjson` excludes `webhooks/*`

Note:
- `php spark routes` may still display global filter aliases generically; the authoritative callback allowance for Meta POST is the `globals.before.csrf.except` entry in `app/Config/Filters.php`.

## Security Verification (WF-001 / WF-002)

## Raw-body HMAC validation pattern (before JSON parsing)

Static evidence command:

```powershell
rg -n "getBody\\(|X-Hub-Signature-256|hash_hmac\\('sha256'|hash_equals\\(|sha256='" app\Controllers\Webhooks\WhatsAppWebhook.php
```

Verified:
1. Uses `$this->request->getBody()` (raw body) before `json_decode`
2. Reads `X-Hub-Signature-256`
3. Computes `sha256=` + `hash_hmac('sha256', $rawBody, $appSecret)`
4. Uses `hash_equals()` for signature comparison
5. Parses JSON only after successful signature verification

## Behavior Implemented

### 1) Challenge endpoint (`GET /webhooks/whatsapp`)
- Validates:
  - `hub.mode` / `hub_mode` = `subscribe`
  - `hub.verify_token` / `hub_verify_token` against `platform_secrets.wa_verify_token`
- Returns plain-text `hub.challenge` when valid
- Fail-closed on invalid/missing token

### 2) Event endpoint (`POST /webhooks/whatsapp`)
- Requires valid HMAC signature using `wa_app_secret`
- Rejects missing/invalid signature with `403`
- Rejects invalid JSON with `400`
- Returns JSON summary on success (`200`)

### 3) Idempotent journaling (`whatsapp_delivery_events`)
- Extracts per-status and per-message events from webhook payload
- Inserts each event by `(provider_event_id, event_type)`
- Duplicate inserts are treated as idempotent duplicates (ignored)

### 4) Webhook-driven state transitions
- Status events update `notification_attempts.status` based on provider status:
  - `sent`, `delivered`, `read`
  - `failed`/`undelivered` -> `failed_permanent` + error details
- Parent `notifications.status` is promoted on webhook events:
  - `sent` -> `sent`
  - `delivered` -> `delivered`
  - `read` -> `read`
- Failed webhook delivery can mark parent notification `failed` (without downgrading `read`)

### 5) STOP/UNSUBSCRIBE opt-out ingestion
- Inbound text `stop` / `unsubscribe` is detected
- Clinic is resolved by webhook `metadata.phone_number_id` against clinic WhatsApp channel config
- Inserts `whatsapp_optouts` (`source = webhook`) idempotently by recipient number

## Runtime / Syntax / Guardrails Verification

### 1) PHP syntax

Command:

```powershell
php -l app\Controllers\Webhooks\WhatsAppWebhook.php
```

Observed:
- No syntax errors

### 2) Guardrails regression check (Windows local)

Command:

```powershell
"C:\Program Files\Git\bin\bash.exe" scripts/ci/saas_guardrails.sh
```

Observed:
- DOM sinks passed
- Group auth helpers passed
- Raw tenant queries in controllers passed
- `SaaS guardrail validation succeeded`

## Limitations / Follow-up

1. Webhook event journaling stores `processed_at` but does not yet capture a full processing/audit status column (schema intentionally minimal in Phase 1).
2. Inbound message clinic resolution currently scans clinic WhatsApp channel configs to match `phone_number_id`; this is acceptable for Phase 4 but may need indexing/caching for scale.
3. End-to-end live webhook verification (Meta callback + real signature) requires:
   - `wa_app_secret`
   - `wa_verify_token`
   - externally reachable HTTPS endpoint

