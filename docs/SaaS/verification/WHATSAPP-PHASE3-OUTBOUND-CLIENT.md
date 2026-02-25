# WHATSAPP-PHASE3-OUTBOUND-CLIENT

Date: 2026-02-24
Status: PASS
Scope: Phase 3 outbound WhatsApp transport client + driver integration (provider error classification, fail-closed recipient/config handling)

## Summary

Implemented the Phase 3 outbound transport layer for WhatsApp dispatch:
- Added `WhatsAppClient` for Meta Cloud API sends using `wa_access_token` from `platform_secrets`
- Upgraded `WhatsAppDriver` from stub to real driver flow with tenant policy/opt-out checks and recipient resolution
- Added provider-specific error classification required by provider facts:
  - `131056` retryable with minimum 60s backoff
  - `131026` ambiguous terminal (no auto-retry)
- Preserved `wa_app_secret` as a separate platform secret (Phase 4 webhook verification)

## Files Added / Updated

- `app/Services/WhatsAppClient.php`
- `app/Services/NotificationDrivers/WhatsAppDriver.php`

## Implementation Notes (Provider Facts Alignment)

### 1) Separate secrets: `wa_access_token` vs `wa_app_secret`

Static evidence command:

```powershell
rg -n "wa_access_token|wa_app_secret" app\Services\WhatsAppClient.php
```

Verified:
- Outbound send path reads `wa_access_token`
- `wa_app_secret` is checked/logged separately (warning if missing) and not used as send auth
- Code comments explicitly state webhook verification is Phase 4

### 2) `131056` retryable with >= 60s backoff (RL-001)

Static evidence command:

```powershell
rg -n "131056|retryableFailure\\(.*60|next_retry_at" app\Services\WhatsAppClient.php
```

Verified:
- `131056` maps to retryable transport result
- `attempt_status = rate_limited`
- `next_retry_at` is set using a minimum 60-second delay

### 3) `131026` ambiguous and not auto-retryable (ER-001)

Static evidence command:

```powershell
rg -n "131026|WA_131026_AMBIGUOUS_UNDELIVERABLE" app\Services\WhatsAppClient.php app\Services\NotificationDrivers\WhatsAppDriver.php
```

Verified:
- `131026` maps to terminal (`failed_permanent`) transport result
- No auto-retry is scheduled
- Driver surfaces `WA_131026_AMBIGUOUS_UNDELIVERABLE` failure reason for operational visibility

## Driver Behavior (Phase 3)

The `WhatsAppDriver` now:
1. Checks tenant policy suspension (`whatsapp_tenant_policies.suspended`)
2. Fails closed if WhatsApp channel config is missing
3. Resolves recipient identity from:
   - `recipient_address` for `external` (normalized)
   - `patient_whatsapp` (`wa_number` preferred, `bsuid` fallback) for `patient`
4. Checks `whatsapp_optouts` by `wa_number`/`bsuid`
5. Sends text payload (`payload.message` or `payload.body`) through `WhatsAppClient`
6. Returns dispatcher-compatible result shape (`notification_status`, `attempt_status`, `retryable`, `next_retry_at`, etc.)

## Runtime / Syntax Verification

### 1) PHP syntax checks

Commands:

```powershell
php -l app\Services\WhatsAppClient.php
php -l app\Services\NotificationDrivers\WhatsAppDriver.php
```

Observed:
- No syntax errors

### 2) Dispatcher command no-op smoke (integration sanity)

Command:

```powershell
php spark notifications:dispatch --clinic-id 19 --channel whatsapp --limit 5
```

Observed:
- Command runs successfully
- Returns summary without runtime errors when there are no pending WhatsApp rows

### 3) Live outbound send + webhook state progression (local tunnel, real device)

Date tested: 2026-02-25

Setup used:
- Local Cloudflare Tunnel webhook callback registered in Meta (`/webhooks/whatsapp`)
- Real patient record in clinic `19` with `patient_whatsapp.wa_number = +970599350450`
- Live Meta credentials seeded in local `platform_secrets`

Observed sequence:
1. Initial real send attempt failed due a dispatcher bug:
   - `DispatchService::loadChannelContext()` only loaded decrypted channel config for `email`, not `whatsapp`
   - Driver failed closed with `WA_CHANNEL_CONFIG_MISSING`
2. Dispatcher bug was fixed:
   - `DispatchService` now loads decrypted config for the requested channel type (`email` and `whatsapp`)
3. Real send attempt before user-initiated window produced:
   - `notification_attempts.error_code = 131047`
   - `error_message = Re-engagement message`
   - webhook journal received `status:failed`
4. After manually opening a conversation window from the mobile device, a fresh real send succeeded:
   - dispatcher summary showed `Sent: 1`
   - `notification_attempts.provider_message_id` populated
   - webhook journal received `status:sent`, `status:delivered`, `status:read`
   - `notification_attempts.status` promoted to `read`
   - parent `notifications.status` promoted to `read`

Representative verification commands:

```powershell
php spark notifications:dispatch --clinic-id 19 --channel whatsapp --limit 1
docker exec -i mysql8-dental mysql -uroot -proot democa_dental_saas -e "SELECT id, notification_id, status, provider_message_id, error_code, LEFT(error_message,255) AS error_message, next_retry_at, created_at FROM notification_attempts WHERE clinic_id=19 AND channel_type='whatsapp' ORDER BY id DESC LIMIT 5; SELECT id, status, failure_reason FROM notifications WHERE clinic_id=19 AND channel_type='whatsapp' ORDER BY id DESC LIMIT 5; SELECT id, provider_event_id, event_type, processed_at FROM whatsapp_delivery_events ORDER BY id DESC LIMIT 10;"
```

Key evidence captured:
- `131047` (re-engagement restriction) before conversation window
- successful outbound send + webhook `sent/delivered/read` progression after conversation window

### 4) Template-message support enhancement (Phase 3 follow-up)

Implemented after live testing to support business-initiated sends outside the conversation window:
- `WhatsAppClient::sendTemplate(...)`
- `WhatsAppDriver` now dispatches template messages when `payload_json` includes `template_name`

Payload support (driver):
- Text path: `message` or `body`
- Template path:
  - `template_name`
  - `template_language` (optional, default `en_US`)
  - `template_components` (optional array)

## Guardrail Verification (Regression Check)

Command (Windows local):

```powershell
"C:\Program Files\Git\bin\bash.exe" scripts/ci/saas_guardrails.sh
```

Observed:
- `DOM sinks in app/Views` passed
- `Group-based auth helpers` passed
- `Raw tenant queries in controllers` passed
- `SaaS guardrail validation succeeded`

## Operational Prerequisites for Live Sends (Executed in local dev for verification)

Before performing a live WhatsApp send test, ensure:
1. `platform_secrets` includes:
   - `wa_access_token`
   - `wa_app_secret` (for upcoming Phase 4 webhook verification)
2. Clinic WhatsApp channel config (`clinic_notification_channels.config_encrypted`) includes a valid sender `phone_number_id` (supported aliases in code):
   - `phone_number_id`
   - `wa_phone_number_id`
   - `whatsapp_phone_number_id`
3. Clinic plan feature `notifications.whatsapp.enabled` is enabled and channel is configured + validated

## Out of Scope (Still Pending)

- Phase 4 webhook routes/controller/signature verification (`X-Hub-Signature-256` + app secret)
- Retry exhaustion terminal handling in dispatcher core (currently transport classification is returned, but full retry scheduler/exhaustion orchestration remains separate)
- Number resolution fallback/prefix retry logic (client intentionally does not auto-retry `131026`)
