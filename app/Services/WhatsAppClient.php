<?php

namespace App\Services;

use App\Models\PlatformSecretModel;
use CodeIgniter\HTTP\CURLRequest;
use CodeIgniter\HTTP\ResponseInterface;

class WhatsAppClient
{
    protected PlatformSecretModel $platformSecretModel;
    protected CURLRequest $http;
    protected string $baseUrl;
    protected string $defaultApiVersion;
    protected int $defaultRetryDelaySeconds;

    public function __construct(?CURLRequest $http = null)
    {
        $this->platformSecretModel = new PlatformSecretModel();
        $this->http = $http ?: \Config\Services::curlrequest();
        $this->baseUrl = rtrim((string) (env('wa.graph_base_url') ?: 'https://graph.facebook.com'), '/');
        $this->defaultApiVersion = (string) (env('wa.graph_api_version') ?: 'v22.0');
        $this->defaultRetryDelaySeconds = 30;
    }

    /**
     * Sends a WhatsApp text message using Meta Cloud API and returns a dispatcher-compatible transport result.
     *
     * Note:
     * - Uses platform secret `wa_access_token` for outbound sends (Phase 3)
     * - `wa_app_secret` is a separate secret for webhook signature verification (Phase 4)
     */
    public function sendText(int $clinicId, array $channelConfig, string $to, string $messageBody): array
    {
        $requestBody = [
            'messaging_product' => 'whatsapp',
            'recipient_type' => 'individual',
            'to' => $to,
            'type' => 'text',
            'text' => [
                'preview_url' => false,
                'body' => $messageBody,
            ],
        ];

        return $this->sendRequest($clinicId, $channelConfig, $requestBody);
    }

    public function sendTemplate(int $clinicId, array $channelConfig, string $to, string $templateName, string $languageCode = 'en_US', array $components = []): array
    {
        $templateName = trim($templateName);
        if ($templateName === '') {
            return $this->terminalFailure('WA_TEMPLATE_NAME_MISSING', 'WhatsApp template name is required.');
        }

        $languageCode = trim($languageCode) !== '' ? trim($languageCode) : 'en_US';

        $requestBody = [
            'messaging_product' => 'whatsapp',
            'recipient_type' => 'individual',
            'to' => $to,
            'type' => 'template',
            'template' => [
                'name' => $templateName,
                'language' => [
                    'code' => $languageCode,
                ],
            ],
        ];

        if (!empty($components)) {
            $requestBody['template']['components'] = $components;
        }

        return $this->sendRequest($clinicId, $channelConfig, $requestBody);
    }

    private function classifyResponse(ResponseInterface $response): array
    {
        $statusCode = (int) $response->getStatusCode();
        $body = (string) $response->getBody();
        $json = json_decode($body, true);

        if ($statusCode >= 200 && $statusCode < 300) {
            $providerMessageId = null;
            if (is_array($json) && !empty($json['messages'][0]['id'])) {
                $providerMessageId = (string) $json['messages'][0]['id'];
            }

            return [
                'attempt_status' => 'sent',
                'provider_message_id' => $providerMessageId,
                'error_code' => null,
                'error_message' => null,
                'retryable' => false,
            ];
        }

        $providerCode = $this->extractProviderErrorCode($json);
        $providerMessage = $this->extractProviderErrorMessage($json) ?: ('HTTP ' . $statusCode . ' from WhatsApp provider');

        if ($providerCode === '131056') {
            // RL-001 (P1): pair-rate limit retryable with >= 60s backoff.
            return $this->retryableFailure($providerCode, $providerMessage, 60, 'rate_limited');
        }

        if ($providerCode === '131026') {
            // ER-001 (P2): ambiguous undeliverable, do not auto-retry in client.
            return [
                'attempt_status' => 'failed_permanent',
                'provider_message_id' => null,
                'error_code' => $providerCode,
                'error_message' => substr($providerMessage, 0, 255),
                'retryable' => false,
                'failure_reason' => 'WA_131026_AMBIGUOUS_UNDELIVERABLE',
            ];
        }

        if ($providerCode === '130429' || $statusCode === 429) {
            return $this->retryableFailure($providerCode ?: 'WA_RATE_LIMITED', $providerMessage, 30, 'rate_limited');
        }

        if ($statusCode >= 500) {
            return $this->retryableFailure($providerCode ?: 'WA_HTTP_5XX', $providerMessage, $this->defaultRetryDelaySeconds, 'failed');
        }

        // Fail-closed: validation/auth/template errors are non-retryable by default.
        return [
            'attempt_status' => 'failed_permanent',
            'provider_message_id' => null,
            'error_code' => $providerCode ?: ('WA_HTTP_' . $statusCode),
            'error_message' => substr($providerMessage, 0, 255),
            'retryable' => false,
        ];
    }

