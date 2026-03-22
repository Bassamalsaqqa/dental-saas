<?php

namespace App\Services;

use App\Libraries\ApiRuntimeContext;
use App\Models\ClinicModel;
use App\Models\ClinicNotificationChannelModel;
use App\Models\NotificationAttemptModel;
use App\Models\NotificationModel;
use App\Models\PlatformSecretModel;

class ControlPlaneWhatsAppTestSendService
{
    protected ClinicModel $clinicModel;
    protected ClinicNotificationChannelModel $channelModel;
    protected PlatformSecretModel $platformSecretModel;
    protected NotificationModel $notificationModel;
    protected NotificationAttemptModel $attemptModel;
    protected DispatchService $dispatchService;
    protected PlanGuard $planGuard;

    public function __construct()
    {
        $this->clinicModel = new ClinicModel();
        $this->channelModel = new ClinicNotificationChannelModel();
        $this->platformSecretModel = new PlatformSecretModel();
        $this->notificationModel = new NotificationModel();
        $this->attemptModel = new NotificationAttemptModel();
        $this->dispatchService = new DispatchService();
        $this->planGuard = new PlanGuard();
    }

    public function send(array $input, ?int $actorUserId = null): array
    {
        $clinicId = (int) ($input['clinic_id'] ?? 0);
        if ($clinicId <= 0) {
            return $this->fail('Clinic is required.');
        }

        $clinic = $this->clinicModel->find($clinicId);
        if (!$clinic) {
            return $this->fail('Selected clinic was not found.');
        }

        $channel = $this->channelModel
            ->withoutTenantScope()
            ->where('clinic_id', $clinicId)
            ->where('channel_type', 'whatsapp')
            ->first();
        $channelConfig = $this->channelModel->withoutTenantScope()->getConfig($clinicId, 'whatsapp') ?? [];

        if (!$channel || empty($channel['configured_by_clinic'])) {
            return $this->fail('WhatsApp channel is not configured for the selected clinic.');
        }

        if (empty($channel['enabled_by_superadmin'])) {
            return $this->fail('WhatsApp is disabled by the platform administrator for the selected clinic.');
        }

        if (empty($channel['validated'])) {
            return $this->fail('WhatsApp channel is saved but not validated yet for the selected clinic.');
        }

        if (!$this->planGuard->isFeatureEnabled($clinicId, 'notifications.whatsapp.enabled', false)) {
            return $this->fail('The selected clinic plan does not allow WhatsApp notifications.');
        }

        $secretErrors = $this->validatePlatformSecrets($channelConfig);
        if (!empty($secretErrors)) {
            return $this->fail(implode(' ', $secretErrors));
        }

        $recipient = $this->normalizeTargetNumber((string) ($input['target_number'] ?? ''));
        if ($recipient === null) {
            return $this->fail('Enter a valid WhatsApp number in international format (example: +970599999999).');
        }

        $payloadResult = $this->buildPayload($input);
        if (!$payloadResult['ok']) {
            return $this->fail((string) $payloadResult['message']);
        }

        $notificationId = $this->notificationModel->createLedgerEntry([
            'clinic_id' => $clinicId,
            'channel_type' => 'whatsapp',
            'recipient_type' => 'external',
            'recipient_id' => null,
            'recipient_address' => $recipient['address'],
            'payload_json' => json_encode($payloadResult['payload'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'status' => 'pending',
            'failure_reason' => null,
            'initiated_by_user_id' => $actorUserId,
        ]);

        if (!$notificationId) {
            return $this->fail('Failed to create the WhatsApp test notification ledger entry.');
        }

        $originalClinicId = ApiRuntimeContext::getClinicId();

        try {
            ApiRuntimeContext::setClinicId($clinicId);
            $this->dispatchService->dispatchPendingForClinic(
                $clinicId,
                'whatsapp',
                1,
                'cp-whatsapp-test:' . ($actorUserId ?? 'system')
            );
        } finally {
            if ($originalClinicId !== null) {
                ApiRuntimeContext::setClinicId($originalClinicId);
            } else {
                ApiRuntimeContext::clear();
            }
        }

        $notification = $this->notificationModel
            ->withoutTenantScope()
            ->where('id', (int) $notificationId)
            ->first();
        $attempt = $this->attemptModel
            ->withoutTenantScope()
            ->where('notification_id', (int) $notificationId)
            ->orderBy('id', 'DESC')
            ->first();

        if (!is_array($notification)) {
            return $this->fail('Notification was created but could not be reloaded for verification.');
        }

        return [
            'ok' => true,
            'clinic' => $clinic,
            'recipient' => $recipient,
            'notification' => $notification,
            'attempt' => is_array($attempt) ? $attempt : null,
            'send_mode' => $payloadResult['mode'],
        ];
    }

    private function validatePlatformSecrets(array $channelConfig): array
    {
        $errors = [];

        foreach (['wa_access_token', 'wa_app_secret', 'wa_verify_token'] as $secretKey) {
            $value = $this->platformSecretModel->getSecret($secretKey);
            if (!is_string($value) || trim($value) === '') {
                $errors[] = 'Platform secret ' . $secretKey . ' is missing.';
            }
        }

        $configuredPhoneNumberId = trim((string) ($channelConfig['phone_number_id'] ?? $channelConfig['wa_phone_number_id'] ?? ''));
        $globalPhoneNumberId = trim((string) ($this->platformSecretModel->getSecret('wa_phone_number_id') ?? ''));
        if ($configuredPhoneNumberId === '' && $globalPhoneNumberId === '') {
            $errors[] = 'WhatsApp Phone Number ID is missing from both clinic config and platform secrets.';
        }

        return $errors;
    }

    private function normalizeTargetNumber(string $raw): ?array
    {
        $raw = trim($raw);
        if ($raw === '') {
            return null;
        }

        $digits = preg_replace('/\D+/', '', $raw);
        if (!is_string($digits) || $digits === '' || strlen($digits) < 8 || strlen($digits) > 15) {
            return null;
        }

        return [
            'address' => $digits,
            'display' => '+' . $digits,
        ];
    }

    private function buildPayload(array $input): array
    {
        $mode = strtolower(trim((string) ($input['send_mode'] ?? 'text')));
        if (!in_array($mode, ['text', 'template'], true)) {
            return [
                'ok' => false,
                'message' => 'Invalid send mode.',
            ];
        }

        if ($mode === 'text') {
            $message = trim((string) ($input['message'] ?? ''));
            if ($message === '') {
                return [
                    'ok' => false,
                    'message' => 'Message body is required for text test sends.',
                ];
            }

            return [
                'ok' => true,
                'mode' => 'text',
                'payload' => [
                    'message' => $message,
                ],
            ];
        }

        $templateName = trim((string) ($input['template_name'] ?? ''));
        if ($templateName === '') {
            return [
                'ok' => false,
                'message' => 'Template name is required for template test sends.',
            ];
        }

        $templateLanguage = trim((string) ($input['template_language'] ?? 'en_US'));
        if ($templateLanguage === '') {
            $templateLanguage = 'en_US';
        }

        $componentsJson = trim((string) ($input['template_components_json'] ?? ''));
        $components = [];
        if ($componentsJson !== '') {
            try {
                $decoded = json_decode($componentsJson, true, 512, JSON_THROW_ON_ERROR);
            } catch (\JsonException $e) {
                return [
                    'ok' => false,
                    'message' => 'Template components must be valid JSON.',
                ];
            }

            if (!is_array($decoded)) {
                return [
                    'ok' => false,
                    'message' => 'Template components JSON must decode to an array.',
                ];
            }

            $components = $decoded;
        }

        return [
            'ok' => true,
            'mode' => 'template',
            'payload' => [
                'template_name' => $templateName,
                'template_language' => $templateLanguage,
                'template_components' => $components,
            ],
        ];
    }

    private function fail(string $message): array
    {
        return [
            'ok' => false,
            'message' => $message,
        ];
    }
}
