<?php

namespace App\Controllers\ControlPlane;

use App\Controllers\BaseController;
use App\Models\EmailTemplateModel;
use App\Services\ControlPlaneAuditService;

class Settings extends BaseController
{
    protected $auditService;

    public function __construct()
    {
        $this->auditService = new ControlPlaneAuditService();
    }
    /**
     * GET /controlplane/settings
     */
    public function index()
    {
        $this->auditService->logEvent('surface_get', [
            'route' => '/controlplane/settings'
        ]);

        return view('control_plane/settings', [
            'title' => 'Settings',
        ]);
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
