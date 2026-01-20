<?php

if (!function_exists('api_error')) {
    /**
     * Return a contract-compliant JSON error response.
     */
    function api_error(int $statusCode, string $code, string $message, array $details = [])
    {
        $response = service('response');
        $data = [
            'error' => [
                'code'    => $code,
                'message' => $message
            ]
        ];

        if (!empty($details)) {
            $data['error']['details'] = $details;
        }

        // Ensure secure headers are present on API error responses.
        $response->setHeader('X-Frame-Options', 'SAMEORIGIN');
        $response->setHeader('X-Content-Type-Options', 'nosniff');
        $response->setHeader('Referrer-Policy', 'same-origin');
        $response->setHeader('X-Permitted-Cross-Domain-Policies', 'none');

        return $response->setStatusCode($statusCode)->setJSON($data);
    }
}
