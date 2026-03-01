# WhatsApp Phase 6 Live Pilot Attempt - 2026-03-01

- **Date:** 2026-03-01
- **Scope:** Run a real outbound WhatsApp send attempt to the designated pilot number and record provider/webhook outcomes.

## Preconditions Verified

Environment checks (clinic `19`):
- WhatsApp channel enabled/configured/validated (`meta_cloud_api`)
- Required platform secret keys exist (`wa_access_token`, `wa_app_secret`, `wa_verify_token`, `wa_phone_number_id`)
- Plan feature gate enabled (`notifications.whatsapp.enabled = true`)
- `patient_whatsapp` row exists for patient `38` with number `+970599350450`

## Execution

1. Enqueued test notification `#48` (`channel_type=whatsapp`, `recipient_type=patient`, `recipient_id=38`).
2. Ran dispatch without unrestricted network:
   - `php spark tenant:run-job DispatchNotificationsJob --clinic-id 19`
   - Result: `Processed: 1`, `Sent: 0`, `Failed: 1`
   - Attempt error: `WA_NETWORK_EXCEPTION` (`Failed to connect to graph.facebook.com:443`)
3. Enqueued second test notification `#49`.
4. Re-ran dispatch with unrestricted network:
   - `php spark tenant:run-job DispatchNotificationsJob --clinic-id 19`
   - Result: `Processed: 1`, `Sent: 1`, `Failed: 0`

## Observed Data Evidence

Notification outcomes:
- `notifications.id=48`: `status=failed`, `failure_reason=WA_SEND_FAILED:WA_NETWORK_EXCEPTION`
- `notifications.id=49`: final `status=failed`, `failure_reason=WA_WEBHOOK_FAILED:131047`

Attempt outcomes:
- `notification_attempts.notification_id=48`: `status=failed`, `error_code=WA_NETWORK_EXCEPTION`
- `notification_attempts.notification_id=49`:
  - `status=failed_permanent`
  - `provider_message_id=wamid.HBgMOTcwNTk5MzUwNDUwFQIAERgSRjE1OEI5MjhEOTg5QjI4RDI0AA==`
  - `error_code=131047`
  - `error_message=Re-engagement message`

Webhook evidence:
- `whatsapp_delivery_events.id=58`
  - `provider_event_id` matches the attempt `provider_message_id`
  - `event_type=status:failed`
  - provider error details in payload: `code=131047`, `title=Re-engagement message`

## Conclusion

The pipeline is functioning end-to-end:
- outbound call to provider works (when network is available),
- provider message ID is captured,
- signed webhook callback is ingested and mapped back to the attempt/notification.

Current pilot blocker is business-policy related, not transport failure:
- provider code `131047` indicates the send was outside the allowed session and requires a template message.

## Required Next Step

Phase 6 should proceed with template-first sends for out-of-session recipients:
- enqueue payloads with `template_name` (+ language/components as needed),
- re-run the same dispatch + webhook verification flow,
- then evaluate delivery progression (`sent -> delivered -> read`) for approved template traffic.
