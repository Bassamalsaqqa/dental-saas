# Settings Route Decision Table

This table records the current settings-related routes across tenant and control plane, their present implementation status, and the intended overhaul direction.

Legend:

- `canonical`: the preferred user-facing route/surface
- `alias`: compatibility path that remains intentionally
- `redirect`: path should only forward to canonical destination
- `dead`: currently registered but not backed by a valid target in the current controller
- `adjacent`: intentionally outside settings, but linked to or from settings

## Tenant Plane

| Path | Method | Target | Current status | Decision | Notes |
|---|---|---|---|---|---|
| `/settings` | GET | `Settings::index` | live | canonical | Main tenant settings shell |
| `/settings/updateClinic` | POST | `Settings::updateClinic` | live | canonical | Clinic profile save |
| `/settings/updateSystem` | POST | `Settings::updateSystem` | live | canonical | System tab save |
| `/settings/updateWorkingHours` | POST | `Settings::updateWorkingHours` | live | canonical | Operations/hours save |
| `/settings/updateKiosk` | POST | `Settings::updateKiosk` | live | canonical | Kiosk save |
| `/settings/plan-request` | POST | `Settings::requestPlan` | live | canonical | Subscription request flow |
| `/settings/notifications/update` | POST | `Settings::updateNotifications` | live | canonical | Canonical alerts preferences save |
| `/settings/notification-settings` | GET | redirect closure | live | redirect | Redirect to `/settings?tab=communications&subtab=alerts` |
| `/settings/channels` | GET | `Settings::channels` | live | alias | Deep-link alias into communications |
| `/settings/email` | GET | `Settings::email` | live | alias | Deep-link alias into communications |
| `/settings/sms` | GET | `Settings::sms` | live | alias | Deep-link alias into communications |
| `/settings/whatsapp` | GET | `Settings::whatsapp` | live | alias | Deep-link alias into communications |
| `/settings/kiosk` | GET | `Settings::kiosk` | live | alias | Deep-link alias into kiosk tab |
| `/notifications` | GET | redirect closure | live | redirect | Redirect to alerts subtab |
| `/notifications/update` | GET | redirect closure | live | redirect | Redirect to alerts subtab |
| `/notifications/ledger` | GET | `NotificationLedger::index` | live | adjacent | Notification ops/log surface, not core settings |
| `/settings/update` | POST | `Settings::update` | live | alias | Explicit fallback redirect to canonical settings shell |
| `/settings/backup` | GET | redirect closure | live | redirect | Redirects to `/settings?tab=backup` |
| `/settings/create-backup` | POST | `Settings::createBackup` | live | canonical | Tenant backup tab now creates a SQL snapshot in `writable/backups` |
| `/settings/restore` | POST | `Settings::restore` | live | fallback | Explicitly returns user-facing error because web restore is not enabled yet |
| `/settings/download-backup/{file}` | GET | `Settings::downloadBackup` | live | canonical | Validated download path for generated backup files |
| `/settings/security` | GET | redirect closure | live | redirect | Redirects to `/settings?tab=system` because there is no dedicated security tab in the current shell |
| `/settings/security/update` | POST | `Settings::updateSecurity` | live | fallback | Explicitly redirects back to the system tab with explanatory feedback |
| `/settings/updateRetention` | POST | `Settings::updateRetention` | live | canonical | Global-mode-only retention save used by the tenant backup tab when superadmin is in tenant context |

## Control Plane

| Path | Method | Target | Current status | Decision | Notes |
|---|---|---|---|---|---|
| `/controlplane/settings` | GET | `ControlPlane\Settings::index` | live | canonical | Main control-plane settings shell |
| `/controlplane/settings/platform` | POST | `ControlPlane\Settings::updatePlatform` | live | canonical | Platform settings save |
| `/controlplane/settings/tenant-defaults` | POST | `ControlPlane\Settings::updateTenantDefaults` | live | canonical | Clinic defaults save |
| `/controlplane/settings/retention` | POST | `ControlPlane\Settings::updateRetention` | live | canonical | Export retention config |
| `/controlplane/settings/retention/prune` | POST | `ControlPlane\Settings::pruneExports` | live | canonical | Export cleanup action |
| `/controlplane/settings/audit-retention` | POST | `ControlPlane\Settings::updateAuditRetention` | live | canonical | Audit retention config |
| `/controlplane/settings/audit-retention/prune` | POST | `ControlPlane\Settings::pruneAuditLogs` | live | canonical | Audit cleanup action |
| `/controlplane/settings/email-templates` | POST | `ControlPlane\Settings::storeEmailTemplate` | live | canonical | Communications config |
| `/controlplane/settings/email-templates/{id}` | POST | `ControlPlane\Settings::updateEmailTemplate/{id}` | live | canonical | Communications config |
| `/controlplane/email-templates` | GET | redirect closure | live | redirect | Redirect to communications subtab |
| `/controlplane/operations` | GET | `ControlPlane\Operations::index` | live | adjacent | Operations console, should remain outside settings but linked clearly |
| `/controlplane/whatsapp` | GET | `ControlPlane\WhatsApp::index` | live | adjacent | WhatsApp live ops/dashboard, not pure config |
| `/controlplane/whatsapp/rotate-secret` | POST | `ControlPlane\WhatsApp::rotateSecret` | live | adjacent | Operational/config bridge |
| `/controlplane/whatsapp/policy` | POST | `ControlPlane\WhatsApp::updatePolicy` | live | adjacent | Candidate for future move into settings only if UI is restructured deliberately |
| `/controlplane/whatsapp/emergency-pause` | POST | `ControlPlane\WhatsApp::setEmergencyPause` | live | adjacent | Operational control, should stay outside core settings |

## Immediate Cleanup Priorities

1. Retire or repoint dead tenant GET routes:
   - `/settings/backup`
   - `/settings/security`

2. Decide whether tenant web restore should remain an explicit fallback or become a real supported feature:
   - `/settings/restore`

3. Decide whether the generic tenant POST alias should remain:
   - `/settings/update`

4. Keep compatibility aliases only where they protect active users or old internal links.
