<?php

namespace App\Controllers\ControlPlane;

use App\Controllers\BaseController;
use App\Models\ClinicModel;
use App\Models\ControlPlaneAuditModel;
use App\Models\EmailTemplateModel;
use App\Models\PlatformSecretModel;
use App\Models\SettingsModel;
use App\Models\WhatsAppTenantPolicyModel;
use App\Services\ControlPlaneAuditService;
use App\Services\RetentionService;
use App\Services\WhatsAppTemplateRegistryService;

class Settings extends BaseController
{
    protected $auditService;
    protected $settingsModel;
    protected WhatsAppTemplateRegistryService $whatsAppTemplateRegistry;

    public function __construct()
    {
        $this->auditService = new ControlPlaneAuditService();
        $this->settingsModel = new SettingsModel();
        $this->whatsAppTemplateRegistry = new WhatsAppTemplateRegistryService();
    }
    /**
     * GET /controlplane/settings
     */
    public function index()
    {
        $this->auditService->logEvent('surface_get', [
            'route' => '/controlplane/settings'
        ]);

        $settings = $this->getPlatformSettings();
        $request = service('request');

        $statusFilter = strtolower(trim((string) ($request ? $request->getGet('status') : '')));
        $search = trim((string) ($request ? $request->getGet('q') : ''));
        $activeTemplateId = (int) ($request ? $request->getGet('template_id') : 0);
        $templateModel = new EmailTemplateModel();
        $templateQuery = $templateModel->orderBy('id', 'DESC');
        if (in_array($statusFilter, ['active', 'draft'], true)) {
            $templateQuery = $templateQuery->where('status', $statusFilter);
        }
        if ($search !== '') {
            $templateQuery = $templateQuery->groupStart()
                ->like('name', $search)
                ->orLike('slug', $search)
                ->orLike('subject', $search)
                ->groupEnd();
        }
        $templates = $templateQuery->findAll();
        $totalTemplates = $templateModel->countAllResults();
        $activeTemplates = (new EmailTemplateModel())->where('status', 'active')->countAllResults();
        $draftTemplates = (new EmailTemplateModel())->where('status', 'draft')->countAllResults();
        $activeTemplate = $activeTemplateId ? $templateModel->find($activeTemplateId) : null;

        $secretModel = new PlatformSecretModel();
        $secretKeys = ['wa_access_token', 'wa_app_secret', 'wa_verify_token', 'wa_phone_number_id'];
        $secretRows = $secretModel->listMetadata($secretKeys);
        $secretsByKey = array_column($secretRows, null, 'secret_key');
        $waSecrets = [];
        foreach ($secretKeys as $key) {
            $waSecrets[] = $secretsByKey[$key] ?? [
                'secret_key' => $key,
                'rotated_at' => null,
                'rotated_by' => null,
                'rotation_note' => null,
                'created_at' => null,
                'updated_at' => null,
                'missing' => true,
            ];
        }

        $policyModel = new WhatsAppTenantPolicyModel();
        $clinicModel = new ClinicModel();
        $clinics = $clinicModel->select('id, name')->orderBy('name', 'ASC')->findAll();
        $existingPolicies = $policyModel->getControlPlanePoliciesWithClinic(500);
        $policiesById = array_column($existingPolicies, null, 'clinic_id');
        $waPolicies = [];
        foreach ($clinics as $c) {
            $waPolicies[] = $policiesById[$c['id']] ?? [
                'clinic_id' => $c['id'],
                'clinic_name' => $c['name'],
                'daily_limit' => 50,
                'monthly_limit' => 1000,
                'per_patient_daily_limit' => 3,
                'per_hour_limit' => 20,
                'suspended' => 0,
                'suspended_reason' => null,
                'updated_at' => null,
                'created_at' => null,
                'is_virtual' => true,
            ];
        }

        $waGlobalTemplates = $this->whatsAppTemplateRegistry->getGlobalConfig();

        return view('control_plane/settings', [
            'title' => 'Settings',
            'settings' => $settings,
            'templates' => $templates,
            'activeTemplate' => $activeTemplate,
            'templateStats' => [
                'total' => $totalTemplates,
                'active' => $activeTemplates,
                'draft' => $draftTemplates,
                'filtered' => count($templates),
            ],
            'templateFilters' => [
                'status' => $statusFilter,
                'q' => $search,
            ],
            'wa_secrets' => $waSecrets,
            'wa_policies' => $waPolicies,
            'wa_global_templates' => $waGlobalTemplates,
            'wa_trigger_catalog' => (new \App\Services\WhatsAppTriggerCatalog())->all(),
        ]);
    }

