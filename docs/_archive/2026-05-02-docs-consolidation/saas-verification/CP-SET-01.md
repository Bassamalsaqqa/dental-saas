# CP-SET-01 Verification

## Scope
Control plane settings now persist platform profile, export retention, and audit retention policies with control-plane-only routes and governance actions.

## Evidence

### Controller and view syntax
Command:
`php -l app/Controllers/ControlPlane/Settings.php`

Output:
```
No syntax errors detected in app/Controllers/ControlPlane/Settings.php
```

Command:
`php -l app/Views/control_plane/settings.php`

Output:
```
No syntax errors detected in app/Views/control_plane/settings.php
```

### Control plane settings routes
Command:
`php spark routes | findstr /I "controlplane/settings"`

Output:
```
| GET    | controlplane/settings                                   | ?    | \App\Controllers\ControlPlane\Settings::index                     | csrf forcehttps controlplane                                      | controlplane secureheaders csrfjson toolbar                                      |
| POST   | controlplane/settings/email-templates                   | ?    | \App\Controllers\ControlPlane\Settings::storeEmailTemplate        | csrf forcehttps controlplane                                      | controlplane secureheaders csrfjson toolbar                                      |
| POST   | controlplane/settings/email-templates/([0-9]+)          | ?    | \App\Controllers\ControlPlane\Settings::updateEmailTemplate/$1    | csrf forcehttps controlplane                                      | controlplane secureheaders csrfjson toolbar                                      |
| POST   | controlplane/settings/platform                          | ?    | \App\Controllers\ControlPlane\Settings::updatePlatform            | csrf forcehttps controlplane                                      | controlplane secureheaders csrfjson toolbar                                      |
| POST   | controlplane/settings/retention                         | ?    | \App\Controllers\ControlPlane\Settings::updateRetention           | csrf forcehttps controlplane                                      | controlplane secureheaders csrfjson toolbar                                      |
| POST   | controlplane/settings/retention/prune                   | ?    | \App\Controllers\ControlPlane\Settings::pruneExports              | csrf forcehttps controlplane                                      | controlplane secureheaders csrfjson toolbar                                      |
| POST   | controlplane/settings/audit-retention                   | ?    | \App\Controllers\ControlPlane\Settings::updateAuditRetention      | csrf forcehttps controlplane                                      | controlplane secureheaders csrfjson toolbar                                      |
| POST   | controlplane/settings/audit-retention/prune             | ?    | \App\Controllers\ControlPlane\Settings::pruneAuditLogs            | csrf forcehttps controlplane                                      | controlplane secureheaders csrfjson toolbar                                      |
```

### Control plane settings keys wired
Command:
`rg -n "control_plane_audit_retention_days|platform_support|retention_mode" app/Controllers/ControlPlane/Settings.php app/Views/control_plane/settings.php`

