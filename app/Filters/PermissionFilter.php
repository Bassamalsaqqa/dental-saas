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
        $isApiV1 = (bool) preg_match('#(^|/)api/v1(/|$)#i', $path);
        $isApi = (bool) preg_match('#(^|/)api(/|$)#i', $path);

        $wantsJson = $isApi
            || ($request->hasHeader('Accept') && str_contains($request->getHeaderLine('Accept'), 'application/json'))
            || ($request->hasHeader('X-Requested-With') && strtolower($request->getHeaderLine('X-Requested-With')) === 'xmlhttprequest');

        $jsonError = static function (int $statusCode, string $code, string $message) {
            $response = service('response');
            return $response->setStatusCode($statusCode)->setJSON([
                'error' => [
                    'code' => $code,
                    'message' => $message,
                ],
            ]);
        };

        // Parse module and action strictly before any authority check
        $parsed = $this->parsePermissionArguments($arguments);
        if ($parsed === null) {
            log_message('error', 'PermissionFilter: Malformed or missing permission filter argument specification: ' . json_encode($arguments));
            if ($wantsJson) {
                return $jsonError(403, 'forbidden', 'Forbidden.');
            }
            return redirect()->to('/')->with('error', 'Access denied (configuration error).');
        }

        [$module, $action] = $parsed;

        // Get the current user
        try {
            $userId = null;
            
            // 1. Only /api/v1/* uses runtime actor (never IonAuth/session fallback)
            if ($isApiV1) {
                $userId = \App\Libraries\ApiRuntimeContext::getUserId();
            } else {
                // 2. Stateful Web or First-Party Session (e.g. /api/search)
                $ionAuth = new \App\Libraries\IonAuth();
                if ($ionAuth->loggedIn()) {
                    $userId = $ionAuth->getUserId();
                }
            }

            if (!$userId) {
                log_message('debug', 'PermissionFilter: No user found');
                if ($wantsJson) {
                    return $jsonError(401, 'unauthenticated', 'Authentication required.');
                }

                return redirect()->to('/auth/login');
            }

            log_message('debug', "PermissionFilter: Checking permission {$module}/{$action} for user {$userId}");

            // Check if user has the required permission
            $permissionService = service('permission');
            if (!$permissionService) {
                $permissionService = new \App\Services\PermissionService();
            }
            $hasPermission = $permissionService->hasPermission($userId, $module, $action);

            log_message('debug', "PermissionFilter: Permission check result: " . ($hasPermission ? 'true' : 'false'));

            if (!$hasPermission) {
                log_message('warning', "Unauthorized access attempt by user {$userId} to {$module}/{$action}");

                if ($wantsJson) {
                    return $jsonError(403, 'forbidden', 'Forbidden.');
                }

                return redirect()->to('/')->with('error', 'You do not have permission to access this resource.');
            }
        } catch (\Throwable $e) {
            // Fail closed with JSON for API/AJAX requests
            log_message('error', 'Permission filter error: ' . $e->getMessage());

            if ($wantsJson) {
                return $jsonError(403, 'forbidden', 'Forbidden.');
            }

            return redirect()->to('/')->with('error', 'System error during permission check.');
        }

        return null;
    }

    private function parsePermissionArguments($arguments): ?array
    {
        if (empty($arguments) || !is_array($arguments)) {
            return null;
        }

        $count = count($arguments);
        if ($count === 1) {
            $raw = (string) $arguments[0];
            $hasColon = str_contains($raw, ':');
            $hasDot = str_contains($raw, '.');
            if ($hasColon && $hasDot) {
                return null;
            }

            if ($hasColon) {
                $parts = explode(':', $raw);
                if (count($parts) !== 2) {
                    return null;
                }
                $module = trim($parts[0]);
                $action = trim($parts[1]);
                if ($module === '' || $action === '') {
                    return null;
                }
                return [$module, $action];
            }

            if ($hasDot) {
                $parts = explode('.', $raw);
                if (count($parts) !== 2) {
                    return null;
                }
                $module = trim($parts[0]);
                $action = trim($parts[1]);
                if ($module === '' || $action === '') {
                    return null;
                }
                return [$module, $action];
            }

            $module = trim($raw);
            if ($module === '') {
                return null;
            }
            return [$module, 'view'];
        }

        if ($count === 2) {
            $raw0 = (string) $arguments[0];
            $raw1 = (string) $arguments[1];

            if (str_contains($raw0, ':') || str_contains($raw0, '.') || str_contains($raw1, ':') || str_contains($raw1, '.')) {
                return null;
            }

            $module = trim($raw0);
            $action = trim($raw1);
            if ($module === '' || $action === '') {
                return null;
            }
            return [$module, $action];
        }

        return null;
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
