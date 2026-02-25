<?php

namespace Tests\Unit;

use App\Controllers\Webhooks\WhatsAppWebhook;
use App\Models\NotificationAttemptModel;
use App\Models\NotificationModel;
use App\Models\WhatsAppDeliveryEventModel;
use CodeIgniter\Test\CIUnitTestCase;

final class WhatsAppWebhookTest extends CIUnitTestCase
{
    public function testWebhookSignatureValidationAcceptsMatchingHmac(): void
    {
        $controller = new WhatsAppWebhook();
        $rawBody = '{"object":"whatsapp_business_account"}';
        $secret = 'test-app-secret';
        $signature = 'sha256=' . hash_hmac('sha256', $rawBody, $secret);

        $result = $this->invokePrivateMethod($controller, 'isValidWebhookSignature', [$rawBody, $signature, $secret]);

        $this->assertTrue($result);
    }

    public function testWebhookSignatureValidationRejectsInvalidHmac(): void
    {
        $controller = new WhatsAppWebhook();
        $rawBody = '{"object":"whatsapp_business_account"}';
        $secret = 'test-app-secret';

        $result = $this->invokePrivateMethod($controller, 'isValidWebhookSignature', [$rawBody, 'sha256=deadbeef', $secret]);

        $this->assertFalse($result);
    }

    public function testJournalEventReturnsFalseForDuplicateDelivery(): void
    {
        $controller = new WhatsAppWebhook();
        $this->setPrivateProperty($controller, 'eventModel', new class extends WhatsAppDeliveryEventModel {
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

        $result = $this->invokePrivateMethod($controller, 'journalEvent', [$event]);

        $this->assertFalse($result);
    }

    public function testJournalEventRethrowsNonDuplicateInsertError(): void
    {
        $controller = new WhatsAppWebhook();
        $this->setPrivateProperty($controller, 'eventModel', new class extends WhatsAppDeliveryEventModel {
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

        $this->invokePrivateMethod($controller, 'journalEvent', [$event]);
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

        $this->setPrivateProperty($controller, 'attemptModel', $attemptModel);
        $this->setPrivateProperty($controller, 'notificationModel', $notificationModel);

        $event = [
            'payload' => [
                'status' => [
                    'id' => 'wamid-delivered',
                    'status' => 'delivered',
                ],
            ],
        ];

        $result = $this->invokePrivateMethod($controller, 'processStatusEvent', [$event]);

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

        $this->setPrivateProperty($controller, 'attemptModel', $attemptModel);
        $this->setPrivateProperty($controller, 'notificationModel', $notificationModel);

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

        $result = $this->invokePrivateMethod($controller, 'processStatusEvent', [$event]);

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

        $this->setPrivateProperty($controller, 'notificationModel', $notificationModel);

        $this->invokePrivateMethod($controller, 'applyParentNotificationWebhookStatus', [19, 10, 'read', null]);

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

        $this->setPrivateProperty($controller, 'notificationModel', $notificationModel);

        $this->invokePrivateMethod($controller, 'applyParentNotificationWebhookStatus', [19, 11, 'failed', '131047']);

        $this->assertCount(0, $notificationModel->updated);
    }

    private function invokePrivateMethod(object $object, string $method, array $args = [])
    {
        $reflection = new \ReflectionClass($object);
        $target = $reflection->getMethod($method);
        $target->setAccessible(true);

        return $target->invokeArgs($object, $args);
    }

    private function setPrivateProperty(object $object, string $property, $value): void
    {
        $reflection = new \ReflectionClass($object);
        $target = $reflection->getProperty($property);
        $target->setAccessible(true);
        $target->setValue($object, $value);
    }
}