Output:
```
app/Controllers/ControlPlane/Settings.php:46:            'platform_support_email' => 'permit_empty|valid_email|max_length[120]',
app/Controllers/ControlPlane/Settings.php:47:            'platform_support_phone' => 'permit_empty|max_length[40]',
app/Controllers/ControlPlane/Settings.php:48:            'platform_support_whatsapp_primary' => 'permit_empty|max_length[40]',
app/Controllers/ControlPlane/Settings.php:49:            'platform_support_whatsapp_secondary' => 'permit_empty|max_length[40]',
app/Controllers/ControlPlane/Settings.php:50:            'platform_support_website' => 'permit_empty|valid_url|max_length[200]',
app/Controllers/ControlPlane/Settings.php:59:            'platform_support_email' => trim((string) $this->request->getPost('platform_support_email')),
app/Controllers/ControlPlane/Settings.php:60:            'platform_support_phone' => trim((string) $this->request->getPost('platform_support_phone')),
app/Controllers/ControlPlane/Settings.php:61:            'platform_support_whatsapp_primary' => trim((string) $this->request->getPost('platform_support_whatsapp_primary')),
app/Controllers/ControlPlane/Settings.php:62:            'platform_support_whatsapp_secondary' => trim((string) $this->request->getPost('platform_support_whatsapp_secondary')),
app/Controllers/ControlPlane/Settings.php:63:            'platform_support_website' => trim((string) $this->request->getPost('platform_support_website')),
app/Controllers/ControlPlane/Settings.php:88:            'retention_mode' => 'required|in_list[latest,keep_last_n,keep_x_days]',
app/Controllers/ControlPlane/Settings.php:98:            'retention_mode' => $this->request->getPost('retention_mode'),
app/Controllers/ControlPlane/Settings.php:112:                'mode' => $settings['retention_mode'],
app/Controllers/ControlPlane/Settings.php:165:            'control_plane_audit_retention_days' => 'required|is_natural_no_zero',
app/Controllers/ControlPlane/Settings.php:172:        $days = (int) $this->request->getPost('control_plane_audit_retention_days');
app/Controllers/ControlPlane/Settings.php:173:        $this->settingsModel->setGlobalSetting('control_plane_audit_retention_days', $days, 'number');
app/Controllers/ControlPlane/Settings.php:199:        $days = (int) $this->settingsModel->getGlobalSetting('control_plane_audit_retention_days', 365);
app/Controllers/ControlPlane/Settings.php:226:            'platform_support_email' => '',
app/Controllers/ControlPlane/Settings.php:227:            'platform_support_phone' => '',
app/Controllers/ControlPlane/Settings.php:228:            'platform_support_whatsapp_primary' => '',
app/Controllers/ControlPlane/Settings.php:229:            'platform_support_whatsapp_secondary' => '',
app/Controllers/ControlPlane/Settings.php:230:            'platform_support_website' => '',
app/Controllers/ControlPlane/Settings.php:231:            'retention_mode' => 'latest',
app/Controllers/ControlPlane/Settings.php:234:            'control_plane_audit_retention_days' => 365,
app/Views/control_plane/settings.php:72:                                        <input type="email" name="platform_support_email" value="<?= esc($settings['platform_support_email'] ?? '') ?>" placeholder="support@yourdomain.com" class="w-full px-4 py-3 bg-white border border-slate-200 rounded-xl text-slate-700 font-medium focus:border-slate-500 focus:ring-4 focus:ring-slate-200/60 transition-all">
app/Views/control_plane/settings.php:76:                                        <input type="text" name="platform_support_phone" value="<?= esc($settings['platform_support_phone'] ?? '') ?>" placeholder="+970 000 000 000" class="w-full px-4 py-3 bg-white border border-slate-200 rounded-xl text-slate-700 font-medium focus:border-slate-500 focus:ring-4 focus:ring-slate-200/60 transition-all">
app/Views/control_plane/settings.php:80:                                        <input type="text" name="platform_support_whatsapp_primary" value="<?= esc($settings['platform_support_whatsapp_primary'] ?? '') ?>" placeholder="+970 000 000 000" class="w-full px-4 py-3 bg-white border border-slate-200 rounded-xl text-slate-700 font-medium focus:border-slate-500 focus:ring-4 focus:ring-slate-200/60 transition-all">
app/Views/control_plane/settings.php:84:                                        <input type="text" name="platform_support_whatsapp_secondary" value="<?= esc($settings['platform_support_whatsapp_secondary'] ?? '') ?>" placeholder="+972 000 000 000" class="w-full px-4 py-3 bg-white border border-slate-200 rounded-xl text-slate-700 font-medium focus:border-slate-500 focus:ring-4 focus:ring-slate-200/60 transition-all">
app/Views/control_plane/settings.php:88:                                        <input type="url" name="platform_support_website" value="<?= esc($settings['platform_support_website'] ?? '') ?>" placeholder="https://yourdomain.com" class="w-full px-4 py-3 bg-white border border-slate-200 rounded-xl text-slate-700 font-medium focus:border-slate-500 focus:ring-4 focus:ring-slate-200/60 transition-all">
app/Views/control_plane/settings.php:118:                                    <?php $retentionMode = $settings['retention_mode'] ?? 'latest'; ?>
app/Views/control_plane/settings.php:121:                                            <input type="radio" name="retention_mode" value="latest" class="sr-only" <?= $retentionMode === 'latest' ? 'checked' : '' ?>>
app/Views/control_plane/settings.php:126:                                            <input type="radio" name="retention_mode" value="keep_last_n" class="sr-only" <?= $retentionMode === 'keep_last_n' ? 'checked' : '' ?>>
app/Views/control_plane/settings.php:131:                                            <input type="radio" name="retention_mode" value="keep_x_days" class="sr-only" <?= $retentionMode === 'keep_x_days' ? 'checked' : '' ?>>
app/Views/control_plane/settings.php:177:                                        <input type="number" name="control_plane_audit_retention_days" value="<?= esc((int) ($settings['control_plane_audit_retention_days'] ?? 365)) ?>" min="30" max="3650" class="w-full px-4 py-3 bg-white border border-slate-200 rounded-xl text-slate-700 font-medium focus:border-indigo-500 focus:ring-4 focus:ring-indigo-200/60 transition-all">
app/Views/control_plane/settings.php:254:    const retentionRadios = document.querySelectorAll('input[name="retention_mode"]');
```
