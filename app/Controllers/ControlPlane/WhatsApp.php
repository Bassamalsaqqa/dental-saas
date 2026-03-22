<?php

namespace App\Controllers\ControlPlane;

use App\Controllers\BaseController;
use App\Models\ClinicNotificationChannelModel;
use App\Models\NotificationAttemptModel;
use App\Models\PlatformSecretModel;
use App\Models\WhatsAppDeliveryEventModel;
use App\Models\WhatsAppOptoutModel;
use App\Models\WhatsAppTenantPolicyModel;
use App\Services\ControlPlaneAuditService;
use App\Services\ControlPlaneWhatsAppTestSendService;

class WhatsApp extends BaseController
{
    private const ALLOWED_SECRET_KEYS = [
        'wa_access_token',
        'wa_app_secret',
        'wa_verify_token',
        'wa_phone_number_id',
    ];

    protected ControlPlaneAuditService $auditService;

    public function __construct()
    {
        $this->auditService = new ControlPlaneAuditService();
    }

    public function index(string $activeTab = 'dashboard')
    {
        $requestedTab = strtolower((string) ($this->request->getGet('tab') ?? $activeTab));
        if (in_array($requestedTab, ['secrets', 'policies'], true)) {
            return redirect()->to(base_url('controlplane/settings?tab=communications&subtab=whatsapp'));
        }
        if (!in_array($requestedTab, ['dashboard', 'journal'], true)) {
            $requestedTab = 'dashboard';
        }

        $this->auditService->logEvent('surface_get', [
            'route' => '/controlplane/whatsapp',
            'tab' => $requestedTab,
        ]);

        $attemptModel = new NotificationAttemptModel();
        $eventModel = new WhatsAppDeliveryEventModel();
        $optoutModel = new WhatsAppOptoutModel();

        $since = date('Y-m-d H:i:s', time() - 86400);
        $attemptCounts = $attemptModel->getControlPlaneWhatsAppCountsSince($since);
        $events24h = $eventModel->countSinceProcessed($since);
        $optouts24h = $optoutModel->countSince($since);

        $failureRate = $attemptCounts['total'] > 0
            ? round(($attemptCounts['failed'] / $attemptCounts['total']) * 100, 2)
            : 0.0;

        return $this->view('control_plane/whatsapp', [
            'title' => 'WhatsApp Operations',
            'active_tab' => $requestedTab,
            'recent_attempts' => $attemptModel->getControlPlaneRecentWhatsAppAttempts(50),
            'recent_events' => $eventModel->getRecentEvents(50),
            'recent_optouts' => $optoutModel->getControlPlaneRecentOptouts(50),
            'test_send_clinics' => $this->getTestSendClinics(),
            'stats' => [
                'attempts_24h_total' => $attemptCounts['total'],
                'attempts_24h_failed' => $attemptCounts['failed'],
                'attempts_24h_sentish' => $attemptCounts['sentish'],
                'failure_rate_24h' => $failureRate,
                'events_24h' => $events24h,
                'optouts_24h' => $optouts24h,
            ],
        ]);
    }

