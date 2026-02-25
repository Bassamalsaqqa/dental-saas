# WHATSAPP-E2E-LOCAL-SETUP

Date: 2026-02-25
Status: PASS (Local prerequisites seeded)
Scope: Local end-to-end WhatsApp test setup automation and deployment/local webhook exposure runbook

## Summary

Added a reusable CLI command to seed local WhatsApp test prerequisites safely (encrypted secrets, clinic channel config, and plan feature enablement), then executed it for local clinic `19` (`Denta+`) using local-only credentials provided during the session.

No secret values are stored in git. Verification in this document records only secret key names and encrypted payload lengths.

## Files Added / Updated

- `app/Commands/WhatsAppSeedLocalTest.php`

## Local Setup Command (Reusable)

Command:

```powershell
php spark whatsapp:seed-local-test --clinic-id <CLINIC_ID> --wa-access-token "<TOKEN>" --wa-app-secret "<APP_SECRET>" --wa-verify-token "<VERIFY_TOKEN>" --wa-phone-number-id "<PHONE_NUMBER_ID>"
```

Behavior:
1. Stores platform secrets via `PlatformSecretModel::setSecret()`:
   - `wa_access_token`
   - `wa_app_secret`
   - `wa_verify_token`
   - `wa_phone_number_id`
2. Writes encrypted clinic WhatsApp channel config (`phone_number_id`) via `ClinicNotificationChannelModel::setConfig()`
3. Sets clinic channel flags:
   - `enabled_by_superadmin = 1`
   - `configured_by_clinic = 1`
   - `validated = 1`
   - `provider_type = meta_cloud_api`
4. Patches the clinic’s current plan `features_json` to enable:
   - `notifications.whatsapp.enabled = true`

## Local Execution (This Session)

Executed for:
- Clinic ID: `19` (`Denta+`)

Observed CLI output (sanitized):
- Setup complete
- Platform secrets seeded
- Clinic WhatsApp channel configured/enabled/validated
- Plan features patched (`Plan ID: 26`)

## DB Verification (No Secret Values Printed)

Command:

```powershell
docker exec -i mysql8-dental mysql -uroot -proot democa_dental_saas -e "SELECT secret_key, LENGTH(secret_encrypted) AS enc_len, rotated_at FROM platform_secrets WHERE secret_key IN ('wa_access_token','wa_app_secret','wa_verify_token','wa_phone_number_id') ORDER BY secret_key; SELECT clinic_id, channel_type, enabled_by_superadmin, configured_by_clinic, validated, provider_type, LENGTH(config_encrypted) AS config_len FROM clinic_notification_channels WHERE clinic_id=19 AND channel_type='whatsapp'; SELECT cs.id, cs.clinic_id, cs.plan_id, cs.status, p.name, JSON_EXTRACT(p.features_json, '$.notifications.whatsapp.enabled') AS wa_enabled FROM clinic_subscriptions cs JOIN plans p ON p.id = cs.plan_id WHERE cs.clinic_id=19 ORDER BY cs.id DESC LIMIT 3;"
```

Observed:
1. `platform_secrets` contains all required keys:
   - `wa_access_token`
   - `wa_app_secret`
   - `wa_phone_number_id`
   - `wa_verify_token`
2. `clinic_notification_channels` for clinic `19` / `whatsapp`:
   - enabled/configured/validated all `1`
   - `provider_type = meta_cloud_api`
   - encrypted config present (`config_len > 0`)
3. Current clinic subscription plan (`Plan ID: 26`) has:
   - `notifications.whatsapp.enabled = true`

## How to Expose Local Webhooks (Before Deployment)

You do **not** need to deploy first to test Phase 4.
Use a secure tunnel to expose your local HTTPS app route to Meta:

### Option A: Cloudflare Tunnel (recommended)
1. Install `cloudflared`
2. Start local app (Apache/XAMPP or `php spark serve`) on your laptop
3. Run:
```powershell
cloudflared tunnel --url http://localhost
```
4. Cloudflare gives a public HTTPS URL, e.g.:
   - `https://<random>.trycloudflare.com`
5. Register webhook callback in Meta:
   - Callback URL: `https://<random>.trycloudflare.com/webhooks/whatsapp`
   - Verify Token: the same `wa_verify_token` stored in `platform_secrets`

### Option B: ngrok
1. Install `ngrok`
2. Run:
```powershell
ngrok http 80
```
3. Use the HTTPS forwarding URL + `/webhooks/whatsapp` in Meta webhook config

## How to Test After Deployment

After deployment (Hostinger or production host):
1. Seed production secrets using `PlatformSecretModel::setSecret()` (never plaintext SQL)
2. Ensure `.env` is correct and HTTPS is active
3. Register Meta webhook callback:
   - `https://<your-domain>/webhooks/whatsapp`
4. Subscribe to `messages` field in Meta App Dashboard
5. Trigger a test send and verify:
   - `notification_attempts.provider_message_id` populated
   - `whatsapp_delivery_events` rows inserted
   - parent `notifications.status` promoted (`sent`/`delivered`/`read`)

## Remaining Manual Inputs for Full End-to-End Test

Local prerequisites are now ready, but you still need:
1. A real test patient and `patient_whatsapp` row for clinic `19`
2. A test notification enqueue path (or a small helper command) targeting that patient
3. A public HTTPS callback URL (tunnel or deployed domain) registered in Meta

## Security Note

Credentials used in chat should be treated as sensitive.
If these values were shared outside a secure internal channel, rotate:
- `wa_access_token`
- `wa_app_secret`
- `wa_verify_token`

