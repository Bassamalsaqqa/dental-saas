<?php

namespace Tests\Unit;

use App\Controllers\Webhooks\WhatsAppWebhook;
use App\Models\ClinicNotificationChannelModel;
use App\Models\NotificationAttemptModel;
use App\Models\NotificationModel;
use App\Models\PlatformSecretModel;
use App\Models\WhatsAppDeliveryEventModel;
use App\Models\WhatsAppOptoutModel;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;

final class WhatsAppWebhookTest extends CIUnitTestCase
{
    use FeatureTestTrait;

    public function testWebhookSignatureValidationAcceptsMatchingHmac(): void
    {
        $controller = new WhatsAppWebhook();
        $rawBody = '{"object":"whatsapp_business_account"}';
        $secret = 'test-app-secret';
        $signature = 'sha256=' . hash_hmac('sha256', $rawBody, $secret);

        $result = $this->invokePrivateMethodLocal($controller, 'isValidWebhookSignature', [$rawBody, $signature, $secret]);

        $this->assertTrue($result);
    }

    public function testWebhookSignatureValidationRejectsInvalidHmac(): void
    {
        $controller = new WhatsAppWebhook();
        $rawBody = '{"object":"whatsapp_business_account"}';
        $secret = 'test-app-secret';

        $result = $this->invokePrivateMethodLocal($controller, 'isValidWebhookSignature', [$rawBody, 'sha256=deadbeef', $secret]);

        $this->assertFalse($result);
    }

    public function testJournalEventReturnsFalseForDuplicateDelivery(): void
    {
        $controller = new WhatsAppWebhook();
        $this->setPrivatePropertyLocal($controller, 'eventModel', new class extends WhatsAppDeliveryEventModel {
            public function insert($row = null, bool $returnID = true)
            {
                throw new \RuntimeException("Duplicate entry 'wamid-123' for key 'provider_event_id'");
            }
        });

        $event = [
            'provider_event_id' => 'wamid-123',
            'event_type' => 'status:delivered',
            'payload' => ['status' => ['id' => 'wamid-123', 'status' => 'delivered']],
        ];

        $result = $this->invokePrivateMethodLocal($controller, 'journalEvent', [$event]);

        $this->assertFalse($result);
    }

    public function testJournalEventRethrowsNonDuplicateInsertError(): void
    {
        $controller = new WhatsAppWebhook();
        $this->setPrivatePropertyLocal($controller, 'eventModel', new class extends WhatsAppDeliveryEventModel {
            public function insert($row = null, bool $returnID = true)
            {
                throw new \RuntimeException('Database connection lost');
            }
        });

        $event = [
            'provider_event_id' => 'wamid-999',
            'event_type' => 'status:failed',
            'payload' => ['status' => ['id' => 'wamid-999', 'status' => 'failed']],
        ];

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Database connection lost');

        $this->invokePrivateMethodLocal($controller, 'journalEvent', [$event]);
    }

