<?php

namespace Tests\Unit;

use App\Services\WhatsAppClient;
use CodeIgniter\Test\CIUnitTestCase;

final class WhatsAppClientTest extends CIUnitTestCase
{
    public function testClassifyResponseExtractsProviderMessageIdOnSuccess(): void
    {
        $client = new WhatsAppClient();
        $response = service('response')
            ->setStatusCode(200)
            ->setBody(json_encode([
                'messages' => [['id' => 'wamid.success.123']],
            ]));

        $result = $this->invokePrivateMethod($client, 'classifyResponse', [$response]);

        $this->assertSame('sent', $result['attempt_status']);
        $this->assertSame('wamid.success.123', $result['provider_message_id']);
        $this->assertFalse($result['retryable']);
    }

    public function testClassifyResponseMarks131056RetryableWithMinimumBackoff(): void
    {
        $client = new WhatsAppClient();
        $now = time();
        $response = service('response')
            ->setStatusCode(400)
            ->setBody(json_encode([
                'error' => [
                    'code' => 131056,
                    'message' => 'Rate limit hit',
                ],
            ]));

        $result = $this->invokePrivateMethod($client, 'classifyResponse', [$response]);

        $this->assertSame('rate_limited', $result['attempt_status']);
        $this->assertTrue($result['retryable']);
        $this->assertSame('131056', $result['error_code']);
        $this->assertArrayHasKey('next_retry_at', $result);
        $this->assertGreaterThanOrEqual($now + 60, strtotime((string) $result['next_retry_at']));
    }

    public function testClassifyResponseMarks131026AsTerminalAmbiguousFailure(): void
    {
        $client = new WhatsAppClient();
        $response = service('response')
            ->setStatusCode(400)
            ->setBody(json_encode([
                'error' => [
                    'code' => 131026,
                    'message' => 'Message undeliverable',
                ],
            ]));

        $result = $this->invokePrivateMethod($client, 'classifyResponse', [$response]);

        $this->assertSame('failed_permanent', $result['attempt_status']);
        $this->assertFalse($result['retryable']);
        $this->assertSame('131026', $result['error_code']);
        $this->assertSame('WA_131026_AMBIGUOUS_UNDELIVERABLE', $result['failure_reason']);
    }

    public function testClassifyResponseMarksServerErrorsRetryable(): void
    {
        $client = new WhatsAppClient();
        $response = service('response')
            ->setStatusCode(503)
            ->setBody('{"error":{"message":"Provider unavailable"}}');

        $result = $this->invokePrivateMethod($client, 'classifyResponse', [$response]);

        $this->assertSame('failed', $result['attempt_status']);
        $this->assertTrue($result['retryable']);
        $this->assertArrayHasKey('next_retry_at', $result);
    }

    public function testSendTemplateFailsClosedWhenTemplateNameMissing(): void
    {
        $client = new WhatsAppClient();

        $result = $client->sendTemplate(19, ['phone_number_id' => '123'], '972500000000', '   ');

        $this->assertSame('failed_permanent', $result['attempt_status']);
        $this->assertSame('WA_TEMPLATE_NAME_MISSING', $result['error_code']);
        $this->assertFalse($result['retryable']);
    }

    private function invokePrivateMethod(object $object, string $method, array $args = [])
    {
        $reflection = new \ReflectionClass($object);
        $target = $reflection->getMethod($method);
        $target->setAccessible(true);

        return $target->invokeArgs($object, $args);
    }
}