    public function sendTest()
    {
        $rules = [
            'clinic_id' => 'required|is_natural_no_zero',
            'target_number' => 'required|max_length[32]',
            'send_mode' => 'required|in_list[text,template]',
            'message' => 'permit_empty|max_length[2000]',
            'template_source' => 'permit_empty|in_list[explicit,fallback]',
            'template_name' => 'permit_empty|max_length[120]',
            'template_language' => 'permit_empty|max_length[20]',
            'template_components_json' => 'permit_empty|max_length[4000]',
            'template_header_text' => 'permit_empty|max_length[120]',
            'template_body_params' => 'permit_empty|max_length[2000]',
            'template_button_type' => 'permit_empty|in_list[quick_reply,url]',
            'template_button_index' => 'permit_empty|is_natural',
            'template_button_value' => 'permit_empty|max_length[512]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->to(base_url('controlplane/whatsapp'))
                ->withInput()
                ->with('error', implode(' ', $this->validator->getErrors()));
        }

        $input = [
            'clinic_id' => (int) $this->request->getPost('clinic_id'),
            'target_number' => trim((string) $this->request->getPost('target_number')),
            'send_mode' => trim((string) $this->request->getPost('send_mode')),
            'message' => trim((string) $this->request->getPost('message')),
            'template_source' => trim((string) $this->request->getPost('template_source')),
            'template_name' => trim((string) $this->request->getPost('template_name')),
            'template_language' => trim((string) $this->request->getPost('template_language')),
            'template_components_json' => trim((string) $this->request->getPost('template_components_json')),
            'template_header_text' => trim((string) $this->request->getPost('template_header_text')),
            'template_body_params' => (string) $this->request->getPost('template_body_params'),
            'template_button_type' => trim((string) $this->request->getPost('template_button_type')),
            'template_button_index' => trim((string) $this->request->getPost('template_button_index')),
            'template_button_value' => trim((string) $this->request->getPost('template_button_value')),
        ];

        $service = new ControlPlaneWhatsAppTestSendService();
        $result = $service->send($input, session()->get('user_id') ? (int) session()->get('user_id') : null);

        $auditMetadata = [
            'clinic_id' => $input['clinic_id'],
            'send_mode' => $input['send_mode'],
            'target_number' => $input['target_number'],
            'ok' => $result['ok'] ?? false,
        ];

        if (!$result['ok']) {
            $auditMetadata['message'] = $result['message'] ?? 'Unknown failure';
            $this->auditService->logEvent('controlplane_whatsapp_test_send', [
                'route' => '/controlplane/whatsapp/send-test',
                'method' => 'POST',
                'metadata' => $auditMetadata,
            ]);

            return redirect()->to(base_url('controlplane/whatsapp'))
                ->withInput()
                ->with('error', (string) $result['message']);
        }

        $notification = $result['notification'];
        $attempt = $result['attempt'] ?? null;
        $auditMetadata['notification_id'] = (int) ($notification['id'] ?? 0);
        $auditMetadata['notification_status'] = (string) ($notification['status'] ?? 'unknown');
        $auditMetadata['attempt_id'] = (int) ($attempt['id'] ?? 0);
        $auditMetadata['attempt_status'] = (string) ($attempt['status'] ?? 'none');
        $auditMetadata['provider_message_id'] = (string) ($attempt['provider_message_id'] ?? '');

        $this->auditService->logEvent('controlplane_whatsapp_test_send', [
            'route' => '/controlplane/whatsapp/send-test',
            'method' => 'POST',
            'metadata' => $auditMetadata,
        ]);

        $status = strtoupper((string) ($notification['status'] ?? 'pending'));
        $attemptSuffix = '';
        if (!empty($attempt['provider_message_id'])) {
            $attemptSuffix = ' Provider message id: ' . $attempt['provider_message_id'] . '.';
        } elseif (!empty($attempt['error_code'])) {
            $attemptSuffix = ' Error code: ' . $attempt['error_code'] . '.';
        }

        $successMessage = sprintf(
            'WhatsApp test send queued for clinic %s to %s. Notification #%d status: %s.%s',
            (string) ($result['clinic']['name'] ?? ('#' . $input['clinic_id'])),
            (string) ($result['recipient']['display'] ?? $input['target_number']),
            (int) ($notification['id'] ?? 0),
            $status,
            $attemptSuffix
        );

        return redirect()->to(base_url('controlplane/whatsapp'))
            ->with('success', $successMessage);
    }

    public function rotateSecret()
    {
        $secretKey = trim((string) $this->request->getPost('secret_key'));
        $newValue = trim((string) $this->request->getPost('new_value'));
        $rotationNote = trim((string) $this->request->getPost('rotation_note'));

        if (!in_array($secretKey, self::ALLOWED_SECRET_KEYS, true)) {
            return redirect()->to(base_url('controlplane/settings?tab=communications&subtab=whatsapp'))
                ->with('error', 'Invalid secret key.');
        }

        if ($newValue === '') {
            return redirect()->to(base_url('controlplane/settings?tab=communications&subtab=whatsapp'))
                ->with('error', 'Secret value is required.');
        }

        $secretModel = new PlatformSecretModel();
        $secretModel->setSecret(
            $secretKey,
            $newValue,
            session()->get('user_id') ? (int) session()->get('user_id') : null,
            $rotationNote !== '' ? $rotationNote : null
        );

        $this->auditService->logEvent('controlplane_whatsapp_secret_rotated', [
            'route' => '/controlplane/whatsapp/rotate-secret',
            'method' => 'POST',
            'metadata' => [
                'secret_key' => $secretKey,
            ],
        ]);

        return redirect()->to(base_url('controlplane/settings?tab=communications&subtab=whatsapp'))
            ->with('success', 'Secret rotated successfully.');
    }

