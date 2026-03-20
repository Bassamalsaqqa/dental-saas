# WhatsApp Phase 6 Workflow Hardening Verification

- Date: 2026-03-20
- Status: Verified
- Scope: readiness endpoint alignment, tenant template/settings cleanup, UI hardening, and template-first workflow wiring

## Implemented Changes

1. Readiness endpoint alignment
- `Settings::testWhatsAppChannel()` now verifies:
  - tenant clinic context
  - clinic WhatsApp channel exists and is configured
  - superadmin enablement
  - tenant validation state
  - tenant `phone_number_id`
  - plan feature gate `notifications.whatsapp.enabled`
  - required platform secrets: `wa_access_token`, `wa_app_secret`, `wa_verify_token`

2. Encoding-safe template defaults
- WhatsApp tenant settings now normalize Arabic defaults using Unicode-safe decoding in:
  - `app/Controllers/Settings.php`
  - `app/Views/settings/partials/communications/whatsapp.php`
  - `app/Views/settings/index.php`
- Reset-to-default behavior now restores the same canonical Arabic defaults used by the backend.

3. UI debt removal
- `app/Views/control_plane/whatsapp.php`
  - removed the DOM sink in the toast renderer
- `app/Views/patient/partials/whatsapp_identity.php`
  - replaced `prompt()` / `alert()` editing with a modal-based form
  - added inline validation/error rendering
  - preserved AJAX save behavior

4. Product workflow wiring
- Added `app/Services/WhatsAppWorkflowService.php`
- Added `app/Commands/WhatsAppQueueProductFlows.php` (`whatsapp:queue-product-flows`)
- Wired product events:
  - appointment confirmation on create/update in `app/Controllers/Appointment.php`
  - follow-up when examination status changes to `completed` in `app/Controllers/Examination.php`
  - invoice payment reminder on invoice creation in `app/Controllers/Finance.php`
  - queued due appointment reminders and queued due invoice-payment reminders via `whatsapp:queue-product-flows`
- Workflow notifications enqueue template-aware payloads using:
  - `message_template_key`
  - `template_vars`
  - duplicate-suppression `workflow_key`

## Verification Evidence

### Syntax
- `php -l app/Controllers/Settings.php`
- `php -l app/Controllers/Appointment.php`
- `php -l app/Controllers/Examination.php`
- `php -l app/Controllers/Finance.php`
- `php -l app/Services/WhatsAppWorkflowService.php`
- `php -l app/Commands/WhatsAppQueueProductFlows.php`
- `php -l app/Views/control_plane/whatsapp.php`
- `php -l app/Views/settings/partials/communications/whatsapp.php`
- `php -l app/Views/patient/partials/whatsapp_identity.php`
- `php -l app/Views/settings/index.php`
- Result: all passed

### Targeted UI-sink scan
- `rg -n "innerHTML|outerHTML|insertAdjacentHTML|\.html\(|prompt\(|alert\(" app/Views/control_plane/whatsapp.php app/Views/patient/partials/whatsapp_identity.php app/Views/settings/index.php`
- Result:
  - no `prompt()` / `alert()` in the patient WhatsApp identity UI
  - no DOM sink usage remains in the control-plane WhatsApp view
  - settings view uses DOM-safe button loading logic

### Command registration
- `php spark list | findstr /I "whatsapp:queue-product-flows whatsapp:ops-check notifications:dispatch"`
- Result: all commands listed

### Route presence
- `php spark routes | findstr /I "controlplane/whatsapp webhooks/whatsapp settings/testWhatsAppChannel whatsapp-identity"`
- Result: expected control-plane, webhook, readiness-test, and patient identity routes listed

### Existing WhatsApp test bundle
- `vendor\bin\phpunit.bat --no-coverage tests\unit\WhatsAppWebhookTest.php tests\unit\WhatsAppClientTest.php tests\unit\DispatchServiceTest.php tests\unit\WhatsAppPhase5EndpointsTest.php`
- Result: `OK (33 tests, 156 assertions)`

### Phase 6 ops-readiness check
- `php spark whatsapp:ops-check --hours 24 --json`
- Result: `status: PASS`

## Notes
- `bash scripts/ci/saas_guardrails.sh` could not be executed in this Windows session because local Bash service startup returned `Access is denied`.
- Existing PHPUnit and command verification ran successfully once the database was available.
- Workflow enqueueing is intentionally asynchronous; these changes do not send directly from domain controllers.
