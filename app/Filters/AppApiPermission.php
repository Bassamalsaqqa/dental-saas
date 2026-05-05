<?php

namespace App\Filters;

use App\Libraries\IonAuth;
use App\Services\PermissionService;
use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class AppApiPermission implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $arguments = $arguments ?? [];
        $module = (string) ($arguments[0] ?? '');
        $action = (string) ($arguments[1] ?? 'view');

        if ($module === '') {
            log_message('error', 'AppApiPermission: missing route permission argument.');
            return $this->forbidden();
        }

        if (str_contains($module, ':') && count($arguments) === 1) {
            [$module, $action] = array_pad(explode(':', $module, 2), 2, 'view');
        }

        $userId = (int) ((new IonAuth())->getUserId() ?: 0);
        if ($userId <= 0) {
            return $this->error('unauthenticated', 'Authentication required.', 401);
        }

        try {
            if ((new PermissionService())->hasPermission($userId, $module, $action)) {
                return null;
            }
        } catch (\Throwable $e) {
            log_message('error', 'AppApiPermission failed: ' . $e->getMessage());
        }

        return $this->forbidden();
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        return null;
    }

    private function forbidden(): ResponseInterface
    {
        return $this->error('forbidden', 'You do not have permission to access this resource.', 403);
    }

    private function error(string $code, string $message, int $status): ResponseInterface
    {
        return service('response')
            ->setStatusCode($status)
            ->setJSON([
                'error' => [
                    'code' => $code,
                    'message' => $message,
                    'details' => [],
                ],
                'meta' => [
                    'request_id' => $this->requestId(),
                ],
            ]);
    }

    private function requestId(): string
    {
        return 'req_' . bin2hex(random_bytes(8));
    }
}
