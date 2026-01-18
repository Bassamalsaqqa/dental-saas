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

        return $response->setStatusCode($statusCode)->setJSON($data);
    }
}
