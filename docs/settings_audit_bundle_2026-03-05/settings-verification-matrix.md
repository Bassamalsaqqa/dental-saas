# Settings Verification Matrix

This matrix records the final verification targets for the current settings overhaul state across tenant and control plane.

Verification basis used in this repo pass:

- route inspection in [app/Config/Routes.php](C:\xampp\htdocs\dental-saas\app\Config\Routes.php)
- controller inspection in [app/Controllers/Settings.php](C:\xampp\htdocs\dental-saas\app\Controllers\Settings.php) and [app/Controllers/ControlPlane/Settings.php](C:\xampp\htdocs\dental-saas\app\Controllers\ControlPlane\Settings.php)
- shell and partial inspection in tenant/control-plane settings views
- syntax validation via `php -l`

## Tenant Plane

| Area | Canonical URL | Expected behavior | Evidence |
|---|---|---|---|
| Main settings shell | `/settings` | Loads consolidated settings shell | [app/Config/Routes.php](C:\xampp\htdocs\dental-saas\app\Config\Routes.php), [app/Views/settings/index.php](C:\xampp\htdocs\dental-saas\app\Views\settings\index.php) |
| Tab deep-linking | `/settings?tab=system` | Selected tab stays addressable and refresh-safe | [app/Views/settings/index.php](C:\xampp\htdocs\dental-saas\app\Views\settings\index.php) |
| Communications subtabs | `/settings?tab=communications&subtab=alerts` | Alerts subtab loads inside consolidated shell | [app/Views/settings/partials/communications.php](C:\xampp\htdocs\dental-saas\app\Views\settings\partials\communications.php), [alerts.php](C:\xampp\htdocs\dental-saas\app\Views\settings\partials\communications\alerts.php) |
| Legacy notification GET | `/settings/notification-settings` | Redirects permanently to alerts subtab | [app/Config/Routes.php](C:\xampp\htdocs\dental-saas\app\Config\Routes.php) |
| Notification save | `/settings/notifications/update` | Canonical POST endpoint for alert preferences | [app/Config/Routes.php](C:\xampp\htdocs\dental-saas\app\Config\Routes.php), [app/Controllers/Settings.php](C:\xampp\htdocs\dental-saas\app\Controllers\Settings.php) |
| Backup GET alias | `/settings/backup` | Redirects permanently to `?tab=backup` | [app/Config/Routes.php](C:\xampp\htdocs\dental-saas\app\Config\Routes.php) |
| Security GET alias | `/settings/security` | Redirects permanently to `?tab=system` | [app/Config/Routes.php](C:\xampp\htdocs\dental-saas\app\Config\Routes.php) |
| Validation summary | any tab save failure | Summary renders at top of settings shell and clears as fields are corrected | [app/Views/settings/index.php](C:\xampp\htdocs\dental-saas\app\Views\settings\index.php) |
| RTL spacing | Arabic locale | Menu, headers, cards, and buttons preserve RTL spacing rules | [app/Views/settings/index.php](C:\xampp\htdocs\dental-saas\app\Views\settings\index.php) |

## Control Plane

| Area | Canonical URL | Expected behavior | Evidence |
|---|---|---|---|
| Main settings shell | `/controlplane/settings` | Loads consolidated control-plane settings shell | [app/Config/Routes.php](C:\xampp\htdocs\dental-saas\app\Config\Routes.php), [app/Views/control_plane/settings.php](C:\xampp\htdocs\dental-saas\app\Views\control_plane\settings.php) |
| Tab deep-linking | `/controlplane/settings?tab=governance` | Selected tab stays addressable and refresh-safe | [app/Views/control_plane/settings.php](C:\xampp\htdocs\dental-saas\app\Views\control_plane\settings.php) |
| Communications subtabs | `/controlplane/settings?tab=communications&subtab=whatsapp` | WhatsApp configuration loads inside communications shell | [communications.php](C:\xampp\htdocs\dental-saas\app\Views\control_plane\settings_partials\communications.php), [whatsapp.php](C:\xampp\htdocs\dental-saas\app\Views\control_plane\settings_partials\communications\whatsapp.php) |
| Email templates redirect | `/controlplane/email-templates` | Redirects to communications/email-templates subtab | [app/Config/Routes.php](C:\xampp\htdocs\dental-saas\app\Config\Routes.php) |
| Save-state contract | POST settings forms | Submit buttons enter loading state only after passing native validation | [app/Views/control_plane/settings.php](C:\xampp\htdocs\dental-saas\app\Views\control_plane\settings.php) |
| Validation summary | any tab save failure | Summary renders at top of settings shell and stale errors clear on correction | [app/Views/control_plane/settings.php](C:\xampp\htdocs\dental-saas\app\Views\control_plane\settings.php) |
| Settings-adjacent ops links | communications tab | Links to WhatsApp command center and operations console remain explicit | [communications.php](C:\xampp\htdocs\dental-saas\app\Views\control_plane\settings_partials\communications.php), [operations.php](C:\xampp\htdocs\dental-saas\app\Views\control_plane\operations.php), [whatsapp.php](C:\xampp\htdocs\dental-saas\app\Views\control_plane\whatsapp.php) |

## Manual Browser Checks Still Recommended

The following should be smoke-tested in a browser before declaring the overhaul fully closed:

1. Open tenant settings directly with `tab` and `subtab` query parameters and confirm refresh persistence.
2. Open control-plane settings directly with `tab` and `subtab` query parameters and confirm refresh persistence.
3. Use browser back/forward after switching tabs in both planes.
4. Save one form in each tenant settings tab and one form in each control-plane settings tab.
5. Switch locale between English and Arabic and verify RTL/LTR spacing remains correct in both settings shells.