    public function testProcessStatusEventMapsDeliveredAndPromotesParentNotification(): void
    {
        $controller = new WhatsAppWebhook();

        $attemptModel = new class extends NotificationAttemptModel {
            public array $row = [
                'id' => 77,
                'clinic_id' => 19,
                'notification_id' => 501,
                'provider_message_id' => 'wamid-delivered',
                'status' => 'sent',
            ];
            public array $updated = [];

            public function withoutTenantScope()
            {
                return $this;
            }

            public function where($key = null, $value = null, ?bool $escape = null)
            {
                return $this;
            }

            public function orderBy(?string $orderBy = null, string $direction = '', ?bool $escape = null)
            {
                return $this;
            }

            public function first()
            {
                return $this->row;
            }

            public function update($id = null, $row = null): bool
            {
                $this->updated[] = ['id' => $id, 'row' => $row];
                return true;
            }
        };

        $notificationModel = new class extends NotificationModel {
            public array $row = [
                'id' => 501,
                'clinic_id' => 19,
                'status' => 'sent',
            ];
            public array $updated = [];

            public function withoutTenantScope()
            {
                return $this;
            }

            public function where($key = null, $value = null, ?bool $escape = null)
            {
                return $this;
            }

            public function first()
            {
                return $this->row;
            }

            public function update($id = null, $row = null): bool
            {
                $this->updated[] = ['id' => $id, 'row' => $row];
                return true;
            }
        };

        $this->setPrivatePropertyLocal($controller, 'attemptModel', $attemptModel);
        $this->setPrivatePropertyLocal($controller, 'notificationModel', $notificationModel);

        $event = [
            'payload' => [
                'status' => [
                    'id' => 'wamid-delivered',
                    'status' => 'delivered',
                ],
            ],
        ];

        $result = $this->invokePrivateMethodLocal($controller, 'processStatusEvent', [$event]);

        $this->assertTrue($result);
        $this->assertSame('delivered', $attemptModel->updated[0]['row']['status']);
        $this->assertSame('delivered', $notificationModel->updated[0]['row']['status']);
    }

    public function testProcessStatusEventMapsFailureCodeAndMarksParentFailed(): void
    {
        $controller = new WhatsAppWebhook();

        $attemptModel = new class extends NotificationAttemptModel {
            public array $row = [
                'id' => 88,
                'clinic_id' => 19,
                'notification_id' => 777,
                'provider_message_id' => 'wamid-failed',
                'status' => 'sent',
            ];
            public array $updated = [];

            public function withoutTenantScope()
            {
                return $this;
            }

            public function where($key = null, $value = null, ?bool $escape = null)
            {
                return $this;
            }

            public function orderBy(?string $orderBy = null, string $direction = '', ?bool $escape = null)
            {
                return $this;
            }

            public function first()
            {
                return $this->row;
            }

            public function update($id = null, $row = null): bool
            {
                $this->updated[] = ['id' => $id, 'row' => $row];
                return true;
            }
        };

        $notificationModel = new class extends NotificationModel {
            public array $row = [
                'id' => 777,
                'clinic_id' => 19,
                'status' => 'sent',
            ];
            public array $updated = [];

            public function withoutTenantScope()
            {
                return $this;
            }

            public function where($key = null, $value = null, ?bool $escape = null)
            {
                return $this;
            }

            public function first()
            {
                return $this->row;
            }

            public function update($id = null, $row = null): bool
            {
                $this->updated[] = ['id' => $id, 'row' => $row];
                return true;
            }
        };

        $this->setPrivatePropertyLocal($controller, 'attemptModel', $attemptModel);
        $this->setPrivatePropertyLocal($controller, 'notificationModel', $notificationModel);

        $event = [
            'payload' => [
                'status' => [
                    'id' => 'wamid-failed',
                    'status' => 'failed',
                    'errors' => [
                        [
                            'code' => '131047',
                            'title' => 'Re-engagement message',
                        ],
                    ],
                ],
            ],
        ];

        $result = $this->invokePrivateMethodLocal($controller, 'processStatusEvent', [$event]);

        $this->assertTrue($result);
        $this->assertSame('failed_permanent', $attemptModel->updated[0]['row']['status']);
        $this->assertSame('131047', $attemptModel->updated[0]['row']['error_code']);
        $this->assertSame('Re-engagement message', $attemptModel->updated[0]['row']['error_message']);
        $this->assertSame('failed', $notificationModel->updated[0]['row']['status']);
        $this->assertSame('WA_WEBHOOK_FAILED:131047', $notificationModel->updated[0]['row']['failure_reason']);
    }

