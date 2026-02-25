<?php

namespace Tests\Unit;

use App\Controllers\Webhooks\WhatsAppWebhook;
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