    /**
     * POST /controlplane/settings/whatsapp-templates
     */
    public function updateWhatsAppTemplates()
    {
        $triggerCatalog = (new \App\Services\WhatsAppTriggerCatalog())->all();
        $triggerTemplates = [];

        foreach ($triggerCatalog as $triggerKey => $meta) {
            $bodyKeysRaw = trim((string) $this->request->getPost('trigger_templates.' . $triggerKey . '.body_param_keys'));
            $bodyKeys = array_values(array_filter(array_map(
                static fn(string $value): string => trim($value),
                explode(',', $bodyKeysRaw)
            ), static fn(string $value): bool => $value !== ''));

            $triggerTemplates[$triggerKey] = [
                'name' => trim((string) $this->request->getPost('trigger_templates.' . $triggerKey . '.name')),
                'language' => WhatsAppTemplateRegistryService::FORCED_LANGUAGE,
                'header_param_key' => trim((string) $this->request->getPost('trigger_templates.' . $triggerKey . '.header_param_key')),
                'body_param_keys' => $bodyKeys,
                'button_type' => trim((string) $this->request->getPost('trigger_templates.' . $triggerKey . '.button_type')),
                'button_index' => trim((string) $this->request->getPost('trigger_templates.' . $triggerKey . '.button_index')),
                'button_param_key' => trim((string) $this->request->getPost('trigger_templates.' . $triggerKey . '.button_param_key')),
                'reference_header_text' => trim((string) $this->request->getPost('trigger_templates.' . $triggerKey . '.reference_header_text')),
                'reference_body_text' => trim((string) $this->request->getPost('trigger_templates.' . $triggerKey . '.reference_body_text')),
                'reference_button_text' => trim((string) $this->request->getPost('trigger_templates.' . $triggerKey . '.reference_button_text')),
            ];
        }

        $globalTriggerTogglesInput = $this->request->getPost('global_trigger_toggles');
        if (is_array($globalTriggerTogglesInput)) {
            $globalTriggerToggles = [];
            foreach (array_keys($triggerCatalog) as $triggerKey) {
                $globalTriggerToggles[$triggerKey] = !empty($globalTriggerTogglesInput[$triggerKey]) ? 1 : 0;
            }
        } else {
            $globalTriggerToggles = $this->whatsAppTemplateRegistry->getGlobalTriggerToggles();
        }

        $fallbackTemplate = [
            'name' => trim((string) $this->request->getPost('fallback_template.name')),
            'language' => WhatsAppTemplateRegistryService::FORCED_LANGUAGE,
            'components' => [],
            'reference_body_text' => trim((string) $this->request->getPost('fallback_template.reference_body_text')),
        ];

        $saved = $this->whatsAppTemplateRegistry->saveGlobalConfig($triggerTemplates, $fallbackTemplate, $globalTriggerToggles);

        if (!$saved) {
            return redirect()->to(base_url('controlplane/settings?tab=communications&subtab=whatsapp'))
                ->with('error', 'Failed to update global WhatsApp templates.');
        }

        $this->auditService->logEvent('controlplane_whatsapp_templates_updated', [
            'route' => '/controlplane/settings/whatsapp-templates',
            'method' => 'POST',
            'metadata' => [
                'trigger_count' => count($triggerTemplates),
                'enabled_trigger_count' => count(array_filter($globalTriggerToggles, static fn (int $value): bool => $value === 1)),
                'fallback_name' => $fallbackTemplate['name'],
                'language' => WhatsAppTemplateRegistryService::FORCED_LANGUAGE,
            ],
        ]);

        return redirect()->to(base_url('controlplane/settings?tab=communications&subtab=whatsapp'))
            ->with('success', 'Global WhatsApp templates updated.');
    }

