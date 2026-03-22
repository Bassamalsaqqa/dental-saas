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
        $recentAttempts = $attemptModel->getControlPlaneRecentWhatsAppAttempts(50);
        $recentEvents = $eventModel->getRecentEvents(50);
        $recentOptouts = $optoutModel->getControlPlaneRecentOptouts(50);

        $failureRate = $attemptCounts['total'] > 0
            ? round(($attemptCounts['failed'] / $attemptCounts['total']) * 100, 2)
            : 0.0;

        $attemptConsole = $this->buildAttemptConsole($recentAttempts);
        $webhookConsole = $this->buildWebhookConsole($recentEvents);
        $optoutConsole = $this->buildOptoutConsole($recentOptouts);

        return $this->view('control_plane/whatsapp', [
            'title' => 'WhatsApp Operations',
            'active_tab' => $requestedTab,
            'recent_attempts' => $attemptConsole['rows'],
            'recent_events' => $webhookConsole['rows'],
            'recent_optouts' => $optoutConsole,
            'recent_stream' => array_slice($attemptConsole['rows'], 0, 8),
            'needs_attention' => array_slice($attemptConsole['issues'], 0, 8),
            'status_breakdown' => $attemptConsole['status_breakdown'],
            'operator_guidance' => $this->buildOperatorGuidance(),
            'test_send_clinics' => $this->getTestSendClinics(),
            'stats' => [
                'attempts_24h_total' => $attemptCounts['total'],
                'attempts_24h_failed' => $attemptCounts['failed'],
                'attempts_24h_sentish' => $attemptCounts['sentish'],
                'attempts_24h_read' => $attemptCounts['read'] ?? 0,
                'attempts_24h_session_restricted' => $attemptCounts['session_restricted'] ?? 0,
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

    private function buildAttemptConsole(array $rows): array
    {
        $items = [];
        $issues = [];
        $statusBreakdown = [
            'pending' => 0,
            'sent' => 0,
            'delivered' => 0,
            'read' => 0,
            'failed' => 0,
            'blocked' => 0,
        ];

        foreach ($rows as $row) {
            $notificationPayload = $this->decodeJson((string) ($row['payload_json'] ?? ''));
            $recipient = $this->formatRecipient($row);
            $content = $this->describePayload($notificationPayload);
            $attemptStatus = strtolower((string) ($row['status'] ?? 'pending'));
            $notificationStatus = strtolower((string) ($row['notification_status'] ?? 'pending'));
            $effectiveStatus = $this->resolveEffectiveStatus($notificationStatus, $attemptStatus);
            $statusMeta = $this->statusMeta($effectiveStatus);
            $deliveryMeta = $this->deriveDeliveryMeta($row);
            $timestamps = $this->formatTimeline((string) ($row['created_at'] ?? ''));
            $failure = $this->describeFailure($row);

            $item = [
                'attempt_id' => (int) ($row['id'] ?? 0),
                'notification_id' => (int) ($row['notification_id'] ?? 0),
                'clinic_name' => (string) ($row['clinic_name'] ?? 'Unknown clinic'),
                'recipient' => $recipient,
                'content' => $content,
                'attempt_status' => $attemptStatus,
                'notification_status' => $notificationStatus,
                'effective_status' => $effectiveStatus,
                'status_meta' => $statusMeta,
                'delivery_meta' => $deliveryMeta,
                'provider_message_id' => (string) ($row['provider_message_id'] ?? ''),
                'error_code' => (string) ($row['error_code'] ?? ''),
                'error_message' => (string) ($row['error_message'] ?? ''),
                'failure_reason' => (string) ($row['notification_failure_reason'] ?? ''),
                'failure' => $failure,
                'created_at' => (string) ($row['created_at'] ?? ''),
                'created_label' => $timestamps['date_time'],
                'created_relative' => $timestamps['relative'],
            ];

            $items[] = $item;
            $statusBreakdown[$effectiveStatus] = ($statusBreakdown[$effectiveStatus] ?? 0) + 1;

            if ($failure['kind'] !== 'none') {
                $issues[] = $item;
            }
        }

        return [
            'rows' => $items,
            'issues' => $issues,
            'status_breakdown' => $statusBreakdown,
        ];
    }

    private function buildWebhookConsole(array $rows): array
    {
        $items = [];

        foreach ($rows as $row) {
            $payload = $this->decodeJson((string) ($row['payload_json'] ?? ''));
            $summary = $this->summarizeWebhookEvent((string) ($row['event_type'] ?? ''), $payload);
            $timestamps = $this->formatTimeline((string) ($row['processed_at'] ?? ''));

            $items[] = [
                'id' => (int) ($row['id'] ?? 0),
                'event_type' => (string) ($row['event_type'] ?? 'unknown'),
                'summary' => $summary,
                'processed_at' => (string) ($row['processed_at'] ?? ''),
                'processed_label' => $timestamps['date_time'],
                'processed_relative' => $timestamps['relative'],
                'payload_json' => (string) ($row['payload_json'] ?? '{}'),
                'payload_pretty' => json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            ];
        }

        return ['rows' => $items];
    }

    private function buildOptoutConsole(array $rows): array
    {
        $items = [];

        foreach ($rows as $row) {
            $timestamps = $this->formatTimeline((string) ($row['created_at'] ?? ''));
            $items[] = [
                'clinic_name' => (string) ($row['clinic_name'] ?? 'Unknown clinic'),
                'wa_number' => (string) ($row['wa_number'] ?? ''),
                'bsuid' => (string) ($row['bsuid'] ?? ''),
                'parent_bsuid' => (string) ($row['parent_bsuid'] ?? ''),
                'created_at' => (string) ($row['created_at'] ?? ''),
                'created_label' => $timestamps['date_time'],
                'created_relative' => $timestamps['relative'],
            ];
        }

        return $items;
    }

    private function buildOperatorGuidance(): array
    {
        return [
            [
                'title' => 'BSUID, parent BSUID, and username',
                'body' => 'These identifiers come from Meta webhook traffic. The app stores them automatically when WhatsApp sends inbound messages, status payloads, or user_id_update events. Many clinics will never need to look at them directly unless you are debugging recipient identity changes.',
            ],
            [
                'title' => '24-hour session window',
                'body' => 'Free-form text messages work only inside the active customer service window. If a send fails with Meta code 131047, the operator should send an approved template or wait for the customer to reply first.',
            ],
            [
                'title' => 'Template-first testing',
                'body' => 'Use template mode for out-of-session testing and for production-grade business-initiated messaging. Use text mode only when you explicitly know the contact has reopened the conversation window.',
            ],
        ];
    }

    private function decodeJson(string $json): array
    {
        if ($json === '') {
            return [];
        }

        $decoded = json_decode($json, true);
        return is_array($decoded) ? $decoded : [];
    }

    private function formatRecipient(array $row): array
    {
        $recipientType = (string) ($row['recipient_type'] ?? 'external');
        $recipientAddress = trim((string) ($row['recipient_address'] ?? ''));
        $patientName = trim((string) (($row['patient_first_name'] ?? '') . ' ' . ($row['patient_last_name'] ?? '')));
        $recipientId = (int) ($row['recipient_id'] ?? 0);

        if ($recipientType === 'patient') {
            return [
                'label' => $patientName !== '' ? $patientName : ('Patient #' . $recipientId),
                'secondary' => $recipientAddress !== '' ? $this->formatRecipientAddress($recipientAddress) : ('Patient record #' . $recipientId),
            ];
        }

        return [
            'label' => $this->formatRecipientAddress($recipientAddress),
            'secondary' => $recipientType === 'external' ? 'External test recipient' : ucfirst($recipientType),
        ];
    }

    private function formatRecipientAddress(string $value): string
    {
        $value = trim($value);
        if ($value === '') {
            return 'Unknown recipient';
        }

        if (preg_match('/^\d{8,15}$/', $value)) {
            return '+' . $value;
        }

        return $value;
    }

    private function describePayload(array $payload): array
    {
        $templateName = trim((string) ($payload['template_name'] ?? ''));
        if ($templateName !== '') {
            $templateLanguage = trim((string) ($payload['template_language'] ?? ''));
            $templateSource = trim((string) ($payload['template_source'] ?? 'explicit'));

            return [
                'kind' => 'template',
                'title' => $templateName,
                'subtitle' => strtoupper($templateLanguage !== '' ? $templateLanguage : 'N/A') . ' · ' . ($templateSource === 'fallback' ? 'Clinic fallback template' : 'Explicit template'),
                'preview' => $this->templatePreview($payload),
            ];
        }

        $message = trim((string) ($payload['message'] ?? $payload['body'] ?? ''));
        return [
            'kind' => 'text',
            'title' => 'Free-form text',
            'subtitle' => 'Session-window send',
            'preview' => $message !== '' ? mb_strimwidth($message, 0, 120, '...') : 'No message body recorded',
        ];
    }

    private function templatePreview(array $payload): string
    {
        $components = $payload['template_components'] ?? [];
        if (!is_array($components) || $components === []) {
            return 'No explicit template parameters recorded';
        }

        $parts = [];
        foreach ($components as $component) {
            if (!is_array($component)) {
                continue;
            }
            $type = strtolower((string) ($component['type'] ?? 'component'));
            $parameters = $component['parameters'] ?? [];
            if (!is_array($parameters)) {
                $parameters = [];
            }
            $rendered = [];
            foreach ($parameters as $parameter) {
                if (!is_array($parameter)) {
                    continue;
                }
                $rendered[] = (string) ($parameter['text'] ?? $parameter['payload'] ?? '');
            }
            $rendered = array_values(array_filter($rendered, static fn(string $value): bool => trim($value) !== ''));
            if ($rendered !== []) {
                $parts[] = ucfirst($type) . ': ' . implode(' · ', $rendered);
            }
        }

        return $parts !== [] ? implode(' | ', $parts) : 'Template parameters recorded';
    }

    private function resolveEffectiveStatus(string $notificationStatus, string $attemptStatus): string
    {
        foreach ([$notificationStatus, $attemptStatus] as $status) {
            if (in_array($status, ['read', 'delivered', 'sent', 'pending', 'blocked'], true)) {
                return $status;
            }
            if (in_array($status, ['failed', 'failed_permanent', 'rate_limited'], true)) {
                return 'failed';
            }
        }

        return 'pending';
    }

    private function statusMeta(string $status): array
    {
        return match ($status) {
            'read' => ['label' => 'Read', 'class' => 'bg-emerald-100 text-emerald-800 border-emerald-200'],
            'delivered' => ['label' => 'Delivered', 'class' => 'bg-teal-100 text-teal-800 border-teal-200'],
            'sent' => ['label' => 'Sent', 'class' => 'bg-blue-100 text-blue-800 border-blue-200'],
            'blocked' => ['label' => 'Blocked', 'class' => 'bg-amber-100 text-amber-800 border-amber-200'],
            'failed' => ['label' => 'Failed', 'class' => 'bg-rose-100 text-rose-800 border-rose-200'],
            default => ['label' => 'Pending', 'class' => 'bg-slate-100 text-slate-700 border-slate-200'],
        };
    }

    private function deriveDeliveryMeta(array $row): array
    {
        $providerMessageId = trim((string) ($row['provider_message_id'] ?? ''));
        if ($providerMessageId !== '') {
            return [
                'label' => 'Provider message id',
                'value' => $providerMessageId,
            ];
        }

        $errorCode = trim((string) ($row['error_code'] ?? ''));
        if ($errorCode !== '') {
            return [
                'label' => 'Provider error code',
                'value' => $errorCode,
            ];
        }

        return [
            'label' => 'Dispatcher state',
            'value' => strtoupper((string) ($row['status'] ?? 'pending')),
        ];
    }

    private function describeFailure(array $row): array
    {
        $errorCode = trim((string) ($row['error_code'] ?? ''));
        $errorMessage = trim((string) ($row['error_message'] ?? ''));
        $failureReason = trim((string) ($row['notification_failure_reason'] ?? ''));

        if ($errorCode === '131047') {
            return [
                'kind' => 'session_window',
                'title' => 'Outside the 24-hour customer service window',
                'detail' => 'Use an approved template or wait for the customer to reply before retrying free-form text.',
            ];
        }

        if ($failureReason === 'WHATSAPP_RECIPIENT_OPTOUT') {
            return [
                'kind' => 'optout',
                'title' => 'Recipient opted out',
                'detail' => 'Do not retry until the customer explicitly opts back in.',
            ];
        }

        if ($errorCode !== '') {
            return [
                'kind' => 'provider_error',
                'title' => 'Provider rejected the send',
                'detail' => trim('Code ' . $errorCode . ($errorMessage !== '' ? ': ' . $errorMessage : '')),
            ];
        }

        if ($failureReason !== '') {
            return [
                'kind' => 'workflow_error',
                'title' => 'Notification failed before delivery',
                'detail' => $failureReason,
            ];
        }

        return [
            'kind' => 'none',
            'title' => '',
            'detail' => '',
        ];
    }

    private function summarizeWebhookEvent(string $eventType, array $payload): array
    {
        $normalizedType = strtolower($eventType);

        if (str_starts_with($normalizedType, 'status:')) {
            $errors = $payload['errors'][0] ?? [];
            return [
                'title' => ucfirst(substr($eventType, 7)) . ' webhook',
                'detail' => trim((string) ($errors['title'] ?? $errors['message'] ?? 'Delivery status update received from Meta.')),
                'code' => (string) ($errors['code'] ?? ''),
                'subject' => (string) ($payload['recipient_id'] ?? $payload['to'] ?? ''),
            ];
        }

        if ($normalizedType === 'message:inbound') {
            return [
                'title' => 'Inbound message',
                'detail' => mb_strimwidth((string) ($payload['text']['body'] ?? 'Inbound WhatsApp message received.'), 0, 120, '...'),
                'code' => '',
                'subject' => (string) ($payload['from'] ?? $payload['wa_id'] ?? ''),
            ];
        }

        if ($normalizedType === 'user_id_update') {
            return [
                'title' => 'Identity update',
                'detail' => 'Meta sent a BSUID / parent BSUID update for an existing WhatsApp contact.',
                'code' => '',
                'subject' => (string) ($payload['user_id'] ?? $payload['parent_user_id'] ?? ''),
            ];
        }

        return [
            'title' => ucfirst(str_replace(':', ' ', $eventType)),
            'detail' => 'Webhook event stored for audit and replay.',
            'code' => '',
            'subject' => '',
        ];
    }

    private function formatTimeline(string $timestamp): array
    {
        if ($timestamp === '') {
            return [
                'date_time' => 'Unknown time',
                'relative' => '',
            ];
        }

        $unix = strtotime($timestamp);
        if ($unix === false) {
            return [
                'date_time' => $timestamp,
                'relative' => '',
            ];
        }

        $delta = time() - $unix;
        if ($delta < 60) {
            $relative = 'just now';
        } elseif ($delta < 3600) {
            $relative = floor($delta / 60) . 'm ago';
        } elseif ($delta < 86400) {
            $relative = floor($delta / 3600) . 'h ago';
        } else {
            $relative = floor($delta / 86400) . 'd ago';
        }

        return [
            'date_time' => date('Y-m-d H:i:s', $unix),
            'relative' => $relative,
        ];
    }
}
