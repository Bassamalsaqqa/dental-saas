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
        // Fail closed if no permission argument is provided
        if (empty($arguments)) {
            log_message('error', 'PermissionFilter: No permission specified in route filter arguments.');
            
            // Check for API request to return JSON
            $isApi = $request->hasHeader('Accept') && str_contains($request->getHeaderLine('Accept'), 'application/json');
            $uri = $request->getUri()->getPath();
            if (str_starts_with($uri, 'api/') || str_starts_with($uri, '/api/')) {
                $isApi = true;
            }

            if ($isApi) {
                $response = service('response');
                return $response->setStatusCode(403)
                                ->setJSON(['error' => 'forbidden']);
            }

            return redirect()->to('/')->with('error', 'Access denied (configuration error).');
        }

        $requiredPermission = $arguments[0];
        $action = $arguments[1] ?? 'view';

        // Get the current user
        try {
            $ionAuth = new \App\Libraries\IonAuth();
            $user = $ionAuth->user()->row();

            if (!$user) {
                log_message('debug', 'PermissionFilter: No user found, redirecting to login');
                return redirect()->to('/auth/login');
            }

            log_message('debug', "PermissionFilter: Checking permission {$requiredPermission}/{$action} for user {$user->id}");

            // Check if user has the required permission
            $permissionService = new \App\Services\PermissionService();
            $hasPermission = $permissionService->hasPermission($user->id, $requiredPermission, $action);

            log_message('debug', "PermissionFilter: Permission check result: " . ($hasPermission ? 'true' : 'false'));

            if (!$hasPermission) {
                // Log the unauthorized access attempt
                log_message('warning', "Unauthorized access attempt by user {$user->id} to {$requiredPermission}/{$action}");
                
                // Check if it's an API request
                $isApi = $request->hasHeader('Accept') && str_contains($request->getHeaderLine('Accept'), 'application/json');
                $uri = $request->getUri()->getPath();
                if (str_starts_with($uri, 'api/') || str_starts_with($uri, '/api/')) {
                    $isApi = true;
                }

                if ($isApi) {
                    $response = service('response');
                    return $response->setStatusCode(403)
                                    ->setJSON(['error' => 'unauthorized']);
                }

                // Return 403 Forbidden - redirect to a safe route without permission filters
                return redirect()->to('/')->with('error', 'You do not have permission to access this resource.');
            }
        } catch (\Exception $e) {
            // Log the error and redirect to login if there's an issue
            log_message('error', 'Permission filter error: ' . $e->getMessage());
            log_message('error', 'Permission filter stack trace: ' . $e->getTraceAsString());
            
            // Fail Closed
            $isApi = $request->hasHeader('Accept') && str_contains($request->getHeaderLine('Accept'), 'application/json');
            $uri = $request->getUri()->getPath();
            if (str_starts_with($uri, 'api/') || str_starts_with($uri, '/api/')) {
                $isApi = true;
            }

            if ($isApi) {
                $response = service('response');
                return $response->setStatusCode(403)
                                ->setJSON(['error' => 'unauthorized']);
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