    /**
     * POST /controlplane/settings/platform
     */
    public function updatePlatform()
    {
        $rules = [
            'platform_name' => 'required|min_length[2]|max_length[100]',
            'platform_support_email' => 'permit_empty|valid_email|max_length[120]',
            'platform_support_phone' => 'permit_empty|max_length[40]',
            'platform_support_whatsapp_primary' => 'permit_empty|max_length[40]',
            'platform_support_whatsapp_secondary' => 'permit_empty|max_length[40]',
            'platform_support_website' => 'permit_empty|valid_url|max_length[200]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $payload = [
            'platform_name' => trim((string) $this->request->getPost('platform_name')),
            'platform_support_email' => trim((string) $this->request->getPost('platform_support_email')),
            'platform_support_phone' => trim((string) $this->request->getPost('platform_support_phone')),
            'platform_support_whatsapp_primary' => trim((string) $this->request->getPost('platform_support_whatsapp_primary')),
            'platform_support_whatsapp_secondary' => trim((string) $this->request->getPost('platform_support_whatsapp_secondary')),
            'platform_support_website' => trim((string) $this->request->getPost('platform_support_website')),
        ];

        foreach ($payload as $key => $value) {
            $this->settingsModel->setGlobalSetting($key, $value, 'string');
        }

        $this->auditService->logEvent('controlplane_settings_platform_update', [
            'route' => '/controlplane/settings/platform',
            'method' => 'POST',
            'metadata' => [
                'platform_name' => $payload['platform_name'],
            ],
        ]);

        return redirect()->to(base_url('controlplane/settings?tab=platform'))
            ->with('success', 'Platform profile updated.');
    }

    /**
     * POST /controlplane/settings/retention
     */
    public function updateRetention()
    {
        $rules = [
            'retention_mode' => 'required|in_list[latest,keep_last_n,keep_x_days]',
            'retention_last_n' => 'permit_empty|is_natural_no_zero',
            'retention_days' => 'permit_empty|is_natural_no_zero',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $settings = [
            'retention_mode' => $this->request->getPost('retention_mode'),
            'retention_last_n' => (int) ($this->request->getPost('retention_last_n') ?: 5),
            'retention_days' => (int) ($this->request->getPost('retention_days') ?: 30),
        ];

        foreach ($settings as $key => $value) {
            $type = is_numeric($value) ? 'number' : 'string';
            $this->settingsModel->setGlobalSetting($key, $value, $type);
        }

        $this->auditService->logEvent('controlplane_settings_retention_update', [
            'route' => '/controlplane/settings/retention',
            'method' => 'POST',
            'metadata' => [
                'mode' => $settings['retention_mode'],
                'retention_last_n' => $settings['retention_last_n'],
                'retention_days' => $settings['retention_days'],
            ],
        ]);

        return redirect()->to(base_url('controlplane/settings?tab=governance'))
            ->with('success', 'Export retention policy updated.');
    }

    /**
     * POST /controlplane/settings/retention/prune
     */
    public function pruneExports()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(400)->setJSON([
                'success' => false,
                'message' => 'Invalid request.',
            ]);
        }

