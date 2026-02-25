<?php

namespace App\Services\NotificationDrivers;

use App\Models\PatientWhatsAppModel;
use App\Models\WhatsAppOptoutModel;
use App\Models\WhatsAppTenantPolicyModel;
use App\Services\WhatsAppClient;

class WhatsAppDriver
{
    protected WhatsAppClient $client;
    protected PatientWhatsAppModel $patientWhatsAppModel;
    protected WhatsAppOptoutModel $optoutModel;
    protected WhatsAppTenantPolicyModel $policyModel;

    public function __construct(?WhatsAppClient $client = null)
    {
        $this->client = $client ?: new WhatsAppClient();
        $this->patientWhatsAppModel = new PatientWhatsAppModel();
        $this->optoutModel = new WhatsAppOptoutModel();
        $this->policyModel = new WhatsAppTenantPolicyModel();
    }

    /**
     * Phase 3 outbound MVP:
     * - fail closed if policy suspended / recipient missing / config invalid
     * - call WhatsAppClient for transport and provider error classification
     */
    public function send(array $notification, int $clinicId, array $context = []): array
    {
        $policy = $this->policyModel->where('clinic_id', $clinicId)->first();
        if (!empty($policy['suspended'])) {
            return $this->blocked('WHATSAPP_TENANT_SUSPENDED', (string) ($policy['suspended_reason'] ?? 'WhatsApp tenant policy is suspended.'));
        }

        $channelConfig = is_array($context['config'] ?? null) ? $context['config'] : [];
        if (empty($channelConfig)) {
            return $this->failedPermanent('WA_CHANNEL_CONFIG_MISSING', 'WhatsApp channel config is missing.');
        }

        $recipient = $this->resolveRecipient($notification, $clinicId);
        if (!$recipient['ok']) {
            return [
                'notification_status' => $recipient['notification_status'] ?? 'blocked',
                'failure_reason' => $recipient['failure_reason'] ?? 'WHATSAPP_RECIPIENT_UNAVAILABLE',
                'attempt_status' => $recipient['attempt_status'] ?? 'failed_permanent',
                'error_code' => $recipient['error_code'] ?? 'WHATSAPP_RECIPIENT_UNAVAILABLE',
                'error_message' => $recipient['error_message'] ?? 'WhatsApp recipient could not be resolved in tenant scope.',
                'retryable' => false,
            ];
        }

        if ($this->isOptedOut($clinicId, $recipient['wa_number'], $recipient['bsuid'])) {
            return $this->blocked('WHATSAPP_RECIPIENT_OPTOUT', 'Recipient has opted out of WhatsApp messaging.');
        }

        $payload = json_decode((string) ($notification['payload_json'] ?? '{}'), true) ?? [];
        $transport = $this->sendPayload($clinicId, $channelConfig, (string) $recipient['to'], $payload);

        $attemptStatus = (string) ($transport['attempt_status'] ?? 'failed_permanent');
        $notificationStatus = !empty($transport['retryable']) ? 'failed' : ($attemptStatus === 'sent' ? 'sent' : 'failed');
        if (($transport['failure_reason'] ?? null) === 'WA_131026_AMBIGUOUS_UNDELIVERABLE') {
            $notificationStatus = 'failed';
        }

        return [
            'notification_status' => $notificationStatus,
            'failure_reason' => $this->mapFailureReason($transport),
            'attempt_status' => $attemptStatus,
            'provider_message_id' => $transport['provider_message_id'] ?? null,
            'error_code' => $transport['error_code'] ?? null,
            'error_message' => $transport['error_message'] ?? null,
            'retryable' => (bool) ($transport['retryable'] ?? false),
            'next_retry_at' => $transport['next_retry_at'] ?? null,
        ];
    }

    private function sendPayload(int $clinicId, array $channelConfig, string $to, array $payload): array
    {
        $templateName = trim((string) ($payload['template_name'] ?? ''));
        if ($templateName !== '') {
            $languageCode = trim((string) ($payload['template_language'] ?? 'en_US'));
            $components = is_array($payload['template_components'] ?? null) ? $payload['template_components'] : [];
            return $this->client->sendTemplate($clinicId, $channelConfig, $to, $templateName, $languageCode, $components);
        }

        $messageBody = trim((string) ($payload['message'] ?? $payload['body'] ?? ''));
        if ($messageBody === '') {
            return $this->failedPermanent('WA_MESSAGE_BODY_MISSING', 'WhatsApp payload requires message/body text or template_name.');
        }

        return $this->client->sendText($clinicId, $channelConfig, $to, $messageBody);
    }

