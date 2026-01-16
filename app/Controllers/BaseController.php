<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\HTTP\CLIRequest;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

/**
 * Class BaseController
 *
 * BaseController provides a convenient place for loading components
 * and performing functions that are needed by all your controllers.
 * Extend this class in any new controllers:
 *     class Home extends BaseController
 *
 * For security be sure to declare any new methods as protected or private.
 */
abstract class BaseController extends Controller
{
    /**
     * Instance of the main Request object.
     *
     * @var CLIRequest|IncomingRequest
     */
    protected $request;

    /**
     * IonAuth library instance
     *
     * @var \App\Libraries\IonAuth
     */
    protected $ionAuth;

    /**
     * Session instance
     *
     * @var \CodeIgniter\Session\Session
     */
    protected $session;

    /**
     * An array of helpers to be loaded automatically upon
     * class instantiation. These helpers will be available
     * to all other controllers that extend BaseController.
     *
     * @var list<string>
     */
    protected $helpers = ['form', 'url', 'auth'];

    /**
     * @return void
     */
    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        // Do Not Edit This Line
        parent::initController($request, $response, $logger);

        // Load IonAuth library
        $this->ionAuth = new \App\Libraries\IonAuth();
        $this->session = \Config\Services::session();
        
        // Load settings helper
        helper('settings');

        // Inject clinic info globally for all view() helper calls, unless already set
        $renderer = \Config\Services::renderer();
        if (!isset($renderer->getData()['clinic'])) {
            $renderer->setVar('clinic', settings()->getClinicInfo());
        }

