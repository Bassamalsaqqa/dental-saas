# CP-SET-02 Verification

## Scope
Add control-plane tenant defaults for new clinic onboarding, store global defaults, and seed new clinics on creation.

## Evidence

### Onboarding service syntax
Command:
`php -l app/Services/OnboardingService.php`

Output:
```
No syntax errors detected in app/Services/OnboardingService.php
```

### Control plane route
Command:
`php spark routes | findstr /I "controlplane/settings/tenant-defaults"`

Output:
```
| POST   | controlplane/settings/tenant-defaults                   | ?    | \App\Controllers\ControlPlane\Settings::updateTenantDefaults      | csrf forcehttps controlplane                                      | controlplane secureheaders csrfjson toolbar                                      |
```

### Defaults + seeding wiring
Command:
`rg -n "tenant-defaults|seedClinicDefaults|default_working_days|default_timezone" app/Controllers/ControlPlane/Settings.php app/Services/OnboardingService.php app/Views/control_plane/settings.php app/Database/Migrations/2026-02-09-110000_AddControlPlaneSettingsDefaults.php`

Output:
```
app/Database/Migrations/2026-02-09-110000_AddControlPlaneSettingsDefaults.php:29:            'default_timezone' => ['value' => 'America/New_York', 'type' => 'string'],
app/Database/Migrations/2026-02-09-110000_AddControlPlaneSettingsDefaults.php:37:            'default_working_days' => ['value' => '["monday","tuesday","wednesday","thursday","friday"]', 'type' => 'json'],
app/Database/Migrations/2026-02-09-110000_AddControlPlaneSettingsDefaults.php:80:            'default_timezone',
app/Database/Migrations/2026-02-09-110000_AddControlPlaneSettingsDefaults.php:88:            'default_working_days',
app/Services/OnboardingService.php:169:            $this->seedClinicDefaults($clinicId);
app/Services/OnboardingService.php:181:    private function seedClinicDefaults(int $clinicId): void
app/Services/OnboardingService.php:187:            'timezone' => $this->settingsModel->getGlobalSetting('default_timezone', 'America/New_York'),
app/Services/OnboardingService.php:195:            'working_days' => $this->settingsModel->getGlobalSetting('default_working_days', ['monday', 'tuesday', 'wednesday', 'thursday', 'friday']),
app/Views/control_plane/settings.php:205:                                <form action="<?= base_url('controlplane/settings/tenant-defaults') ?>" method="POST" class="space-y-6">
app/Views/control_plane/settings.php:210:                                            <input type="text" name="default_timezone" value="<?= esc($settings['default_timezone'] ?? '') ?>" class="w-full px-4 py-3 bg-white border border-slate-200 rounded-xl text-slate-700 font-medium focus:border-emerald-500 focus:ring-4 focus:ring-emerald-200/60 transition-all" required>
app/Views/control_plane/settings.php:255:                                    <?php $workingDefaults = $settings['default_working_days'] ?? ['monday', 'tuesday', 'wednesday', 'thursday', 'friday']; ?>
app/Views/control_plane/settings.php:272:                                                    <input type="checkbox" name="default_working_days[]" value="<?= esc($dayKey) ?>" class="h-4 w-4 text-emerald-600 border-slate-300 rounded" <?= in_array($dayKey, $workingDefaults, true) ? 'checked' : '' ?>>
app/Controllers/ControlPlane/Settings.php:188:     * POST /controlplane/settings/tenant-defaults
app/Controllers/ControlPlane/Settings.php:193:            'default_timezone' => 'required',
app/Controllers/ControlPlane/Settings.php:207:        $workingDays = $this->request->getPost('default_working_days');
app/Controllers/ControlPlane/Settings.php:216:            'default_timezone' => trim((string) $this->request->getPost('default_timezone')),
app/Controllers/ControlPlane/Settings.php:224:            'default_working_days' => $workingDays,
app/Controllers/ControlPlane/Settings.php:229:            if ($key === 'default_working_days') {
app/Controllers/ControlPlane/Settings.php:238:            'route' => '/controlplane/settings/tenant-defaults',
app/Controllers/ControlPlane/Settings.php:241:                'default_timezone' => $settings['default_timezone'],
app/Controllers/ControlPlane/Settings.php:298:            'default_timezone' => 'America/New_York',
app/Controllers/ControlPlane/Settings.php:306:            'default_working_days' => ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'],
```