    public function testApplyParentNotificationWebhookStatusPromotesForwardOnly(): void
    {
        $controller = new WhatsAppWebhook();

        $notificationModel = new class extends NotificationModel {
            public array $row = [
                'id' => 10,
                'clinic_id' => 19,
                'status' => 'processing',
            ];
            public array $updated = [];

            public function withoutTenantScope()
            {
                return $this;
            }

            public function where($key = null, $value = null, ?bool $escape = null)
            {
                return $this;
            }

            public function first()
            {
                return $this->row;
            }

            public function update($id = null, $row = null): bool
            {
                $this->updated[] = ['id' => $id, 'row' => $row];
                return true;
            }
        };

        $this->setPrivatePropertyLocal($controller, 'notificationModel', $notificationModel);

        $this->invokePrivateMethodLocal($controller, 'applyParentNotificationWebhookStatus', [19, 10, 'read', null]);

        $this->assertCount(1, $notificationModel->updated);
        $this->assertSame('read', $notificationModel->updated[0]['row']['status']);
    }

    public function testApplyParentNotificationWebhookStatusDoesNotDowngradeReadToFailed(): void
    {
        $controller = new WhatsAppWebhook();

        $notificationModel = new class extends NotificationModel {
            public array $row = [
                'id' => 11,
                'clinic_id' => 19,
                'status' => 'read',
            ];
            public array $updated = [];

            public function withoutTenantScope()
            {
                return $this;
            }

            public function where($key = null, $value = null, ?bool $escape = null)
            {
                return $this;
            }

            public function first()
            {
                return $this->row;
            }

            public function update($id = null, $row = null): bool
            {
                $this->updated[] = ['id' => $id, 'row' => $row];
                return true;
            }
        };

        $this->setPrivatePropertyLocal($controller, 'notificationModel', $notificationModel);

        $this->invokePrivateMethodLocal($controller, 'applyParentNotificationWebhookStatus', [19, 11, 'failed', '131047']);

        $this->assertCount(0, $notificationModel->updated);
    }

    public function testWebhookChallengeEndpointReturnsChallengeWhenVerifyTokenMatches(): void
    {
        $secretModel = new PlatformSecretModel();
        $originalVerifyToken = $secretModel->getSecret('wa_verify_token');
        $testVerifyToken = 'unit-test-verify-token';

        $secretModel->setSecret('wa_verify_token', $testVerifyToken, null, 'phpunit challenge test');

        try {
            $response = $this->get('/webhooks/whatsapp?hub.mode=subscribe&hub.verify_token=' . rawurlencode($testVerifyToken) . '&hub.challenge=123456789');

            $response->assertStatus(200);
            $this->assertSame('123456789', (string) $response->response()->getBody());
        } finally {
            if (is_string($originalVerifyToken) && $originalVerifyToken !== '') {
                $secretModel->setSecret('wa_verify_token', $originalVerifyToken, null, 'phpunit restore');
            }
        }
    }

    public function testWebhookEventsEndpointRejectsMissingSignature(): void
    {
        $this->ensureWebhookAppSecretForTests();

        $response = $this
            ->withHeaders(['Content-Type' => 'application/json'])
            ->withBody('{"object":"whatsapp_business_account","entry":[]}')
            ->post('/webhooks/whatsapp');

        $response->assertStatus(403);
        $response->assertJSONFragment(['error' => 'MISSING_SIGNATURE']);
    }

    public function testWebhookEventsEndpointRejectsInvalidSignature(): void
    {
        $this->ensureWebhookAppSecretForTests();

        $response = $this
            ->withHeaders([
                'Content-Type' => 'application/json',
                'X-Hub-Signature-256' => 'sha256=deadbeef',
            ])
            ->withBody('{"object":"whatsapp_business_account","entry":[]}')
            ->post('/webhooks/whatsapp');

        $response->assertStatus(403);
        $response->assertJSONFragment(['error' => 'INVALID_SIGNATURE']);
    }

