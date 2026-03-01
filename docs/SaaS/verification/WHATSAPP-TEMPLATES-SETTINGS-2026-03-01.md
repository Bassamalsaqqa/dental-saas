# WhatsApp Templates + Settings Customization - 2026-03-01

- **Date:** 2026-03-01
- **Scope:** Make WhatsApp messaging templates customizable from Settings, default template content to Arabic, and harden runtime behavior for out-of-session handling.

## Implemented

### 1) Tenant Settings: configurable WhatsApp template profile

Updated tenant channel config modal to support WhatsApp template controls:
- `fallback_template.name` (out-of-session fallback)
- `fallback_template.language` (default `ar`)
- `fallback_template.components` JSON
- `message_templates` JSON object (Arabic defaults, fully editable)

Files:
- `app/Views/components/smtp_form.php`
- `app/Views/settings/channels.php`
- `app/Controllers/Settings.php`
- `app/Config/Routes.php`

New endpoint:
- `POST /settings/getChannelConfig` (`Settings::getChannelConfig`)
  - AJAX-only
  - tenant-scoped
  - returns sanitized config
  - for WhatsApp, returns normalized template profile with Arabic defaults

### 2) Runtime behavior: template-aware driver + out-of-session fallback

Enhanced WhatsApp driver behavior:
- Supports `payload.message_template_key` + `payload.template_vars` to render a message from configured `message_templates`.
- If provider returns `131047` (re-engagement/out-of-session), driver auto-attempts configured `fallback_template`.

Files:
- `app/Services/NotificationDrivers/WhatsAppDriver.php`

### 3) Runtime hardening: phone number ID fallback

Enhanced WhatsApp client send path:
- Uses `platform_secrets.wa_phone_number_id` when channel config `phone_number_id` is missing.
- If provider returns Graph error `100` for configured ID, retries once using platform secret phone number ID.

File:
- `app/Services/WhatsAppClient.php`

### 4) Test coverage updates

Added endpoint coverage for config retrieval defaults:
- `testSettingsGetChannelConfigReturnsWhatsaAppTemplateDefaults`

File:
- `tests/unit/WhatsAppPhase5EndpointsTest.php`

## Verification

### PHP lint

```bat
php -l app/Controllers/Settings.php
php -l app/Services/NotificationDrivers/WhatsAppDriver.php
php -l app/Services/WhatsAppClient.php
php -l app/Views/components/smtp_form.php
php -l app/Views/settings/channels.php
php -l tests/unit/WhatsAppPhase5EndpointsTest.php
```

Observed:
- all files report `No syntax errors detected`

### Unit tests

```bat
vendor\bin\phpunit.bat --no-coverage tests\unit\WhatsAppPhase5EndpointsTest.php tests\unit\WhatsAppClientTest.php tests\unit\DispatchServiceTest.php tests\unit\WhatsAppWebhookTest.php
```

Observed:
- `OK (33 tests, 156 assertions)`

### Live dispatch verification (clinic `19`, patient `38`, real number)

Enqueued and dispatched controlled WhatsApp notification:
- `notifications.id=72`
- dispatch summary: `Processed: 1`, `Sent: 1`, `Failed: 0`
- final state:
  - `notifications.id=72 -> status=read`
  - `notification_attempts.notification_id=72 -> status=read`
  - `provider_message_id` present

Validated new Arabic template-key rendering path:
- Enqueued `notifications.id=73` with payload:
  - `message_template_key=appointment_confirmation`
  - `template_vars={ appointment_date, appointment_time }`
- Dispatch summary: `Processed: 1`, `Sent: 1`, `Failed: 0`
- Final state:
  - `notifications.id=73 -> status=delivered`
  - `notification_attempts.notification_id=73 -> status=delivered`
  - `provider_message_id` present

## Notes

- Arabic message templates are now seeded as defaults but fully editable per clinic from Settings.
- Out-of-session delivery still depends on approved template names in Meta; fallback execution requires valid `fallback_template.name`.
- The new runtime fallback in `WhatsAppClient` protects against accidental channel `phone_number_id` misconfiguration by retrying with `wa_phone_number_id` from platform secrets when error `100` is detected.
