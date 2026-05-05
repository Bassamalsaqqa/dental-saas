<?php

namespace App\Filters;

use App\Libraries\IonAuth;
use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class AppApiAuth implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        if ((new IonAuth())->loggedIn()) {
            return null;
        }

        return service('response')
            ->setStatusCode(401)
            ->setJSON([
                'error' => [
                    'code' => 'unauthenticated',
                    'message' => 'Authentication required.',
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