    public function testWebhookEventsEndpointAcceptsValidSignedNoopPayload(): void
    {
        $appSecret = $this->ensureWebhookAppSecretForTests();
        $payload = '{"object":"whatsapp_business_account","entry":[]}';
        $signature = 'sha256=' . hash_hmac('sha256', $payload, $appSecret);

        $response = $this
            ->withHeaders([
                'Content-Type' => 'application/json',
                'X-Hub-Signature-256' => $signature,
            ])
            ->withBody($payload)
            ->post('/webhooks/whatsapp');

        $response->assertStatus(200);
        $response->assertJSONFragment(['success' => true]);
        $json = json_decode((string) $response->getJSON(), true);
        $this->assertIsArray($json);
        $this->assertSame(0, $json['summary']['journaled'] ?? null);
        $this->assertSame(0, $json['summary']['duplicates'] ?? null);
    }

    public function testWebhookEventsEndpointIsIdempotentForDuplicateDelivery(): void
    {
        $appSecret = $this->ensureWebhookAppSecretForTests();
        $providerEventId = 'phpunit-webhook-' . date('YmdHis') . '-' . substr(bin2hex(random_bytes(4)), 0, 8);
        $payloadArray = [
            'object' => 'whatsapp_business_account',
            'entry' => [
                [
                    'id' => 'entry-1',
                    'changes' => [
                        [
                            'field' => 'messages',
                            'value' => [
                                'metadata' => ['phone_number_id' => 'does-not-resolve'],
                                'statuses' => [
                                    [
                                        'id' => $providerEventId,
                                        'status' => 'delivered',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
        $payload = json_encode($payloadArray);
        $this->assertIsString($payload);
        $signature = 'sha256=' . hash_hmac('sha256', $payload, $appSecret);

        $eventModel = new WhatsAppDeliveryEventModel();

        try {
            $eventModel->where('provider_event_id', $providerEventId)->delete();

            $first = $this
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'X-Hub-Signature-256' => $signature,
                ])
                ->withBody($payload)
                ->post('/webhooks/whatsapp');

            $first->assertStatus(200);
            $firstJson = json_decode((string) $first->getJSON(), true);
            $this->assertIsArray($firstJson);
            $this->assertSame(1, $firstJson['summary']['journaled'] ?? null);
            $this->assertSame(0, $firstJson['summary']['duplicates'] ?? null);

            $second = $this
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'X-Hub-Signature-256' => $signature,
                ])
                ->withBody($payload)
                ->post('/webhooks/whatsapp');

            $second->assertStatus(200);
            $secondJson = json_decode((string) $second->getJSON(), true);
            $this->assertIsArray($secondJson);
            $this->assertSame(0, $secondJson['summary']['journaled'] ?? null);
            $this->assertSame(1, $secondJson['summary']['duplicates'] ?? null);

            $rows = $eventModel->where('provider_event_id', $providerEventId)->findAll();
            $this->assertCount(1, $rows);
        } finally {
            $eventModel->where('provider_event_id', $providerEventId)->delete();
        }
    }

    public function testWebhookEventsEndpointCreatesOptoutOnInboundStop(): void
    {
        [$clinicId, $phoneNumberId] = $this->findResolvableWhatsAppChannelForWebhookTest();
        if ($clinicId <= 0 || $phoneNumberId === '') {
            $this->markTestSkipped('No resolvable WhatsApp channel config with phone_number_id found for webhook STOP test.');
        }

        $appSecret = $this->ensureWebhookAppSecretForTests();
        $waNumberDigits = '9725' . substr((string) time(), -6) . random_int(10, 99);
        $providerEventId = 'phpunit-stop-' . substr(bin2hex(random_bytes(6)), 0, 12);

        $payloadArray = [
            'object' => 'whatsapp_business_account',
            'entry' => [[
                'id' => 'entry-stop',
                'changes' => [[
                    'field' => 'messages',
                    'value' => [
                        'metadata' => ['phone_number_id' => $phoneNumberId],
                        'contacts' => [[
                            'wa_id' => $waNumberDigits,
                            'profile' => ['name' => 'PHPUnit Stop'],
                        ]],
                        'messages' => [[
                            'id' => $providerEventId,
                            'from' => $waNumberDigits,
                            'type' => 'text',
                            'text' => ['body' => 'STOP'],
                        ]],
                    ],
                ]],
            ]],
        ];
        $payload = json_encode($payloadArray);
        $this->assertIsString($payload);
        $signature = 'sha256=' . hash_hmac('sha256', $payload, $appSecret);

        $eventModel = new WhatsAppDeliveryEventModel();
        $optoutModel = new WhatsAppOptoutModel();

        try {
            $eventModel->where('provider_event_id', $providerEventId)->delete();
            $optoutModel->withoutTenantScope()->where('clinic_id', $clinicId)->where('wa_number', $waNumberDigits)->delete();

            $response = $this
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'X-Hub-Signature-256' => $signature,
                ])
                ->withBody($payload)
                ->post('/webhooks/whatsapp');

            $response->assertStatus(200);
            $json = json_decode((string) $response->getJSON(), true);
            $this->assertIsArray($json);
            $this->assertSame(1, $json['summary']['journaled'] ?? null);
            $this->assertSame(1, $json['summary']['processed'] ?? null);
            $this->assertSame(1, $json['summary']['optouts_created'] ?? null);

            $optout = $optoutModel->withoutTenantScope()
                ->where('clinic_id', $clinicId)
                ->where('wa_number', $waNumberDigits)
                ->first();

            $this->assertIsArray($optout);
            $this->assertSame('webhook', $optout['source']);
        } finally {
            $optoutModel->withoutTenantScope()->where('clinic_id', $clinicId)->where('wa_number', $waNumberDigits)->delete();
            $eventModel->where('provider_event_id', $providerEventId)->delete();
        }
    }

    public function testWebhookEventsEndpointUpdatesAttemptAndNotificationFromStatusCallback(): void
    {
        [$clinicId] = $this->findResolvableWhatsAppChannelForWebhookTest();
        if ($clinicId <= 0) {
            $this->markTestSkipped('No WhatsApp-enabled clinic found for status callback endpoint test.');
        }

        $appSecret = $this->ensureWebhookAppSecretForTests();
        $providerMessageId = 'wamid.phpunit.' . substr(bin2hex(random_bytes(8)), 0, 16);

        $notificationModel = new NotificationModel();
        $attemptModel = new NotificationAttemptModel();
        $eventModel = new WhatsAppDeliveryEventModel();

        $notificationId = 0;
        $attemptId = 0;

        try {
            $notificationId = (int) $notificationModel->withoutTenantScope()->insert([
                'clinic_id' => $clinicId,
                'channel_type' => 'whatsapp',
                'recipient_type' => 'external',
                'recipient_address' => '+10000000000',
                'payload_json' => json_encode(['message' => 'PHPUnit status webhook test']),
                'status' => 'processing',
                'locked_at' => null,
                'locked_by' => null,
                'initiated_by_user_id' => null,
                'job_audit_id' => null,
                'parent_notification_id' => null,
            ]);
            $this->assertGreaterThan(0, $notificationId);

            $attemptId = (int) $attemptModel->withoutTenantScope()->insert([
                'clinic_id' => $clinicId,
                'notification_id' => $notificationId,
                'channel_type' => 'whatsapp',
                'status' => 'sent',
                'provider_message_id' => $providerMessageId,
                'idempotency_key' => 'phpunit-' . substr(bin2hex(random_bytes(6)), 0, 12),
                'retry_count' => 0,
                'max_retries' => 3,
                'next_retry_at' => null,
            ]);
            $this->assertGreaterThan(0, $attemptId);

            $payloadArray = [
                'object' => 'whatsapp_business_account',
                'entry' => [[
                    'id' => 'entry-status',
                    'changes' => [[
                        'field' => 'messages',
                        'value' => [
                            'statuses' => [[
                                'id' => $providerMessageId,
                                'status' => 'delivered',
                            ]],
                        ],
                    ]],
                ]],
            ];
            $payload = json_encode($payloadArray);
            $this->assertIsString($payload);
            $signature = 'sha256=' . hash_hmac('sha256', $payload, $appSecret);

            $response = $this
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'X-Hub-Signature-256' => $signature,
                ])
                ->withBody($payload)
                ->post('/webhooks/whatsapp');

            $response->assertStatus(200);
            $json = json_decode((string) $response->getJSON(), true);
            $this->assertIsArray($json);
            $this->assertSame(1, $json['summary']['journaled'] ?? null);
            $this->assertSame(1, $json['summary']['processed'] ?? null);
            $this->assertSame(1, $json['summary']['status_updates'] ?? null);

            $attempt = $attemptModel->withoutTenantScope()->where('id', $attemptId)->first();
            $notification = $notificationModel->withoutTenantScope()->where('id', $notificationId)->first();

            $this->assertIsArray($attempt);
            $this->assertIsArray($notification);
            $this->assertSame('delivered', $attempt['status']);
            $this->assertSame('delivered', $notification['status']);

            $eventRows = $eventModel->where('provider_event_id', $providerMessageId)->findAll();
            $this->assertCount(1, $eventRows);
            $this->assertNotNull($eventRows[0]['processed_at']);
        } finally {
            if ($attemptId > 0) {
                $attemptModel->withoutTenantScope()->where('id', $attemptId)->delete();
            }
            if ($notificationId > 0) {
                $notificationModel->withoutTenantScope()->where('id', $notificationId)->delete();
            }
            $eventModel->where('provider_event_id', $providerMessageId)->delete();
        }
    }

