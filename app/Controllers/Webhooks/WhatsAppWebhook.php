<?php

namespace App\Controllers\Webhooks;

use App\Controllers\BaseController;
use App\Models\ClinicNotificationChannelModel;
use App\Models\NotificationAttemptModel;
use App\Models\NotificationModel;
use App\Models\PatientWhatsAppModel;
use App\Models\PlatformSecretModel;
use App\Models\WhatsAppDeliveryEventModel;
use App\Models\WhatsAppOptoutModel;

class WhatsAppWebhook extends BaseController
{
    protected PlatformSecretModel $platformSecretModel;
    protected WhatsAppDeliveryEventModel $eventModel;
    protected NotificationAttemptModel $attemptModel;
    protected NotificationModel $notificationModel;
    protected PatientWhatsAppModel $patientWhatsAppModel;
    protected WhatsAppOptoutModel $optoutModel;
    protected ClinicNotificationChannelModel $channelModel;

    public function __construct()
    {
        $this->platformSecretModel = new PlatformSecretModel();
        $this->eventModel = new WhatsAppDeliveryEventModel();
        $this->attemptModel = new NotificationAttemptModel();
        $this->notificationModel = new NotificationModel();
        $this->patientWhatsAppModel = new PatientWhatsAppModel();
        $this->optoutModel = new WhatsAppOptoutModel();
        $this->channelModel = new ClinicNotificationChannelModel();
    }

