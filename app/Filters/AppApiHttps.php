<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class AppApiHttps implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $arguments ??= [];
        $force = in_array('force', $arguments, true);

        if (! $force && ENVIRONMENT !== 'production') {
            return null;
        }

        if ($request->isSecure()) {
            return null;
        }

        return service('response')
            ->setStatusCode(426, 'Upgrade Required')
            ->setJSON([
                'error' => [
                    'code' => 'https_required',
                    'message' => 'HTTPS is required for App API requests.',
                    'details' => [],
                ],
                'meta' => [
                    'request_id' => $this->requestId(),
                ],
            ]);
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