    private function invokePrivateMethodLocal(object $object, string $method, array $args = [])
    {
        $reflection = new \ReflectionClass($object);
        $target = $reflection->getMethod($method);
        $target->setAccessible(true);

        return $target->invokeArgs($object, $args);
    }

    private function setPrivatePropertyLocal(object $object, string $property, $value): void
    {
        $reflection = new \ReflectionClass($object);
        $target = $reflection->getProperty($property);
        $target->setAccessible(true);
        $target->setValue($object, $value);
    }

    private function ensureWebhookAppSecretForTests(): string
    {
        $secretModel = new PlatformSecretModel();
        $secret = $secretModel->getSecret('wa_app_secret');

        if (!is_string($secret) || $secret === '') {
            $secret = 'phpunit-fallback-wa-app-secret';
            $secretModel->setSecret('wa_app_secret', $secret, null, 'phpunit webhook tests');
        }

        return $secret;
    }

    /**
     * @return array{0:int,1:string}
     */
    private function findResolvableWhatsAppChannelForWebhookTest(): array
    {
        $channelModel = new ClinicNotificationChannelModel();
        $rows = $channelModel->withoutTenantScope()
            ->where('channel_type', 'whatsapp')
            ->findAll();

        $encrypter = \Config\Services::encrypter();

        foreach ($rows as $row) {
            if (!is_array($row)) {
                continue;
            }

            $clinicId = (int) ($row['clinic_id'] ?? 0);
            $configEncrypted = (string) ($row['config_encrypted'] ?? '');
            if ($clinicId <= 0 || $configEncrypted === '') {
                continue;
            }

            try {
                $decoded = base64_decode($configEncrypted, true);
                if ($decoded === false) {
                    continue;
                }
                $json = (string) $encrypter->decrypt($decoded);
                $config = json_decode($json, true);
            } catch (\Throwable $e) {
                continue;
            }

            if (!is_array($config)) {
                continue;
            }

            $phoneNumberId = trim((string) ($config['phone_number_id'] ?? $config['wa_phone_number_id'] ?? $config['whatsapp_phone_number_id'] ?? ''));
            if ($phoneNumberId !== '') {
                return [$clinicId, $phoneNumberId];
            }
        }

        return [0, ''];
    }
}