    private function resolveRecipient(array $notification, int $clinicId): array
    {
        $recipientType = (string) ($notification['recipient_type'] ?? '');
        $recipientId = (int) ($notification['recipient_id'] ?? 0);
        $recipientAddress = trim((string) ($notification['recipient_address'] ?? ''));

        if ($recipientType === 'external') {
            $normalized = $this->normalizeWhatsAppAddress($recipientAddress);
            if ($normalized === null) {
                return [
                    'ok' => false,
                    'notification_status' => 'blocked',
                    'failure_reason' => 'WHATSAPP_EXTERNAL_ADDRESS_INVALID',
                    'attempt_status' => 'failed_permanent',
                    'error_code' => 'WA_RECIPIENT_INVALID',
                    'error_message' => 'External WhatsApp recipient address is missing or invalid.',
                ];
            }

            return [
                'ok' => true,
                'to' => $normalized['to'],
                'wa_number' => $normalized['wa_number'],
                'bsuid' => $normalized['bsuid'],
            ];
        }

        if ($recipientType === 'patient' && $recipientId > 0) {
            $row = $this->patientWhatsAppModel
                ->where('clinic_id', $clinicId)
                ->where('patient_id', $recipientId)
                ->first();

            if (!$row) {
                return [
                    'ok' => false,
                    'notification_status' => 'blocked',
                    'failure_reason' => 'PATIENT_WHATSAPP_NOT_CONFIGURED',
                    'attempt_status' => 'failed_permanent',
                    'error_code' => 'WA_PATIENT_NOT_CONFIGURED',
                    'error_message' => 'No patient_whatsapp record found for recipient.',
                ];
            }

            $waNumber = trim((string) ($row['wa_number'] ?? ''));
            $bsuid = trim((string) ($row['bsuid'] ?? ''));

            if ($waNumber !== '') {
                $normalized = $this->normalizeWhatsAppAddress($waNumber);
                if ($normalized !== null) {
                    return [
                        'ok' => true,
                        'to' => $normalized['to'],
                        'wa_number' => $normalized['wa_number'],
                        'bsuid' => $bsuid !== '' ? $bsuid : ($normalized['bsuid'] ?? null),
                    ];
                }
            }

            if ($bsuid !== '') {
                return [
                    'ok' => true,
                    'to' => $bsuid,
                    'wa_number' => null,
                    'bsuid' => $bsuid,
                ];
            }

            return [
                'ok' => false,
                'notification_status' => 'blocked',
                'failure_reason' => 'PATIENT_WHATSAPP_IDENTITY_MISSING',
                'attempt_status' => 'failed_permanent',
                'error_code' => 'WA_PATIENT_IDENTITY_MISSING',
                'error_message' => 'patient_whatsapp exists but has no wa_number or bsuid.',
            ];
        }

        // User recipient support can be added once a user_whatsapp registry exists.
        return [
            'ok' => false,
            'notification_status' => 'blocked',
            'failure_reason' => 'USER_WHATSAPP_NOT_CONFIGURED',
            'attempt_status' => 'failed_permanent',
            'error_code' => 'WA_USER_NOT_CONFIGURED',
            'error_message' => 'WhatsApp user recipient resolution is not implemented yet.',
        ];
    }

    private function isOptedOut(int $clinicId, ?string $waNumber, ?string $bsuid): bool
    {
        $builder = $this->optoutModel->where('clinic_id', $clinicId);

        $hasLookup = false;
        $builder->groupStart();
        if (!empty($waNumber)) {
            $builder->where('wa_number', $waNumber);
            $hasLookup = true;
        }
        if (!empty($bsuid)) {
            if ($hasLookup) {
                $builder->orWhere('bsuid', $bsuid);
            } else {
                $builder->where('bsuid', $bsuid);
                $hasLookup = true;
            }
        }
        $builder->groupEnd();

        if (!$hasLookup) {
            return false;
        }

        return $builder->first() !== null;
    }

    private function normalizeWhatsAppAddress(string $value): ?array
    {
        $value = trim($value);
        if ($value === '') {
            return null;
        }

        // If it contains alphabetic chars or starts with '@', treat as username/BSUID-like identifier.
        if (preg_match('/[A-Za-z_@]/', $value)) {
            return [
                'to' => $value,
                'wa_number' => null,
                'bsuid' => $value,
            ];
        }

        $digits = preg_replace('/\D+/', '', $value);
        if ($digits === null || $digits === '' || strlen($digits) < 8 || strlen($digits) > 20) {
            return null;
        }

        return [
            'to' => $digits,
            'wa_number' => $digits,
            'bsuid' => null,
        ];
    }

    private function mapFailureReason(array $transport): ?string
    {
        if (!empty($transport['failure_reason'])) {
            return (string) $transport['failure_reason'];
        }

        $errorCode = (string) ($transport['error_code'] ?? '');
        if ($errorCode === '') {
            return null;
        }

        if ($errorCode === '131056') {
            return 'WA_PAIR_RATE_LIMITED';
        }

        if ($errorCode === '131026') {
            return 'WA_131026_AMBIGUOUS_UNDELIVERABLE';
        }

        return substr('WA_SEND_FAILED:' . $errorCode, 0, 100);
    }

    private function blocked(string $failureReason, string $message): array
    {
        return [
            'notification_status' => 'blocked',
            'failure_reason' => $failureReason,
            'attempt_status' => 'failed_permanent',
            'error_code' => $failureReason,
            'error_message' => substr($message, 0, 255),
            'retryable' => false,
        ];
    }

    private function failedPermanent(string $errorCode, string $message): array
    {
        return [
            'notification_status' => 'failed',
            'failure_reason' => substr('WA_SEND_FAILED:' . $errorCode, 0, 100),
            'attempt_status' => 'failed_permanent',
            'error_code' => $errorCode,
            'error_message' => substr($message, 0, 255),
            'retryable' => false,
        ];
    }
}
