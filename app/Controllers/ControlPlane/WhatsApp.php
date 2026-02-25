<?php

namespace App\Controllers\ControlPlane;

use App\Controllers\BaseController;
use App\Models\ClinicModel;
use App\Models\NotificationAttemptModel;
use App\Models\PlatformSecretModel;
use App\Models\WhatsAppDeliveryEventModel;
use App\Models\WhatsAppOptoutModel;
use App\Models\WhatsAppTenantPolicyModel;
use App\Services\ControlPlaneAuditService;

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

    public function index()
    {
        $this->auditService->logEvent('surface_get', [
            'route' => '/controlplane/whatsapp',
        ]);

        $attemptModel = new NotificationAttemptModel();
        $eventModel = new WhatsAppDeliveryEventModel();
        $optoutModel = new WhatsAppOptoutModel();
        $policyModel = new WhatsAppTenantPolicyModel();
        $clinicModel = new ClinicModel();
        $secretModel = new PlatformSecretModel();

        $since = date('Y-m-d H:i:s', time() - 86400);
        $attemptCounts = $attemptModel->getControlPlaneWhatsAppCountsSince($since);
        $events24h = $eventModel->countSinceProcessed($since);
        $optouts24h = $optoutModel->countSince($since);

        $failureRate = $attemptCounts['total'] > 0
            ? round(($attemptCounts['failed'] / $attemptCounts['total']) * 100, 2)
            : 0.0;

        $secretRows = $secretModel->listMetadata(self::ALLOWED_SECRET_KEYS);
        $secretsByKey = [];
        foreach ($secretRows as $row) {
            $secretsByKey[$row['secret_key']] = $row;
        }

        $secrets = [];
        foreach (self::ALLOWED_SECRET_KEYS as $key) {
            $secrets[] = $secretsByKey[$key] ?? [
                'secret_key' => $key,
                'rotated_at' => null,
                'rotated_by' => null,
                'rotation_note' => null,
                'created_at' => null,
                'updated_at' => null,
                'missing' => true,
            ];
        }

        $clinics = $clinicModel->select('id, name')->orderBy('name', 'ASC')->findAll();

        return $this->view('control_plane/whatsapp', [
            'title' => 'WhatsApp Operations',
            'disableDataTables' => true,
            'recent_attempts' => $attemptModel->getControlPlaneRecentWhatsAppAttempts(50),
            'recent_events' => $eventModel->getRecentEvents(50),
            'recent_optouts' => $optoutModel->getControlPlaneRecentOptouts(50),
            'policies' => $policyModel->getControlPlanePoliciesWithClinic(200),
            'clinics' => $clinics,
            'secrets' => $secrets,
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

    public function rotateSecret()
    {
        $secretKey = trim((string) $this->request->getPost('secret_key'));
        $newValue = trim((string) $this->request->getPost('new_value'));
        $rotationNote = trim((string) $this->request->getPost('rotation_note'));

        if (!in_array($secretKey, self::ALLOWED_SECRET_KEYS, true)) {
            return redirect()->to(base_url('controlplane/whatsapp'))
                ->with('error', 'Invalid secret key.');
        }

        if ($newValue === '') {
            return redirect()->to(base_url('controlplane/whatsapp?tab=secrets'))
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

        return redirect()->to(base_url('controlplane/whatsapp?tab=secrets'))
            ->with('success', 'Secret rotated successfully.');
    }

    public function updatePolicy()
    {
        $clinicId = (int) $this->request->getPost('clinic_id');
        if ($clinicId <= 0) {
            return redirect()->to(base_url('controlplane/whatsapp?tab=policies'))
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
            return redirect()->to(base_url('controlplane/whatsapp?tab=policies'))
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

        return redirect()->to(base_url('controlplane/whatsapp?tab=policies'))
            ->with('success', 'WhatsApp tenant policy updated.');
    }

    public function setEmergencyPause()
    {
        $clinicId = (int) $this->request->getPost('clinic_id');
        $suspend = $this->request->getPost('suspend') ? 1 : 0;
        $reason = trim((string) $this->request->getPost('suspended_reason'));

        if ($clinicId <= 0) {
            return redirect()->to(base_url('controlplane/whatsapp?tab=policies'))
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

        return redirect()->to(base_url('controlplane/whatsapp?tab=policies'))
            ->with('success', $suspend ? 'Emergency pause enabled.' : 'Emergency pause cleared.');
    }
}
