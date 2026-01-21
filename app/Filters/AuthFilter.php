<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use App\Libraries\IonAuth;

/**
 * Authentication Filter
 * 
 * This filter checks if the user is authenticated.
 * If not, it redirects to the login page.
 */
class AuthFilter implements FilterInterface
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
        $ionAuth = new IonAuth();
        $session = session();
        
        // Check if user is logged in
        if (!$ionAuth->loggedIn()) {
            // Check if it's an API request
            $path = $request->getUri()->getPath();
            $isApi = strpos($path, '/api/') !== false 
                || str_starts_with($path, 'api/')
                || ($request->hasHeader('Accept') && str_contains($request->getHeaderLine('Accept'), 'application/json'));

            if ($isApi) {
                $response = service('response');
                return $response->setStatusCode(401)
                                ->setJSON([
                                    'error' => [
                                        'code' => 'unauthenticated',
                                        'message' => 'Authentication required.'
                                    ]
                                ]);
            }

            // Store the current URL to redirect back after login
            session()->set('redirect_url', current_url());
            
            // Redirect to login page
            return redirect()->to('/auth/login');
        }

        $path = trim($request->getUri()->getPath(), '/');
        $isApi = strpos($path, 'api/') === 0
            || ($request->hasHeader('Accept') && str_contains($request->getHeaderLine('Accept'), 'application/json'));

        if (!$session->get('global_mode') && !$session->get('active_clinic_id')) {
            $isAllowed = $path === ''
                || $path === 'logout'
                || str_starts_with($path, 'auth')
                || str_starts_with($path, 'clinic')
                || str_starts_with($path, 'controlplane');

            if (!$isAllowed && !$isApi && !$request->isAJAX()) {
                return redirect()->to('/clinic/select');
            }
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
