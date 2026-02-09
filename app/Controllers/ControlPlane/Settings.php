<?php

namespace App\Controllers\ControlPlane;

use App\Controllers\BaseController;
use App\Models\ControlPlaneAuditModel;
use App\Models\EmailTemplateModel;
use App\Models\SettingsModel;
use App\Services\ControlPlaneAuditService;
use App\Services\RetentionService;

class Settings extends BaseController
{
    protected $auditService;
    protected $settingsModel;

    public function __construct()
    {
        $this->auditService = new ControlPlaneAuditService();
        $this->settingsModel = new SettingsModel();
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

        return view('control_plane/settings', [
            'title' => 'Settings',
            'settings' => $settings,
        ]);
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

        return redirect()->to(base_url('controlplane/email-templates?template_id=' . $templateId . '#template-editor'))
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

        return redirect()->to(base_url('controlplane/email-templates?template_id=' . $id . '#template-editor'))
            ->with('success', 'Email template updated.');
    }
}