    private function sendRequest(int $clinicId, array $channelConfig, array $requestBody): array
    {
        $phoneNumberId = trim((string) ($this->firstNonEmpty($channelConfig, [
            'phone_number_id',
            'wa_phone_number_id',
            'whatsapp_phone_number_id',
        ]) ?? ''));

        if ($phoneNumberId === '') {
            return $this->terminalFailure('WA_PHONE_NUMBER_ID_MISSING', 'WhatsApp phone number ID is not configured for this clinic.');
        }

        $accessToken = $this->platformSecretModel->getSecret('wa_access_token');
        if ($accessToken === null || trim($accessToken) === '') {
            return $this->terminalFailure('WA_ACCESS_TOKEN_MISSING', 'Platform secret wa_access_token is missing.');
        }

        if ($this->platformSecretModel->getSecret('wa_app_secret') === null) {
            log_message('warning', 'WhatsAppClient send for clinic_id={clinic_id} without wa_app_secret present in platform_secrets. Phase 4 webhook verification will require it.', [
                'clinic_id' => $clinicId,
            ]);
        }

        $apiVersion = trim((string) ($this->firstNonEmpty($channelConfig, ['graph_api_version', 'api_version']) ?? $this->defaultApiVersion));
        $timeout = max(3, min((int) ($channelConfig['timeout_seconds'] ?? 15), 60));
        $endpoint = $this->baseUrl . '/' . $apiVersion . '/' . rawurlencode($phoneNumberId) . '/messages';

        try {
            $response = $this->http->request('POST', $endpoint, [
                'http_errors' => false,
                'timeout' => $timeout,
                'headers' => [
                    'Authorization' => 'Bearer ' . $accessToken,
                    'Content-Type' => 'application/json',
                ],
                'json' => $requestBody,
            ]);
        } catch (\Throwable $e) {
            return $this->retryableFailure(
                'WA_NETWORK_EXCEPTION',
                substr($e->getMessage(), 0, 255),
                $this->defaultRetryDelaySeconds,
                'failed'
            );
        }

        return $this->classifyResponse($response);
    }

    private function extractProviderErrorCode($json): ?string
    {
        if (!is_array($json)) {
            return null;
        }

        $code = $json['error']['code'] ?? null;
        if ($code === null || $code === '') {
            return null;
        }

        return (string) $code;
    }

    private function extractProviderErrorMessage($json): ?string
    {
        if (!is_array($json)) {
            return null;
        }

        $message = $json['error']['error_user_msg']
            ?? $json['error']['error_data']['details']
            ?? $json['error']['message']
            ?? null;

        if ($message === null) {
            return null;
        }

        return (string) $message;
    }

    private function firstNonEmpty(array $config, array $keys): ?string
    {
        foreach ($keys as $key) {
            if (!array_key_exists($key, $config)) {
                continue;
            }

            $value = trim((string) ($config[$key] ?? ''));
            if ($value !== '') {
                return $value;
            }
        }

        return null;
    }

    private function terminalFailure(string $errorCode, string $message): array
    {
        return [
            'attempt_status' => 'failed_permanent',
            'provider_message_id' => null,
            'error_code' => $errorCode,
            'error_message' => substr($message, 0, 255),
            'retryable' => false,
        ];
    }

    private function retryableFailure(string $errorCode, string $message, int $delaySeconds, string $attemptStatus): array
    {
        $delaySeconds = max(1, $delaySeconds);

        return [
            'attempt_status' => $attemptStatus,
            'provider_message_id' => null,
            'error_code' => $errorCode,
            'error_message' => substr($message, 0, 255),
            'retryable' => true,
            'next_retry_at' => date('Y-m-d H:i:s', time() + $delaySeconds),
        ];
    }
}
