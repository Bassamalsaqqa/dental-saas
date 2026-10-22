<?php
// GHSA-9g9v-xgwj-697h: actual application client with synthetic transport. No DB or network.
// Publisher workaround: use a fresh nonshared CURLRequest after a failure.
// https://github.com/codeigniter4/CodeIgniter4/security/advisories/GHSA-9g9v-xgwj-697h
namespace CodeIgniter\HTTP {
    interface ResponseInterface {}
    class CURLRequest {
        public int $calls = 0;
        public function request($method, string $url, array $options = []): ResponseInterface {
            ++$this->calls;
            \Harness::$requests[] = [$this, $method, $url, $options];
            $next = array_shift(\Harness::$responses);
            if ($next instanceof \Throwable) { throw $next; }
            return $next;
        }
    }
}
namespace App\Models {
    class PlatformSecretModel {
        public function getSecret(string $key): ?string {
            return ['wa_access_token' => 'synthetic-token', 'wa_phone_number_id' => 'platform', 'wa_app_secret' => 'synthetic-signing'][$key] ?? null;
        }
    }
}
namespace Config {
    class Services {
        public static function curlrequest(array $options = [], $response = null, $config = null, bool $getShared = true) {
            if ($getShared) { throw new \RuntimeException('Shared client requested'); }
            return new \CodeIgniter\HTTP\CURLRequest();
        }
    }
}
namespace {
    function env($key) { return null; }
    function log_message(...$args) {}
    final class Harness {
        public static array $requests = [];
        public static array $responses = [];
        public static int $assertions = 0;
        public static function check(bool $ok, string $label): void {
            ++self::$assertions;
            if (!$ok) { throw new \RuntimeException($label); }
        }
    }
    final class Reply implements \CodeIgniter\HTTP\ResponseInterface {
        public function __construct(private int $status, private string $body) {}
        public function getStatusCode(): int { return $this->status; }
        public function getBody(): string { return $this->body; }
    }
    require dirname(__DIR__) . '/app/Services/WhatsAppClient.php';
    $ok = new Reply(200, '{"messages":[{"id":"synthetic-id"}]}');
    $client = new \App\Services\WhatsAppClient();
    Harness::$responses = [new \RuntimeException('synthetic failure'), $ok];
    $first = $client->sendText(1, ['phone_number_id' => 'clinic'], ['wa_number' => '123'], 'synthetic');
    $second = $client->sendText(1, ['phone_number_id' => 'clinic'], ['wa_number' => '123'], 'synthetic');
    Harness::check($first['error_code'] === 'WA_NETWORK_EXCEPTION', 'failure handled');
    Harness::check($second['attempt_status'] === 'sent', 'later send succeeds');
    Harness::check(Harness::$requests[0][0] !== Harness::$requests[1][0], 'failed default transport not reused');
    Harness::check(Harness::$requests[1][3]['headers']['Authorization'] === 'Bearer synthetic-token', 'explicit token retained');
    Harness::check(Harness::$requests[1][2] === 'https://graph.facebook.com/v22.0/clinic/messages', 'endpoint unchanged');
    Harness::$requests = [];
    Harness::$responses = [new Reply(400, '{"error":{"code":100}}'), $ok];
    $fallback = $client->sendText(1, ['phone_number_id' => 'clinic'], ['wa_number' => '123'], 'synthetic');
    Harness::check($fallback['attempt_status'] === 'sent', 'platform fallback retained');
    Harness::check(Harness::$requests[0][0] !== Harness::$requests[1][0], 'fallback gets fresh transport');
    Harness::check(str_ends_with(Harness::$requests[1][2], '/platform/messages'), 'platform endpoint retained');
    Harness::$requests = [];
    Harness::$responses = [$ok, $ok];
    $injected = new \CodeIgniter\HTTP\CURLRequest();
    $injectedClient = new \App\Services\WhatsAppClient($injected);
    $injectedClient->sendText(1, ['phone_number_id' => 'clinic'], ['wa_number' => '123'], 'synthetic');
    $injectedClient->sendText(1, ['phone_number_id' => 'clinic'], ['wa_number' => '123'], 'synthetic');
    Harness::check($injected->calls === 2, 'injected transport compatibility retained');
    Harness::check(Harness::$requests[0][0] === $injected && Harness::$requests[1][0] === $injected, 'injected instance retained');
    echo 'PASS: ' . Harness::$assertions . " assertions; no DB or network\n";
}
