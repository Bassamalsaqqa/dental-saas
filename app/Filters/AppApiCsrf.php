<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Security\Exceptions\SecurityException;

class AppApiCsrf implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        if (! $request instanceof IncomingRequest) {
            return null;
        }

        try {
            service('security')->verify($request);
        } catch (SecurityException $e) {
            return service('response')
                ->setStatusCode(419, 'CSRF Token Mismatch')
                ->setJSON([
                    'error' => [
                        'code' => 'csrf_failed',
                        'message' => 'Security token expired. Refresh and try again.',
                        'details' => [],
                    ],
                    'meta' => [
                        'request_id' => $this->requestId(),
                    ],
                ]);
        }

        return null;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        return null;
    }

    private function requestId(): string
    {
        return 'req_' . bin2hex(random_bytes(8));
    }
}
