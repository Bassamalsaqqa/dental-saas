<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class CsrfJson implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        // Do nothing
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Check if response is JSON
        if (strpos($response->getHeaderLine('Content-Type'), 'application/json') !== false) {
            $body = $response->getBody();
            // Decode with associative array
            $data = json_decode($body, true);

            // Only inject if it's a valid JSON object/array
            if (json_last_error() === JSON_ERROR_NONE && is_array($data)) {
                // Add new CSRF token
                $data['csrf_token'] = csrf_hash();
                // We use setJSON to handle encoding and headers correctly
                $response->setJSON($data);
            }
        }
    }
}
