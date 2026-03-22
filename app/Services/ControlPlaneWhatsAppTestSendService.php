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
            return $this->fail('Enter a valid WhatsApp number in international format (example: +970599999999) or a valid BSUID/parent BSUID.');
        }

        $payloadResult = $this->buildPayload($input, $channelConfig);
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

        if (preg_match('/[A-Za-z_@]/', $raw)) {
            return [
                'address' => $raw,
                'display' => $raw,
            ];
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

    private function buildPayload(array $input, array $channelConfig): array
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

        $templateSource = strtolower(trim((string) ($input['template_source'] ?? 'explicit')));
        if (!in_array($templateSource, ['explicit', 'fallback'], true)) {
            return [
                'ok' => false,
                'message' => 'Template source is invalid.',
            ];
        }

        $templateName = trim((string) ($input['template_name'] ?? ''));
        $templateLanguage = trim((string) ($input['template_language'] ?? 'en_US'));

        if ($templateSource === 'fallback') {
            $fallback = is_array($channelConfig['fallback_template'] ?? null) ? $channelConfig['fallback_template'] : [];
            $templateName = trim((string) ($fallback['name'] ?? ''));
            $templateLanguage = trim((string) ($fallback['language'] ?? 'ar'));
            if ($templateName === '') {
                return [
                    'ok' => false,
                    'message' => 'The selected clinic does not have a fallback template configured.',
                ];
            }
        } elseif ($templateName === '') {
            return [
                'ok' => false,
                'message' => 'Template name is required for template test sends.',
            ];
        }

        if ($templateLanguage === '') {
            $templateLanguage = $templateSource === 'fallback' ? 'ar' : 'en_US';
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
        } else {
            $components = $this->buildGuidedTemplateComponents($input);
        }

        return [
            'ok' => true,
            'mode' => 'template',
            'payload' => [
                'template_name' => $templateName,
                'template_language' => $templateLanguage,
                'template_components' => $components,
                'template_source' => $templateSource,
            ],
        ];
    }

    private function buildGuidedTemplateComponents(array $input): array
    {
        $components = [];

        $headerText = trim((string) ($input['template_header_text'] ?? ''));
        if ($headerText !== '') {
            $components[] = [
                'type' => 'header',
                'parameters' => [[
                    'type' => 'text',
                    'text' => $headerText,
                ]],
            ];
        }

        $bodyParamsRaw = str_replace(["\r\n", "\r"], "\n", (string) ($input['template_body_params'] ?? ''));
        $bodyParams = array_values(array_filter(array_map(
            static fn(string $line): string => trim($line),
            explode("\n", $bodyParamsRaw)
        ), static fn(string $line): bool => $line !== ''));

        if (!empty($bodyParams)) {
            $components[] = [
                'type' => 'body',
                'parameters' => array_map(static fn(string $value): array => [
                    'type' => 'text',
                    'text' => $value,
                ], $bodyParams),
            ];
        }

        $buttonValue = trim((string) ($input['template_button_value'] ?? ''));
        if ($buttonValue !== '') {
            $buttonType = strtolower(trim((string) ($input['template_button_type'] ?? 'quick_reply')));
            if (!in_array($buttonType, ['quick_reply', 'url'], true)) {
                $buttonType = 'quick_reply';
            }

            $buttonIndex = trim((string) ($input['template_button_index'] ?? '0'));
            if ($buttonIndex === '' || !ctype_digit($buttonIndex)) {
                $buttonIndex = '0';
            }

            $parameterType = $buttonType === 'url' ? 'text' : 'payload';
            $components[] = [
                'type' => 'button',
                'sub_type' => $buttonType,
                'index' => $buttonIndex,
                'parameters' => [[
                    'type' => $parameterType,
                    $parameterType === 'url' ? 'text' : 'payload' => $buttonValue,
                ]],
            ];
        }

        return $components;
    }

    private function fail(string $message): array
    {
        return [
            'ok' => false,
            'message' => $message,
        ];
    }
}