        // Global data injection for authenticated users (Switcher, Profile, etc)
        if ($this->isLoggedIn()) {
            $user = $this->getCurrentUser();
            if ($user) {
                $clinicService = new \App\Services\ClinicService();
                $renderer->setVar('user', $user);
                $renderer->setVar('user_groups', $this->getUserGroups());
                $renderer->setVar('user_memberships', $clinicService->getMemberships($user->id));
                
                // P5-11-UX: Control Plane Visibility
                $this->initPermissionService();
                $isSuperAdmin = $this->permissionService->isSuperAdmin($user->id);
                $renderer->setVar('is_super_admin', $isSuperAdmin);
                $renderer->setVar('global_mode', session()->get('global_mode'));
            }
        }
    }

    /**
     * Check if user is logged in
     *
     * @return bool
     */
    protected function isLoggedIn()
    {
        return $this->ionAuth->loggedIn();
    }


    /**
     * Check if user is in specific group
     *
     * @param string|array $group
     * @param int|null $userId
     * @return bool
     */
    protected function inGroup($group, $userId = null)
    {
        return $this->ionAuth->inGroup($group, $userId);
    }

    /**
     * Get current user
     *
     * @return object|null
     */
    protected function getCurrentUser()
    {
        return $this->ionAuth->user()->row();
    }

    /**
     * Get user groups
     *
     * @param int|null $userId
     * @return array
     */
    protected function getUserGroups($userId = null)
    {
        return $this->ionAuth->getUsersGroups($userId)->getResult();
    }

    /**
     * Get user data for views
     *
     * @return array
     */
    protected function getUserDataForView(): array
    {
        $user = $this->getCurrentUser();
        $userGroups = $this->getUserGroups();
        
        $clinicService = new \App\Services\ClinicService();
        $memberships = $user ? $clinicService->getMemberships($user->id) : [];
        
        return [
            'user' => $user,
            'user_groups' => $userGroups,
            'user_memberships' => $memberships
        ];
    }

    /**
     * Override the view method to automatically include user and clinic data
     *
     * @param string $name
     * @param array $data
     * @param array $options
     * @return string
     */
    protected function view(string $name, array $data = [], array $options = []): string
    {
        // Automatically merge user data if user is logged in
        if ($this->isLoggedIn()) {
            $data = array_merge($data, $this->getUserDataForView());
        }

        // Add clinic info if not already present
        if (!isset($data['clinic'])) {
            $data['clinic'] = settings()->getClinicInfo();
        }
        
        return view($name, $data, $options);
    }

    /**
     * Require login - redirect to login if not logged in
     *
     * @return \CodeIgniter\HTTP\RedirectResponse|null
     */
    protected function requireLogin()
    {
        if (!$this->isLoggedIn()) {
            return redirect()->to('/auth/login');
        }
        return null;
    }

    /**
     * Require admin - redirect to dashboard if not admin
     *
     * @return \CodeIgniter\HTTP\RedirectResponse|null
     */
    protected function requireAdmin()
    {
        if (!$this->isAdmin()) {
            $this->session->setFlashdata('error', 'You must be an administrator to access this page.');
            return redirect()->to('/dashboard');
        }
        return null;
    }

    /**
     * Require specific group membership
     *
     * @param string|array $group
     * @return \CodeIgniter\HTTP\RedirectResponse|null
     */
    protected function requireGroup($group)
    {
        if (!$this->inGroup($group)) {
            $this->session->setFlashdata('error', 'You do not have permission to access this page.');
            return redirect()->to('/dashboard'); 
        }
        return null;
    }

    /**
     * Permission service instance
     *
     * @var \App\Services\PermissionService
     */
    protected $permissionService;

    /**
     * Initialize permission service
     */
    protected function initPermissionService()
    {
        if (!$this->permissionService) {
            $this->permissionService = new \App\Services\PermissionService();
        }
    }

    /**
     * Check if user has permission for specific action
     *
     * @param string $module
     * @param string $action
     * @return bool
     */
    protected function hasPermission($module, $action)
    {
        $user = $this->getCurrentUser();
        if (!$user) {
            return false;
        }

        $this->initPermissionService();
        return $this->permissionService->hasPermission($user->id, $module, $action);
    }

    /**
     * Get user permissions
     *
     * @param int|null $userId
     * @return array
     */
    protected function getUserPermissions($userId = null)
    {
        if (!$userId) {
            $user = $this->getCurrentUser();
            if (!$user) {
                return [];
            }
            $userId = $user->id;
        }

        $this->initPermissionService();
        return $this->permissionService->getUserPermissions($userId);
    }

    /**
     * Check if user has role
     *
     * @param string $roleSlug
     * @param int|null $userId
     * @return bool
     */
    protected function hasRole($roleSlug, $userId = null)
    {
        if (!$userId) {
            $user = $this->getCurrentUser();
            if (!$user) {
                return false;
            }
            $userId = $user->id;
        }

        $this->initPermissionService();
        return $this->permissionService->hasRole($userId, $roleSlug);
    }

    /**
     * Check if user has any of the given roles
     *
     * @param array $roleSlugs
     * @param int|null $userId
     * @return bool
     */
    protected function hasAnyRole($roleSlugs, $userId = null)
    {
        if (!$userId) {
            $user = $this->getCurrentUser();
            if (!$user) {
                return false;
            }
            $userId = $user->id;
        }

        $this->initPermissionService();
        return $this->permissionService->hasAnyRole($userId, $roleSlugs);
    }

    /**
     * Check if user is admin
     *
     * @param int|null $userId
     * @return bool
     */
    protected function isAdmin($userId = null)
    {
        // If no userId provided, use IonAuth method for backward compatibility
        if (!$userId) {
            return $this->ionAuth->isAdmin();
        }

        $this->initPermissionService();
        return $this->permissionService->isAdmin($userId);
    }

    /**
     * Require specific permission
     *
     * @param string $module
     * @param string $action
     * @return \CodeIgniter\HTTP\RedirectResponse|null
     */
    protected function requirePermission($module, $action)
    {
        if (!$this->hasPermission($module, $action)) {
            $this->session->setFlashdata('error', 'You do not have permission to perform this action.');
            return redirect()->to('/dashboard');
        }
        return null;
    }

    /**
     * Require specific role
     *
     * @param string|array $roleSlug
     * @return \CodeIgniter\HTTP\RedirectResponse|null
     */
    protected function requireRole($roleSlug)
    {
        $user = $this->getCurrentUser();
        if (!$user) {
            return redirect()->to('/auth/login');
        }

        $this->initPermissionService();
        
        if (is_array($roleSlug)) {
            if (!$this->permissionService->hasAnyRole($user->id, $roleSlug)) {
                $this->session->setFlashdata('error', 'You do not have the required role to access this page.');
                return redirect()->to('/dashboard');
            }
        } else {
            if (!$this->permissionService->hasRole($user->id, $roleSlug)) {
                $this->session->setFlashdata('error', 'You do not have the required role to access this page.');
                return redirect()->to('/dashboard');
            }
        }
        
        return null;
    }
}