    /**
     * Meta webhook challenge endpoint.
     * GET /webhooks/whatsapp
     */
    public function challenge()
    {
        $mode = (string) $this->request->getGet('hub_mode');
        if ($mode === '') {
            $mode = (string) $this->request->getGet('hub.mode');
        }

        $token = (string) $this->request->getGet('hub_verify_token');
        if ($token === '') {
            $token = (string) $this->request->getGet('hub.verify_token');
        }

        $challenge = (string) $this->request->getGet('hub_challenge');
        if ($challenge === '') {
            $challenge = (string) $this->request->getGet('hub.challenge');
        }

        if ($mode !== 'subscribe' || $challenge === '') {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'INVALID_CHALLENGE_REQUEST']);
        }

        $verifyToken = $this->platformSecretModel->getSecret('wa_verify_token');
        if ($verifyToken === null || $verifyToken === '') {
            log_message('error', 'WhatsApp webhook challenge failed: wa_verify_token missing');
            return $this->response->setStatusCode(503)->setJSON(['error' => 'WEBHOOK_VERIFY_TOKEN_NOT_CONFIGURED']);
        }

        if (!hash_equals((string) $verifyToken, $token)) {
            return $this->response->setStatusCode(403)->setJSON(['error' => 'INVALID_VERIFY_TOKEN']);
        }

        return $this->response
            ->setStatusCode(200)
            ->setHeader('Content-Type', 'text/plain; charset=UTF-8')
            ->setBody($challenge);
    }

    /**
     * Meta webhook event receiver.
     * POST /webhooks/whatsapp
     */
    public function events()
    {
        $rawBody = (string) $this->request->getBody();
        $signature = trim((string) $this->request->getHeaderLine('X-Hub-Signature-256'));

        $appSecret = $this->platformSecretModel->getSecret('wa_app_secret');
        if ($appSecret === null || $appSecret === '') {
            log_message('error', 'WhatsApp webhook rejected: wa_app_secret missing');
            return $this->response->setStatusCode(503)->setJSON(['error' => 'WEBHOOK_SECRET_NOT_CONFIGURED']);
        }

        if ($signature === '') {
            return $this->response->setStatusCode(403)->setJSON(['error' => 'MISSING_SIGNATURE']);
        }

        $expectedSignature = 'sha256=' . hash_hmac('sha256', $rawBody, (string) $appSecret);
        if (!hash_equals($expectedSignature, $signature)) {
            return $this->response->setStatusCode(403)->setJSON(['error' => 'INVALID_SIGNATURE']);
        }

        $payload = json_decode($rawBody, true);
        if (!is_array($payload)) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'INVALID_JSON']);
        }

        $summary = [
            'journaled' => 0,
            'duplicates' => 0,
            'processed' => 0,
            'status_updates' => 0,
            'optouts_created' => 0,
            'errors' => 0,
        ];

        foreach ($this->extractEvents($payload) as $event) {
            if (!$this->journalEvent($event)) {
                $summary['duplicates']++;
                continue;
            }
            $summary['journaled']++;

            try {
                $this->processEvent($event, $summary);
                $summary['processed']++;
            } catch (\Throwable $e) {
                $summary['errors']++;
                log_message('error', 'WhatsApp webhook event processing failed type={type} provider_event_id={provider_event_id}: {message}', [
                    'type' => $event['event_type'],
                    'provider_event_id' => $event['provider_event_id'],
                    'message' => substr($e->getMessage(), 0, 255),
                ]);
            }
        }

        return $this->response->setStatusCode(200)->setJSON([
            'success' => true,
            'summary' => $summary,
        ]);
    }

    private function extractEvents(array $payload): array
    {
        $events = [];
        $entries = $payload['entry'] ?? [];
        if (!is_array($entries)) {
            return $events;
        }

        foreach ($entries as $entryIndex => $entry) {
            if (!is_array($entry)) {
                continue;
            }

            $changes = $entry['changes'] ?? [];
            if (!is_array($changes)) {
                continue;
            }

            foreach ($changes as $changeIndex => $change) {
                if (!is_array($change)) {
                    continue;
                }

                $field = (string) ($change['field'] ?? '');
                $value = $change['value'] ?? [];
                if ($field !== 'messages' || !is_array($value)) {
                    continue;
                }

                $metadata = is_array($value['metadata'] ?? null) ? $value['metadata'] : [];

                foreach (($value['statuses'] ?? []) as $status) {
                    if (!is_array($status)) {
                        continue;
                    }
                    $providerMessageId = (string) ($status['id'] ?? '');
                    $statusType = strtolower((string) ($status['status'] ?? 'unknown'));
                    $events[] = [
                        'provider_event_id' => $providerMessageId !== '' ? $providerMessageId : $this->fallbackEventId($entryIndex, $changeIndex, 'status', $status),
                        'event_type' => 'status:' . $statusType,
                        'kind' => 'status',
                        'payload' => [
                            'entry' => $entry,
                            'change' => $change,
                            'metadata' => $metadata,
                            'status' => $status,
                        ],
                    ];
                }

                foreach (($value['messages'] ?? []) as $message) {
                    if (!is_array($message)) {
                        continue;
                    }
                    $messageId = (string) ($message['id'] ?? '');
                    $messageType = strtolower((string) ($message['type'] ?? 'unknown'));
                    $events[] = [
                        'provider_event_id' => $messageId !== '' ? $messageId : $this->fallbackEventId($entryIndex, $changeIndex, 'message', $message),
                        'event_type' => 'message:' . $messageType,
                        'kind' => 'message',
                        'payload' => [
                            'entry' => $entry,
                            'change' => $change,
                            'metadata' => $metadata,
                            'message' => $message,
                            'contacts' => is_array($value['contacts'] ?? null) ? $value['contacts'] : [],
                        ],
                    ];
                }
            }
        }

        return $events;
    }

    private function fallbackEventId(int $entryIndex, int $changeIndex, string $kind, array $payload): string
    {
        return substr(hash('sha256', $kind . '|' . $entryIndex . '|' . $changeIndex . '|' . json_encode($payload)), 0, 40);
    }

    private function journalEvent(array $event): bool
    {
        try {
            $this->eventModel->insert([
                'provider_event_id' => (string) $event['provider_event_id'],
                'event_type' => (string) $event['event_type'],
                'payload_json' => json_encode($event['payload']),
                'processed_at' => null,
            ]);
            return true;
        } catch (\Throwable $e) {
            // Duplicate webhook deliveries should be idempotent due to unique key.
            if (stripos($e->getMessage(), 'Duplicate entry') !== false) {
                return false;
            }

            throw $e;
        }
    }

    private function processEvent(array $event, array &$summary): void
    {
        if (($event['kind'] ?? '') === 'status') {
            $updated = $this->processStatusEvent($event);
            if ($updated) {
                $summary['status_updates']++;
            }
        }

        if (($event['kind'] ?? '') === 'message') {
            if ($this->processInboundMessageEvent($event)) {
                $summary['optouts_created']++;
            }
        }

        $this->eventModel
            ->where('provider_event_id', (string) $event['provider_event_id'])
            ->where('event_type', (string) $event['event_type'])
            ->set('processed_at', 'NOW()', false)
            ->update();
    }

    private function processStatusEvent(array $event): bool
    {
        $statusPayload = $event['payload']['status'] ?? [];
        if (!is_array($statusPayload)) {
            return false;
        }

        $providerMessageId = (string) ($statusPayload['id'] ?? '');
        $status = strtolower((string) ($statusPayload['status'] ?? ''));
        if ($providerMessageId === '' || $status === '') {
            return false;
        }

        $attemptModel = $this->attemptModel->withoutTenantScope();
        $attempt = $attemptModel
            ->where('provider_message_id', $providerMessageId)
            ->orderBy('id', 'DESC')
            ->first();

        if (!$attempt) {
            log_message('warning', 'WhatsApp webhook status ignored: no notification_attempt found for provider_message_id={id}', [
                'id' => $providerMessageId,
            ]);
            return false;
        }

        $attemptId = (int) $attempt['id'];
        $clinicId = (int) ($attempt['clinic_id'] ?? 0);
        $notificationId = (int) ($attempt['notification_id'] ?? 0);

        $attemptUpdate = [];
        $notificationTargetStatus = null;

        switch ($status) {
            case 'sent':
                $attemptUpdate['status'] = 'sent';
                $notificationTargetStatus = 'sent';
                break;
            case 'delivered':
                $attemptUpdate['status'] = 'delivered';
                $notificationTargetStatus = 'delivered';
                break;
            case 'read':
                $attemptUpdate['status'] = 'read';
                $notificationTargetStatus = 'read';
                break;
            case 'failed':
            case 'undelivered':
                $attemptUpdate['status'] = 'failed_permanent';
                $errorCode = (string) ($statusPayload['errors'][0]['code'] ?? 'WA_WEBHOOK_DELIVERY_FAILED');
                $errorMessage = (string) ($statusPayload['errors'][0]['title'] ?? $statusPayload['errors'][0]['message'] ?? 'WhatsApp delivery failed');
                $attemptUpdate['error_code'] = substr($errorCode, 0, 64);
                $attemptUpdate['error_message'] = substr($errorMessage, 0, 65535);
                $notificationTargetStatus = 'failed';
                break;
            default:
                return false;
        }

        $attemptModel->update($attemptId, $attemptUpdate);

        if ($notificationId > 0 && $clinicId > 0) {
            $this->applyParentNotificationWebhookStatus($clinicId, $notificationId, $notificationTargetStatus, $attemptUpdate['error_code'] ?? null);
        }

        return true;
    }

    private function applyParentNotificationWebhookStatus(int $clinicId, int $notificationId, ?string $targetStatus, ?string $errorCode = null): void
    {
        if ($targetStatus === null || $targetStatus === '') {
            return;
        }

        $notificationModel = $this->notificationModel->withoutTenantScope();
        $notification = $notificationModel
            ->where('id', $notificationId)
            ->where('clinic_id', $clinicId)
            ->first();

        if (!$notification) {
            return;
        }

        $current = (string) ($notification['status'] ?? '');
        $update = null;

        if (in_array($targetStatus, ['sent', 'delivered', 'read'], true)) {
            $rank = [
                'pending' => 0,
                'processing' => 1,
                'sent' => 2,
                'delivered' => 3,
                'read' => 4,
            ];
            $currentRank = $rank[$current] ?? -1;
            $targetRank = $rank[$targetStatus] ?? -1;
            if ($targetRank > $currentRank) {
                $update = ['status' => $targetStatus];
            }
        } elseif ($targetStatus === 'failed') {
            // Do not downgrade a read notification.
            if ($current !== 'read') {
                $update = [
                    'status' => 'failed',
                    'failure_reason' => substr('WA_WEBHOOK_FAILED:' . ($errorCode ?: 'UNKNOWN'), 0, 100),
                ];
            }
        }

        if ($update !== null) {
            $notificationModel->update($notificationId, $update);
        }
    }

    private function processInboundMessageEvent(array $event): bool
    {
        $message = $event['payload']['message'] ?? [];
        if (!is_array($message)) {
            return false;
        }

        $textBody = strtolower(trim((string) ($message['text']['body'] ?? '')));
        if (!in_array($textBody, ['stop', 'unsubscribe'], true)) {
            return false;
        }

        $waNumber = preg_replace('/\D+/', '', (string) ($message['from'] ?? ''));
        $waNumber = is_string($waNumber) ? $waNumber : '';
        $metadata = $event['payload']['metadata'] ?? [];
        $phoneNumberId = trim((string) ($metadata['phone_number_id'] ?? ''));

        if ($waNumber === '') {
            return false;
        }

        $clinicId = $this->resolveClinicIdByPhoneNumberId($phoneNumberId);
        if ($clinicId <= 0) {
            log_message('warning', 'WhatsApp STOP webhook ignored: clinic could not be resolved for phone_number_id={phone_number_id}', [
                'phone_number_id' => $phoneNumberId,
            ]);
            return false;
        }

        $patientRow = $this->patientWhatsAppModel
            ->withoutTenantScope()
            ->where('clinic_id', $clinicId)
            ->where('wa_number', $waNumber)
            ->first();

        $optoutModel = $this->optoutModel->withoutTenantScope();
        $exists = $optoutModel
            ->where('clinic_id', $clinicId)
            ->where('wa_number', $waNumber)
            ->first();

        if ($exists) {
            return false;
        }

        $optoutModel->insert([
            'clinic_id' => $clinicId,
            'patient_id' => $patientRow['patient_id'] ?? null,
            'wa_number' => $waNumber,
            'bsuid' => $patientRow['bsuid'] ?? null,
            'source' => 'webhook',
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        return true;
    }

    private function resolveClinicIdByPhoneNumberId(string $phoneNumberId): int
    {
        if ($phoneNumberId === '') {
            return 0;
        }

        $rows = $this->channelModel
            ->withoutTenantScope()
            ->where('channel_type', 'whatsapp')
            ->findAll();

        foreach ($rows as $row) {
            $clinicId = (int) ($row['clinic_id'] ?? 0);
            if ($clinicId <= 0) {
                continue;
            }

            $config = $this->channelModel->getConfig($clinicId, 'whatsapp');
            if (!is_array($config)) {
                continue;
            }

            $configured = trim((string) ($config['phone_number_id'] ?? $config['wa_phone_number_id'] ?? $config['whatsapp_phone_number_id'] ?? ''));
            if ($configured !== '' && hash_equals($configured, $phoneNumberId)) {
                return $clinicId;
            }
        }

        return 0;
    }
}

