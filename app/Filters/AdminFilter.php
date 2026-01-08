<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Admin Filter
 * 
 * This filter checks if the user is an administrator and redirects to dashboard if not.
 */
class AdminFilter implements FilterInterface
{
    /**
     * Do whatever processing this filter needs to do.
     * By default it should not return anything during
     * normal execution. However, when an abnormal state
     * is found, it should return an instance of
     * CodeIgniter\HTTP\Response which will be sent to
     * the client and will stop the normal execution.
     *
     * @param RequestInterface $request
     * @param array|null       $arguments
     *
     * @return mixed
     */
    public function before(RequestInterface $request, $arguments = null)
    {
        // Load IonAuth library
        $ionAuth = new \App\Libraries\IonAuth();
        $session = \Config\Services::session();

        // Check if user is logged in
        if (!$ionAuth->loggedIn()) {
            $session->setFlashdata('message', 'Please log in to access this page.');
            return redirect()->to('/auth/login');
        }

        // Check if user is admin
        if (!$ionAuth->isAdmin()) {
            $session->setFlashdata('error', 'You must be an administrator to access this page.');
            return redirect()->to('/dashboard');
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
