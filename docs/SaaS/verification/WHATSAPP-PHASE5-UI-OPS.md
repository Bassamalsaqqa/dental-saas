# WhatsApp Phase 5 UI + Ops Verification

- **Date:** 2026-02-25
- **Scope:** Tenant WhatsApp channel config UX (structured fields + readiness validation), patient WhatsApp identity UI, and Control Plane WhatsApp operations surface (secrets rotation + tenant policy/emergency pause controls).

## Implemented

### Tenant: Channel Config UX (`/settings/channels`)
- Added structured WhatsApp form fields in the config modal:
  - `phone_number_id`
  - optional `api_version`
- Preserved SMTP-specific form flow for email and JSON fallback for other channels.
- Added tenant AJAX endpoint `POST /settings/testWhatsAppChannel` to validate WhatsApp channel readiness without sending a message.
- Readiness checks verify:
  - tenant WhatsApp channel exists and is configured
  - `phone_number_id` present in decrypted channel config
  - platform secrets present: `wa_access_token`, `wa_app_secret`, `wa_verify_token`
  - plan feature `notifications.whatsapp.enabled`

### Tenant: Patient WhatsApp Identity UI (`/patient/{id}`)
- Added a WhatsApp identity card in patient details.
- Displays:
  - `wa_number`
  - `resolution_status`
  - `verified_at`
  - opt-out indicator (latest `whatsapp_optouts` row for patient)
- Added tenant AJAX endpoint:
  - `POST /patient/{id}/whatsapp-identity`
  - plural alias `POST /patients/{id}/whatsapp-identity`
- Server-side behavior:
  - validates patient belongs to active clinic
  - normalizes WhatsApp number to `+<digits>` format
  - upserts `patient_whatsapp`
  - resets status to `pending` and clears `verified_at` / `last_error_code` on change

### Control Plane: WhatsApp Ops (`/controlplane/whatsapp`)
- Added dedicated control-plane page with:
  - 24h summary cards (attempts, sent-ish, failures, failure rate, events/opt-outs)
  - recent WhatsApp attempts
  - recent webhook events
  - recent opt-outs
  - tenant policy snapshot
- Added secret rotation forms (write-only values; metadata only display)
  - allowlisted keys only:
    - `wa_access_token`
    - `wa_app_secret`
    - `wa_verify_token`
    - `wa_phone_number_id`
- Added emergency pause / policy controls:
  - `POST /controlplane/whatsapp/emergency-pause`
  - `POST /controlplane/whatsapp/policy`
- All mutating actions are audit-logged through `ControlPlaneAuditService::logEvent(...)`.

## Guardrail / Architecture Notes

- Control-plane WhatsApp dashboard queries are implemented via model helpers, not raw controller `db->query()` / `->table(...)`.
- Tenant-scoped WhatsApp models use `withoutTenantScope()` in control-plane helper methods where global visibility is required.
- No DOM sink usage (`innerHTML`, `outerHTML`, `insertAdjacentHTML`, `.html(`) introduced in `app/Views`.

## Verification Performed

- PHP lint (controllers/models/views touched): PASS
  - `app/Controllers/Settings.php`
  - `app/Controllers/Patient.php`
  - `app/Controllers/ControlPlane/WhatsApp.php`
  - `app/Models/*` helpers touched
  - `app/Views/*` touched
- Route registration checks via `php spark routes`: PASS
  - `settings/testWhatsAppChannel`
  - `patient/{id}/whatsapp-identity`
  - `patients/{id}/whatsapp-identity`
  - `controlplane/whatsapp*`
- Guardrail regex checks (sandbox-safe direct `rg` runs): PASS
  - no DOM sinks in `app/Views`
  - no raw controller queries in `app/Controllers`

## Follow-up (Phase 5 hardening)

- Add automated tests for:
  - `Settings::testWhatsAppChannel` readiness combinations
  - patient WhatsApp identity normalization + tenant ownership checks
  - control-plane secret-key allowlist enforcement
  - emergency pause / policy upsert behavior
- Optional UX improvement: fetch/display masked existing WhatsApp channel config fields for clinic admins (without exposing secrets).

## Automated Endpoint Tests (2026-03-01)

Added new test suite:
- `tests/unit/WhatsAppPhase5EndpointsTest.php`

Coverage includes:
- `POST /settings/testWhatsAppChannel`
  - rejects non-AJAX requests
  - returns "not configured" when clinic channel setup is missing
- `POST /patient/{id}/whatsapp-identity`
  - rejects non-AJAX requests
  - rejects invalid number shape
  - accepts and normalizes valid input to `+<digits>` (e.g. `+970599350450`)
- `POST /controlplane/whatsapp/rotate-secret`
  - rejects keys outside allowlist
  - accepts allowlisted key rotation
- `POST /controlplane/whatsapp/policy`
  - upserts policy limits for a clinic
- `POST /controlplane/whatsapp/emergency-pause`
  - toggles suspension and persists suspension reason

Execution evidence:

```bat
vendor\bin\phpunit.bat --no-coverage tests\unit\WhatsAppWebhookTest.php tests\unit\WhatsAppClientTest.php tests\unit\DispatchServiceTest.php tests\unit\WhatsAppPhase5EndpointsTest.php
```

Result:
- `OK (32 tests, 149 assertions)`