        try {
            $retentionService = new RetentionService();
            $count = $retentionService->physicalCleanup();

            $this->auditService->logEvent('controlplane_settings_retention_prune', [
                'route' => '/controlplane/settings/retention/prune',
                'method' => 'POST',
                'metadata' => [
                    'deleted_count' => $count,
                ],
            ]);

            return $this->response->setJSON([
                'success' => true,
                'count' => $count,
                'message' => 'Export artifacts pruned.',
            ]);
        } catch (\Throwable $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => $e->getMessage(),
            ]);
        }
    }

    /**
     * POST /controlplane/settings/audit-retention
     */
    public function updateAuditRetention()
    {
        $rules = [
            'control_plane_audit_retention_days' => 'required|is_natural_no_zero',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $days = (int) $this->request->getPost('control_plane_audit_retention_days');
        $this->settingsModel->setGlobalSetting('control_plane_audit_retention_days', $days, 'number');

        $this->auditService->logEvent('controlplane_settings_audit_retention_update', [
            'route' => '/controlplane/settings/audit-retention',
            'method' => 'POST',
            'metadata' => [
                'days' => $days,
            ],
        ]);

        return redirect()->to(base_url('controlplane/settings?tab=governance'))
            ->with('success', 'Audit log retention updated.');
    }

    /**
     * POST /controlplane/settings/tenant-defaults
     */
    public function updateTenantDefaults()
    {
        $rules = [
            'default_timezone' => 'required',
            'default_date_format' => 'required|in_list[Y-m-d,m/d/Y,d/m/Y]',
            'default_time_format' => 'required|in_list[24,12]',
            'default_currency' => 'required|in_list[ILS,USD,JOD,EUR,GBP]',
            'default_currency_position' => 'required|in_list[before,after]',
            'default_appointment_duration' => 'required|integer|greater_than[0]|less_than[480]',
            'default_booking_chair_max' => 'required|integer|greater_than[0]|less_than_equal_to[3]',
            'default_working_hours_start' => 'required|regex_match[/^([01]?[0-9]|2[0-3]):[0-5][0-9]$/]',
            'default_working_hours_end' => 'required|regex_match[/^([01]?[0-9]|2[0-3]):[0-5][0-9]$/]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $workingDays = $this->request->getPost('default_working_days');
        if (!is_array($workingDays)) {
            $workingDays = [];
        }

        $allowedDays = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
        $workingDays = array_values(array_intersect($workingDays, $allowedDays));

        $settings = [
            'default_timezone' => trim((string) $this->request->getPost('default_timezone')),
            'default_date_format' => $this->request->getPost('default_date_format'),
            'default_time_format' => $this->request->getPost('default_time_format'),
            'default_currency' => $this->request->getPost('default_currency'),
            'default_currency_position' => $this->request->getPost('default_currency_position'),
            'default_appointment_duration' => (int) $this->request->getPost('default_appointment_duration'),
            'default_booking_chair_max' => min(3, max(1, (int) $this->request->getPost('default_booking_chair_max'))),
            'default_working_hours_start' => $this->request->getPost('default_working_hours_start'),
            'default_working_hours_end' => $this->request->getPost('default_working_hours_end'),
            'default_working_days' => $workingDays,
        ];

        foreach ($settings as $key => $value) {
            $type = 'string';
            if ($key === 'default_working_days') {
                $type = 'json';
            } elseif (in_array($key, [
                'default_time_format',
                'default_date_format',
                'default_currency',
                'default_currency_position',
                'default_timezone',
                'default_working_hours_start',
                'default_working_hours_end',
            ], true)) {
                $type = 'string';
            } elseif (is_numeric($value)) {
                $type = 'number';
            }
            $this->settingsModel->setGlobalSetting($key, $value, $type);
        }

        $this->auditService->logEvent('controlplane_settings_tenant_defaults_update', [
            'route' => '/controlplane/settings/tenant-defaults',
            'method' => 'POST',
            'metadata' => [
                'default_timezone' => $settings['default_timezone'],
                'default_currency' => $settings['default_currency'],
            ],
        ]);

        return redirect()->to(base_url('controlplane/settings?tab=clinic-defaults'))
            ->with('success', 'Clinic defaults updated for new clinics.');
    }

    /**
     * POST /controlplane/settings/audit-retention/prune
     */
    public function pruneAuditLogs()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(400)->setJSON([
                'success' => false,
                'message' => 'Invalid request.',
            ]);
        }

        $days = (int) $this->settingsModel->getGlobalSetting('control_plane_audit_retention_days', 365);
        $cutoff = date('Y-m-d H:i:s', strtotime("-{$days} days"));

        $auditModel = new ControlPlaneAuditModel();
        $count = $auditModel->where('created_at <', $cutoff)->countAllResults();
        $auditModel->where('created_at <', $cutoff)->delete();

        $this->auditService->logEvent('controlplane_settings_audit_retention_prune', [
            'route' => '/controlplane/settings/audit-retention/prune',
            'method' => 'POST',
            'metadata' => [
                'deleted_count' => $count,
                'cutoff' => $cutoff,
            ],
        ]);

        return $this->response->setJSON([
            'success' => true,
            'count' => $count,
            'message' => 'Audit logs pruned.',
        ]);
    }

    private function getPlatformSettings(): array
    {
        $defaults = [
            'platform_name' => 'DentaCare Cloud',
            'platform_support_email' => '',
            'platform_support_phone' => '',
            'platform_support_whatsapp_primary' => '',
            'platform_support_whatsapp_secondary' => '',
            'platform_support_website' => '',
            'retention_mode' => 'latest',
            'retention_last_n' => 5,
            'retention_days' => 30,
            'control_plane_audit_retention_days' => 365,
            'default_timezone' => 'Asia/Jerusalem',
            'default_date_format' => 'd/m/Y',
            'default_time_format' => '12',
            'default_currency' => 'ILS',
            'default_currency_position' => 'after',
            'default_appointment_duration' => 30,
            'default_booking_chair_max' => 3,
            'default_working_hours_start' => '09:00',
            'default_working_hours_end' => '17:00',
            'default_working_days' => ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'],
        ];

        $stored = $this->settingsModel->withoutTenantScope()->getGlobalSettings();

        return array_merge($defaults, $stored);
    }

    /**
     * GET /controlplane/email-templates
     */
    public function emailTemplates()
    {
        $this->auditService->logEvent('surface_get', [
            'route' => '/controlplane/email-templates'
        ]);

        $request = service('request');
        $statusFilter = strtolower(trim((string) ($request ? $request->getGet('status') : '')));
        $search = trim((string) ($request ? $request->getGet('q') : ''));
        $activeTemplateId = (int) ($request ? $request->getGet('template_id') : 0);

        $templateModel = new EmailTemplateModel();
        $templateQuery = $templateModel->orderBy('id', 'DESC');
        if (in_array($statusFilter, ['active', 'draft'], true)) {
            $templateQuery = $templateQuery->where('status', $statusFilter);
        }
        if ($search !== '') {
            $templateQuery = $templateQuery->groupStart()
                ->like('name', $search)
                ->orLike('slug', $search)
                ->orLike('subject', $search)
                ->groupEnd();
        }

        $templates = $templateQuery->findAll();
        $totalTemplates = $templateModel->countAllResults();
        $activeTemplates = (new EmailTemplateModel())->where('status', 'active')->countAllResults();
        $draftTemplates = (new EmailTemplateModel())->where('status', 'draft')->countAllResults();
        $activeTemplate = null;
        if ($activeTemplateId) {
            $activeTemplate = $templateModel->find($activeTemplateId);
        }

        return view('control_plane/email_templates', [
            'title' => 'Email Templates',
            'templates' => $templates,
            'activeTemplate' => $activeTemplate,
            'templateStats' => [
                'total' => $totalTemplates,
                'active' => $activeTemplates,
                'draft' => $draftTemplates,
                'filtered' => count($templates),
            ],
            'templateFilters' => [
                'status' => $statusFilter,
                'q' => $search,
            ],
        ]);
    }

    /**
     * POST /controlplane/settings/email-templates
     */
    public function storeEmailTemplate()
    {
        $templateModel = new EmailTemplateModel();
        $post = $this->request->getPost();
        $name = trim((string) ($post['name'] ?? ''));
        $subject = trim((string) ($post['subject'] ?? ''));
        $slug = trim((string) ($post['slug'] ?? ''));

        if ($name === '' || $subject === '') {
            return redirect()->back()->with('error', 'Template name and subject are required.');
        }

        if ($slug === '') {
            helper('text');
            $slug = url_title($name, '-', true);
        }

        $existing = $templateModel->where('slug', $slug)->first();
        if ($existing) {
            return redirect()->back()->with('error', 'Template slug already exists.');
        }

        $status = in_array($post['status'] ?? 'draft', ['active', 'draft'], true) ? $post['status'] : 'draft';

        $templateId = $templateModel->insert([
            'name' => $name,
            'slug' => $slug,
            'subject' => $subject,
            'body_html' => trim((string) ($post['body_html'] ?? '')),
            'body_text' => trim((string) ($post['body_text'] ?? '')),
            'status' => $status,
        ]);

        $this->auditService->logEvent('email_template_create', [
            'route' => '/controlplane/settings/email-templates',
            'method' => 'POST',
            'metadata' => ['template_id' => $templateId, 'slug' => $slug],
        ]);

        return redirect()->to(base_url('controlplane/settings?tab=communications&subtab=email-templates&template_id=' . $templateId . '#template-editor'))
            ->with('success', 'Email template created.');
    }

    /**
     * POST /controlplane/settings/email-templates/{id}
     */
    public function updateEmailTemplate(int $id)
    {
        $templateModel = new EmailTemplateModel();
        $template = $templateModel->find($id);
        if (!$template) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Template not found');
        }

        $post = $this->request->getPost();
        $name = trim((string) ($post['name'] ?? ''));
        $subject = trim((string) ($post['subject'] ?? ''));
        $slug = trim((string) ($post['slug'] ?? ''));

        if ($name === '' || $subject === '') {
            return redirect()->back()->with('error', 'Template name and subject are required.');
        }

        if ($slug === '') {
            helper('text');
            $slug = url_title($name, '-', true);
        }

        $existing = $templateModel->where('slug', $slug)->where('id !=', $id)->first();
        if ($existing) {
            return redirect()->back()->with('error', 'Template slug already exists.');
        }

        $status = in_array($post['status'] ?? 'draft', ['active', 'draft'], true) ? $post['status'] : 'draft';

        $templateModel->update($id, [
            'name' => $name,
            'slug' => $slug,
            'subject' => $subject,
            'body_html' => trim((string) ($post['body_html'] ?? '')),
            'body_text' => trim((string) ($post['body_text'] ?? '')),
            'status' => $status,
        ]);

        $this->auditService->logEvent('email_template_update', [
            'route' => '/controlplane/settings/email-templates/' . $id,
            'method' => 'POST',
            'metadata' => ['template_id' => $id, 'slug' => $slug],
        ]);

        return redirect()->to(base_url('controlplane/settings?tab=communications&subtab=email-templates&template_id=' . $id . '#template-editor'))
            ->with('success', 'Email template updated.');
    }
}
