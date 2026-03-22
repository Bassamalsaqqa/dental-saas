<?php

namespace App\Services;

use App\Libraries\ApiRuntimeContext;
use App\Models\ClinicModel;
use App\Models\ClinicNotificationChannelModel;
use App\Models\NotificationAttemptModel;
use App\Models\NotificationModel;
use App\Models\PlatformSecretModel;
use App\Models\SettingsModel;
use App\Services\WhatsAppTriggerCatalog;

class ControlPlaneWhatsAppTestSendService
{
    protected ClinicModel $clinicModel;
    protected ClinicNotificationChannelModel $channelModel;
    protected PlatformSecretModel $platformSecretModel;
    protected NotificationModel $notificationModel;
    protected NotificationAttemptModel $attemptModel;
    protected DispatchService $dispatchService;
    protected PlanGuard $planGuard;
    protected WhatsAppTriggerCatalog $triggerCatalog;
    protected WhatsAppTemplateRegistryService $templateRegistry;
    protected SettingsModel $settingsModel;

    public function __construct()
    {
        $this->clinicModel = new ClinicModel();
        $this->channelModel = new ClinicNotificationChannelModel();
        $this->platformSecretModel = new PlatformSecretModel();
        $this->notificationModel = new NotificationModel();
        $this->attemptModel = new NotificationAttemptModel();
        $this->dispatchService = new DispatchService();
        $this->planGuard = new PlanGuard();
        $this->triggerCatalog = new WhatsAppTriggerCatalog();
        $this->templateRegistry = new WhatsAppTemplateRegistryService();
        $this->settingsModel = new SettingsModel();
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

        $secretErrors = $this->validatePlatformSecrets();
        if (!empty($secretErrors)) {
            return $this->fail(implode(' ', $secretErrors));
        }

        $recipient = $this->normalizeTargetNumber((string) ($input['target_number'] ?? ''));
        if ($recipient === null) {
            return $this->fail('Enter a valid WhatsApp number in international format (example: +970599999999) or a valid BSUID/parent BSUID.');
        }

        $payloadResult = $this->buildPayload($input, $clinicId);
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

    private function validatePlatformSecrets(): array
    {
        $errors = [];

        foreach (['wa_access_token', 'wa_app_secret', 'wa_verify_token'] as $secretKey) {
            $value = $this->platformSecretModel->getSecret($secretKey);
            if (!is_string($value) || trim($value) === '') {
                $errors[] = 'Platform secret ' . $secretKey . ' is missing.';
            }
        }

        $globalPhoneNumberId = trim((string) ($this->platformSecretModel->getSecret('wa_phone_number_id') ?? ''));
        if ($globalPhoneNumberId === '') {
            $errors[] = 'WhatsApp Phone Number ID is missing from platform secrets.';
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

    private function buildPayload(array $input, int $clinicId): array
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
        if (!in_array($templateSource, ['explicit', 'fallback', 'trigger_template'], true)) {
            return [
                'ok' => false,
                'message' => 'Template source is invalid.',
            ];
        }

        $templateName = trim((string) ($input['template_name'] ?? ''));
        $templateLanguage = trim((string) ($input['template_language'] ?? WhatsAppTemplateRegistryService::FORCED_LANGUAGE));

        if ($templateSource === 'fallback') {
            $fallback = $this->templateRegistry->getGlobalFallbackTemplate();
            $templateName = trim((string) ($fallback['name'] ?? ''));
            $templateLanguage = trim((string) ($fallback['language'] ?? WhatsAppTemplateRegistryService::FORCED_LANGUAGE));
            if ($templateName === '') {
                return [
                    'ok' => false,
                    'message' => 'The control plane does not have a fallback template configured.',
                ];
            }
        } elseif ($templateSource === 'trigger_template') {
            $triggerKey = trim((string) ($input['trigger_key'] ?? ''));
            $triggerTemplates = $this->templateRegistry->getGlobalTriggerTemplates();
            $triggerConfig = is_array($triggerTemplates[$triggerKey] ?? null) ? $triggerTemplates[$triggerKey] : null;
            if ($triggerConfig === null) {
                return [
                    'ok' => false,
                    'message' => 'Select a valid workflow trigger template.',
                ];
            }

            $templateName = trim((string) ($triggerConfig['name'] ?? ''));
            $templateLanguage = trim((string) ($triggerConfig['language'] ?? 'ar')) ?: 'ar';
            if ($templateName === '') {
                return [
                    'ok' => false,
                    'message' => 'The control plane does not have an approved template configured for that trigger.',
                ];
            }

            $templateVars = $this->buildTriggerTemplateVarsFromGuidedInput($clinicId, $triggerConfig, $input);
            $components = $this->buildTriggerTemplateComponents($triggerConfig, $templateVars);

            return [
                'ok' => true,
                'mode' => 'template',
                'payload' => [
                    'workflow_trigger' => $triggerKey,
                    'template_name' => $templateName,
                    'template_language' => $templateLanguage,
                    'template_components' => $components,
                    'template_source' => 'trigger_template',
                ],
            ];
        } elseif ($templateName === '') {
            return [
                'ok' => false,
                'message' => 'Template name is required for template test sends.',
            ];
        }

        if ($templateLanguage === '') {
            $templateLanguage = WhatsAppTemplateRegistryService::FORCED_LANGUAGE;
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

    private function buildTriggerTemplateVarsFromGuidedInput(int $clinicId, array $triggerConfig, array $input): array
    {
        $vars = $this->buildClinicTemplateVars($clinicId);

        $headerText = trim((string) ($input['template_header_text'] ?? ''));
        $headerParamKey = trim((string) ($triggerConfig['header_param_key'] ?? ''));
        if ($headerText !== '' && $headerParamKey !== '') {
            $vars[$headerParamKey] = $headerText;
        }

        $bodyParamsRaw = str_replace(["\r\n", "\r"], "\n", (string) ($input['template_body_params'] ?? ''));
        $bodyParams = array_values(array_filter(array_map(
            static fn(string $line): string => trim($line),
            explode("\n", $bodyParamsRaw)
        ), static fn(string $line): bool => $line !== ''));

        $bodyParamKeys = is_array($triggerConfig['body_param_keys'] ?? null) ? $triggerConfig['body_param_keys'] : [];
        foreach ($bodyParamKeys as $index => $key) {
            $cleanKey = trim((string) $key);
            if ($cleanKey === '' || !array_key_exists($index, $bodyParams)) {
                continue;
            }
            $vars[$cleanKey] = $bodyParams[$index];
        }

        $buttonValue = trim((string) ($input['template_button_value'] ?? ''));
        $buttonParamKey = trim((string) ($triggerConfig['button_param_key'] ?? ''));
        if ($buttonValue !== '' && $buttonParamKey !== '') {
            $vars[$buttonParamKey] = $buttonValue;
        }

        return $vars;
    }

    private function buildClinicTemplateVars(int $clinicId): array
    {
        $vars = [
            'clinic_name' => '',
            'clinic_phone' => '',
            'clinic_address' => '',
        ];

        if ($clinicId <= 0) {
            return $vars;
        }

        $settingsRows = $this->settingsModel
            ->withoutTenantScope()
            ->select('setting_key, setting_value')
            ->where('clinic_id', $clinicId)
            ->whereIn('setting_key', ['clinic_name', 'clinic_phone', 'clinic_address'])
            ->findAll();

        foreach ($settingsRows as $row) {
            $key = (string) ($row['setting_key'] ?? '');
            if (array_key_exists($key, $vars)) {
                $vars[$key] = trim((string) ($row['setting_value'] ?? ''));
            }
        }

        if ($vars['clinic_name'] === '') {
            $clinic = $this->clinicModel->find($clinicId);
            $vars['clinic_name'] = trim((string) ($clinic['name'] ?? ''));
        }

        return $vars;
    }

    private function buildTriggerTemplateComponents(array $triggerConfig, array $templateVars): array
    {
        $components = [];

        $headerParamKey = trim((string) ($triggerConfig['header_param_key'] ?? ''));
        if ($headerParamKey !== '' && array_key_exists($headerParamKey, $templateVars)) {
            $components[] = [
                'type' => 'header',
                'parameters' => [[
                    'type' => 'text',
                    'text' => (string) $templateVars[$headerParamKey],
                ]],
            ];
        }

        $bodyParameters = [];
        foreach (($triggerConfig['body_param_keys'] ?? []) as $key) {
            $cleanKey = trim((string) $key);
            if ($cleanKey === '' || !array_key_exists($cleanKey, $templateVars)) {
                continue;
            }

            $bodyParameters[] = [
                'type' => 'text',
                'text' => (string) $templateVars[$cleanKey],
            ];
        }

        if ($bodyParameters !== []) {
            $components[] = [
                'type' => 'body',
                'parameters' => $bodyParameters,
            ];
        }

        $buttonParamKey = trim((string) ($triggerConfig['button_param_key'] ?? ''));
        $buttonType = trim((string) ($triggerConfig['button_type'] ?? ''));
        if ($buttonParamKey !== '' && $buttonType !== '' && array_key_exists($buttonParamKey, $templateVars)) {
            $parameterType = $buttonType === 'url' ? 'text' : 'payload';
            $parameter = ['type' => $parameterType];
            $parameter[$parameterType === 'text' ? 'text' : 'payload'] = (string) $templateVars[$buttonParamKey];

            $components[] = [
                'type' => 'button',
                'sub_type' => $buttonType,
                'index' => ctype_digit((string) ($triggerConfig['button_index'] ?? '0')) ? (string) $triggerConfig['button_index'] : '0',
                'parameters' => [$parameter],
            ];
        }

        return $components;
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
                    $parameterType === 'text' ? 'text' : 'payload' => $buttonValue,
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