    public function updatePolicy()
    {
        $clinicId = (int) $this->request->getPost('clinic_id');
        if ($clinicId <= 0) {
            return redirect()->to(base_url('controlplane/settings?tab=communications&subtab=whatsapp'))
                ->with('error', 'Clinic is required.');
        }

        $rules = [
            'daily_limit' => 'permit_empty|is_natural_no_zero',
            'monthly_limit' => 'permit_empty|is_natural_no_zero',
            'per_patient_daily_limit' => 'permit_empty|is_natural_no_zero',
            'per_hour_limit' => 'permit_empty|is_natural_no_zero',
            'reminder_lead_time_minutes' => 'permit_empty|is_natural_no_zero',
            'suspended_reason' => 'permit_empty|max_length[255]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->to(base_url('controlplane/settings?tab=communications&subtab=whatsapp'))
                ->with('error', implode(' ', $this->validator->getErrors()));
        }

        $policyModel = new WhatsAppTenantPolicyModel();
        $existing = $policyModel->getByClinicForControlPlane($clinicId);

        $payload = [
            'daily_limit' => (int) ($this->request->getPost('daily_limit') ?: ($existing['daily_limit'] ?? 50)),
            'monthly_limit' => (int) ($this->request->getPost('monthly_limit') ?: ($existing['monthly_limit'] ?? 1000)),
            'per_patient_daily_limit' => (int) ($this->request->getPost('per_patient_daily_limit') ?: ($existing['per_patient_daily_limit'] ?? 3)),
            'per_hour_limit' => (int) ($this->request->getPost('per_hour_limit') ?: ($existing['per_hour_limit'] ?? 20)),
            'reminder_lead_time_minutes' => (int) ($this->request->getPost('reminder_lead_time_minutes') ?: ($existing['reminder_lead_time_minutes'] ?? 1440)),
            'suspended' => $this->request->getPost('suspended') ? 1 : 0,
            'suspended_reason' => null,
        ];

        $reason = trim((string) $this->request->getPost('suspended_reason'));
        if ($payload['suspended'] === 1 && $reason !== '') {
            $payload['suspended_reason'] = $reason;
        } elseif ($payload['suspended'] === 1 && !empty($existing['suspended_reason'])) {
            $payload['suspended_reason'] = $existing['suspended_reason'];
        }

        $policyModel->upsertForControlPlane($clinicId, $payload);

        $this->auditService->logEvent('controlplane_whatsapp_policy_updated', [
            'route' => '/controlplane/whatsapp/policy',
            'method' => 'POST',
            'metadata' => [
                'clinic_id' => $clinicId,
                'suspended' => $payload['suspended'],
            ],
        ]);

        return redirect()->to(base_url('controlplane/settings?tab=communications&subtab=whatsapp'))
            ->with('success', 'WhatsApp tenant policy updated.');
    }

    public function setEmergencyPause()
    {
        $clinicId = (int) $this->request->getPost('clinic_id');
        $suspend = $this->request->getPost('suspend') ? 1 : 0;
        $reason = trim((string) $this->request->getPost('suspended_reason'));

        if ($clinicId <= 0) {
            return redirect()->to(base_url('controlplane/settings?tab=communications&subtab=whatsapp'))
                ->with('error', 'Clinic is required.');
        }

        $policyModel = new WhatsAppTenantPolicyModel();
        $existing = $policyModel->getByClinicForControlPlane($clinicId) ?? [];
        $policyModel->upsertForControlPlane($clinicId, [
            'daily_limit' => (int) ($existing['daily_limit'] ?? 50),
            'monthly_limit' => (int) ($existing['monthly_limit'] ?? 1000),
            'per_patient_daily_limit' => (int) ($existing['per_patient_daily_limit'] ?? 3),
            'per_hour_limit' => (int) ($existing['per_hour_limit'] ?? 20),
            'reminder_lead_time_minutes' => (int) ($existing['reminder_lead_time_minutes'] ?? 1440),
            'suspended' => $suspend,
            'suspended_reason' => $suspend ? ($reason !== '' ? $reason : 'Emergency pause set by superadmin') : null,
        ]);

        $this->auditService->logEvent('controlplane_whatsapp_emergency_pause', [
            'route' => '/controlplane/whatsapp/emergency-pause',
            'method' => 'POST',
            'metadata' => [
                'clinic_id' => $clinicId,
                'suspended' => $suspend,
            ],
        ]);

        return redirect()->to(base_url('controlplane/settings?tab=communications&subtab=whatsapp'))
            ->with('success', $suspend ? 'Emergency pause enabled.' : 'Emergency pause cleared.');
    }

    private function getTestSendClinics(): array
    {
        $channelModel = new ClinicNotificationChannelModel();

        return $channelModel
            ->withoutTenantScope()
            ->select('clinic_notification_channels.clinic_id, clinics.name AS clinic_name, clinic_notification_channels.provider_type, clinic_notification_channels.last_tested_at')
            ->join('clinics', 'clinics.id = clinic_notification_channels.clinic_id', 'left')
            ->where('clinic_notification_channels.channel_type', 'whatsapp')
            ->where('clinic_notification_channels.enabled_by_superadmin', 1)
            ->where('clinic_notification_channels.configured_by_clinic', 1)
            ->where('clinic_notification_channels.validated', 1)
            ->orderBy('clinics.name', 'ASC')
            ->findAll();
    }
}
