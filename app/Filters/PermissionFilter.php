<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class PermissionFilter implements FilterInterface
{
    /**
     * Do whatever processing this filter needs to do.
     * By default it should not return anything during
     * normal execution. However, when an abnormal state
     * is found, it should return an instance of
     * CodeIgniter\HTTP\Response. If it does, script
     * execution will end and that Response will be
     * sent back to the client, allowing for error pages,
     * redirects, etc.
     *
     * @param RequestInterface $request
     * @param array|null       $arguments
     *
     * @return mixed
     */
    public function before(RequestInterface $request, $arguments = null)
    {
        $path = trim($request->getUri()->getPath(), '/');
        // strict API segment match: works whether app is hosted at / or /dental-saas/
        $isApi = ($path === 'api/v1' || str_starts_with($path, 'api/v1/'))
            || str_contains($path, '/api/v1/');

        $jsonError = function (int $statusCode, string $code, string $message) {
            $response = service('response');
            return $response->setStatusCode($statusCode)->setJSON([
                'error' => [
                    'code' => $code,
                    'message' => $message,
                ],
            ]);
        };

        // Fail closed if no permission argument is provided
        if (empty($arguments)) {
            log_message('error', 'PermissionFilter: No permission specified in route filter arguments.');

            if ($isApi) {
                return $jsonError(403, 'forbidden', 'Forbidden.');
            }

            return redirect()->to('/')->with('error', 'Access denied (configuration error).');
        }

        $requiredPermission = $arguments[0];
        $action = $arguments[1] ?? 'view';

        // Get the current user
        try {
            $userId = null;
            
            // 1. Try ApiRuntimeContext (Stateless API)
            if ($isApi) {
                $userId = \App\Libraries\ApiRuntimeContext::getUserId();
            }

            // 2. Try IonAuth (Stateful Web)
            if (!$userId && !$isApi) {
                $ionAuth = new \App\Libraries\IonAuth();
                if ($ionAuth->loggedIn()) {
                    $userId = $ionAuth->getUserId();
                }
            }

            if (!$userId) {
                log_message('debug', 'PermissionFilter: No user found, redirecting to login');
                if ($isApi) {
                    return $jsonError(401, 'unauthenticated', 'Authentication required.');
                }

                return redirect()->to('/auth/login');
            }

            log_message('debug', "PermissionFilter: Checking permission {$requiredPermission}/{$action} for user {$userId}");

            // Check if user has the required permission
            $permissionService = new \App\Services\PermissionService();
            $hasPermission = $permissionService->hasPermission($userId, $requiredPermission, $action);

            log_message('debug', "PermissionFilter: Permission check result: " . ($hasPermission ? 'true' : 'false'));

            if (!$hasPermission) {
                // Log the unauthorized access attempt
                log_message('warning', "Unauthorized access attempt by user {$userId} to {$requiredPermission}/{$action}");

                if ($isApi) {
                    return $jsonError(403, 'forbidden', 'Forbidden.');
                }

                // Return 403 Forbidden - redirect to a safe route without permission filters
                return redirect()->to('/')->with('error', 'You do not have permission to access this resource.');
            }
        } catch (\Exception $e) {
            // Log the error and redirect to login if there's an issue
            log_message('error', 'Permission filter error: ' . $e->getMessage());
            log_message('error', 'Permission filter stack trace: ' . $e->getTraceAsString());

            // Fail closed with JSON for API requests
            if ($isApi) {
                return $jsonError(403, 'forbidden', 'Forbidden.');
            }

            return redirect()->to('/')->with('error', 'System error during permission check.');
        }
    }

    /**
     * Allows After filters to inspect and modify the response
     * object as needed. This method does not allow any way
     * to stop execution of other after filters, short of
     * throwing an Exception or Error.
     *
     * @param RequestInterface  $request
     * @param ResponseInterface $response
     * @param array|null        $arguments
     *
     * @return mixed
     */
    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Do nothing
    }
}
