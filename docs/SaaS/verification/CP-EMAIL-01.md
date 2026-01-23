# CP-EMAIL-01 Verification

## Scope
Add control plane email template management (model, migration, settings UI routes).

## Evidence

### Route + wiring grep
Command:
`rg -n "email-templates|EmailTemplateModel|email_templates" app\\Config\\Routes.php app\\Controllers\\ControlPlane\\Settings.php app\\Views\\control_plane\\settings.php`

Output:
```
app\\Controllers\\ControlPlane\\Settings.php:6:use App\\Models\\EmailTemplateModel;
app\\Controllers\\ControlPlane\\Settings.php:26:        $templateModel = new EmailTemplateModel();
app\\Controllers\\ControlPlane\\Settings.php:41:     * POST /controlplane/settings/email-templates
app\\Controllers\\ControlPlane\\Settings.php:45:        $templateModel = new EmailTemplateModel();
app\\Controllers\\ControlPlane\\Settings.php:77:            'route' => '/controlplane/settings/email-templates',
app\\Controllers\\ControlPlane\\Settings.php:87:     * POST /controlplane/settings/email-templates/{id}
app\\Controllers\\ControlPlane\\Settings.php:91:        $templateModel = new EmailTemplateModel();
app\\Controllers\\ControlPlane\\Settings.php:128:            'route' => '/controlplane/settings/email-templates/' . $id,
app\\Views\\control_plane\\settings.php:130:                ? base_url('controlplane/settings/email-templates/' . $activeTemplate['id'])
app\\Views\\control_plane\\settings.php:131:                : base_url('controlplane/settings/email-templates');
app\\Config\\Routes.php:43:        $routes->post('settings/email-templates', 'ControlPlane\\Settings::storeEmailTemplate');
app\\Config\\Routes.php:44:        $routes->post('settings/email-templates/(:num)', 'ControlPlane\\Settings::updateEmailTemplate/$1');
```

### Files added/updated
- `app/Database/Migrations/2026-01-23-091500_CreateEmailTemplates.php`
- `app/Models/EmailTemplateModel.php`
- `app/Controllers/ControlPlane/Settings.php`
- `app/Views/control_plane/settings.php`
- `app/Config/Routes.php`
