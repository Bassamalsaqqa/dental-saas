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
     * Locale resolver instance
     *
     * @var \App\Services\LocaleResolverService
     */
    protected $localeResolver;

    /**
     * Resolved locale for current request
     */
    protected string $currentLocale = 'en';

    /**
     * Whether current locale should render RTL
     */
    protected bool $isRtl = false;

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
        $this->localeResolver = service('localeResolver');
        
        // Load settings helper
        helper('settings');
        helper('navigation');
        $this->applyRuntimeTimezone();
        $this->applyLocaleContext($request);

        // Inject clinic info globally for all view() helper calls, unless already set
        $renderer = \Config\Services::renderer();
        $renderer->setVar('locale', $this->currentLocale);
        $renderer->setVar('is_rtl', $this->isRtl);
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
                $isSuperAdmin = $this->permissionService ? $this->permissionService->isSuperAdmin($user->id) : false;
                $renderer->setVar('is_super_admin', $isSuperAdmin);
                $renderer->setVar('global_mode', session()->get('global_mode'));
            }

            // Auto-track navigation breadcrumb
            $this->trackNavigation();
        }
    }

    private function trackNavigation(): void
    {
        try {
            $nav = service('navigation');
            if ($nav->shouldSkip()) {
                return;
            }

            $uri = service('uri');
            $segments = $uri->getSegments();
            $segment1 = $segments[0] ?? '';
            $segment2 = $segments[1] ?? '';
            $currentUrl = (string) $this->request->getUri();

            $icon = 'fa-circle';
            $label = ucfirst($segment1);

            $labelMap = [
                'dashboard' => ['label' => lang('UI.menuDashboard'), 'icon' => 'fa-tachometer-alt'],
                'patients' => ['label' => lang('UI.menuPatients'), 'icon' => 'fa-users'],
                'patient' => ['label' => lang('UI.menuPatients'), 'icon' => 'fa-user'],
                'examinations' => ['label' => lang('UI.menuExaminations'), 'icon' => 'fa-stethoscope'],
                'examination' => ['label' => lang('UI.menuExaminations'), 'icon' => 'fa-stethoscope'],
                'appointments' => ['label' => lang('UI.menuAppointments'), 'icon' => 'fa-calendar-alt'],
                'appointment' => ['label' => lang('UI.menuAppointments'), 'icon' => 'fa-calendar-alt'],
                'treatments' => ['label' => lang('UI.menuTreatments'), 'icon' => 'fa-tooth'],
                'treatment' => ['label' => lang('UI.menuTreatments'), 'icon' => 'fa-tooth'],
                'prescriptions' => ['label' => lang('UI.menuPrescriptions'), 'icon' => 'fa-prescription-bottle-alt'],
                'prescription' => ['label' => lang('UI.menuPrescriptions'), 'icon' => 'fa-prescription-bottle-alt'],
                'finance' => ['label' => lang('UI.menuFinance'), 'icon' => 'fa-dollar-sign'],
                'reports' => ['label' => lang('UI.menuReports'), 'icon' => 'fa-chart-bar'],
                'inventory' => ['label' => lang('UI.menuInventory'), 'icon' => 'fa-boxes'],
                'providers' => ['label' => lang('UI.menuProviders'), 'icon' => 'fa-handshake'],
                'users' => ['label' => lang('UI.menuUserManagement'), 'icon' => 'fa-users-cog'],
                'roles' => ['label' => lang('UI.menuRoleManagement'), 'icon' => 'fa-user-tag'],
                'settings' => ['label' => lang('UI.menuSettings'), 'icon' => 'fa-cog'],
                'notifications' => ['label' => lang('UI.notifications'), 'icon' => 'fa-bell'],
                'contracts' => ['label' => 'Contracts', 'icon' => 'fa-file-signature'],
                'odontogram' => ['label' => 'Odontogram', 'icon' => 'fa-tooth'],
            ];

            if (isset($labelMap[$segment1])) {
                $label = $labelMap[$segment1]['label'];
                $icon = $labelMap[$segment1]['icon'];
            }

            if (is_numeric($segment2) && $segment2 > 0) {
                $label = '#' . $segment2 . ' — ' . $label;
            }

            $nav->push($label, $currentUrl);
        } catch (\Throwable $e) {
            log_message('error', 'Navigation track error: ' . $e->getMessage());
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
        
        // View identity, rather than URL aliases, owns heavy optional vendor assets.
        $data['loadChartJs'] ??= in_array($name, ['dashboard/index', 'reports/index', 'finance/reports'], true);
        $data['loadFullCalendar'] ??= in_array($name, ['appointment/calendar', 'examination/calendar'], true);
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
     * Normalize a date-only value to ISO (Y-m-d) using clinic/global date format settings.
     * Returns null when the input is empty or invalid.
     */
    protected function normalizeDateOnly(?string $value): ?string
    {
        $raw = trim((string) ($value ?? ''));
        if ($raw === '') {
            return null;
        }

        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $raw)) {
            return $raw;
        }

        $format = (string) settings()->get('date_format', 'd/m/Y');
        if (in_array($format, ['d/m/Y', 'm/d/Y'], true)) {
            $pattern = '/^(\d{1,2})\/(\d{1,2})\/(\d{4})$/';
            if (preg_match($pattern, $raw, $m)) {
                if ($format === 'd/m/Y') {
                    [$day, $month, $year] = [$m[1], $m[2], $m[3]];
                } else {
                    [$month, $day, $year] = [$m[1], $m[2], $m[3]];
                }
                $month = str_pad($month, 2, '0', STR_PAD_LEFT);
                $day = str_pad($day, 2, '0', STR_PAD_LEFT);
                if (checkdate((int) $month, (int) $day, (int) $year)) {
                    return "{$year}-{$month}-{$day}";
                }
                return null;
            }
        }

        $ts = strtotime($raw);
        return $ts === false ? null : date('Y-m-d', $ts);
    }

    /**
     * Normalize one POST field containing a date-only value to ISO Y-m-d.
     * Returns null for empty values, and null for invalid values.
     */
    protected function normalizePostedDateOnly(string $field): ?string
    {
        return $this->normalizeDateOnly((string) $this->request->getPost($field));
    }

    /**
     * Return the clinic's configured date format.
     */
    protected function getClinicDateFormat(): string
    {
        $format = (string) settings()->get('date_format', 'd/m/Y');
        return $format === '' ? 'd/m/Y' : $format;
    }

    /**
     * Build a validation rule string for clinic date inputs.
     *
     * Important:
     * UI date fields may be submitted as ISO `Y-m-d` because the shared layout
     * normalizes `data-clinic-date="1"` values on form submit.
     * Using generic `valid_date` keeps server validation compatible with both:
     * - displayed clinic format (e.g. d/m/Y, m/d/Y)
     * - normalized ISO format (Y-m-d)
     */
    protected function getClinicDateValidationRule(): string
    {
        return 'required|valid_date';
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

    /**
     * Sanitize internal return target to avoid open redirects.
     *
     * @param string|null $returnTo
     * @param array<int, string> $allowedPrefixes
     * @return string|null
     */
    protected function sanitizeInternalReturnTo(?string $returnTo, array $allowedPrefixes = ['patient', 'examination', 'appointment', 'treatment', 'prescription', 'finance']): ?string
    {
        $target = trim((string) $returnTo);
        if ($target === '') {
            return null;
        }

        $target = ltrim($target, '/');

        if (strpos($target, '://') !== false || strpos($target, '//') === 0) {
            return null;
        }

        if (!preg_match('#^[a-zA-Z0-9/_\\-?=&]+$#', $target)) {
            return null;
        }

        foreach ($allowedPrefixes as $prefix) {
            if (preg_match('#^' . preg_quote($prefix, '#') . '(/|\\?|$)#', $target)) {
                return $target;
            }
        }

        return null;
    }

    protected function workflowReturnTo(array $allowedPrefixes = []): ?string
    {
        $allowedPrefixes = $allowedPrefixes ?: ['patient', 'patients', 'examination', 'examinations', 'appointment', 'appointments', 'treatment', 'treatments', 'prescription', 'prescriptions', 'finance'];

        return $this->sanitizeInternalReturnTo(
            (string) $this->request->getPost('return_to'),
            $allowedPrefixes
        );
    }

    protected function redirectAfterWorkflow(string $fallback, array $allowedPrefixes = [], ?string $returnToOverride = null, bool $readPostedReturnTo = true)
    {
        try {
            service('navigation')->completeWorkflow();
        } catch (\Throwable $e) {
            log_message('error', 'Navigation workflow completion failed: ' . $e->getMessage());
        }

        $returnTo = $returnToOverride;
        if ($returnTo === null && $readPostedReturnTo) {
            $returnTo = $this->workflowReturnTo($allowedPrefixes);
        }

        return redirect()->to(base_url($returnTo ?: $fallback));
    }

    /**
     * Apply timezone at runtime using clinic settings when available.
     * Falls back to app default timezone to avoid relying on server defaults.
     */
    protected function applyRuntimeTimezone(): void
    {
        try {
            $fallbackTimezone = (string) (config('App')->appTimezone ?? 'UTC');
            $timezone = $fallbackTimezone;

            if (function_exists('settings')) {
                $settingsTimezone = settings()->getTimezone();
                if (is_string($settingsTimezone) && $settingsTimezone !== '') {
                    $timezone = $settingsTimezone;
                }
            }

            if (!in_array($timezone, timezone_identifiers_list(), true)) {
                $timezone = $fallbackTimezone;
            }

            date_default_timezone_set($timezone);
        } catch (\Throwable $e) {
            log_message('error', 'Timezone initialization failed: ' . $e->getMessage());
            date_default_timezone_set('UTC');
        }
    }

    /**
     * Resolve and apply UI locale for non-kiosk pages.
     */
    protected function applyLocaleContext(RequestInterface $request): void
    {
        if (!$request instanceof IncomingRequest) {
            return;
        }

        // Kiosk already resolves locale explicitly per clinic context.
        $uri = $request->getUri();
        $uriPath = trim((string) ($uri instanceof \CodeIgniter\HTTP\SiteURI ? $uri->getRoutePath() : $uri->getPath()), '/');
        if (str_starts_with($uriPath, 'kiosk')) {
            $this->currentLocale = strtolower((string) $request->getLocale() ?: 'en');
            $this->isRtl = $this->localeResolver->isRtl($this->currentLocale);
            return;
        }

        $appConfig = config('App');
        $supported = $appConfig->supportedLocales ?? ['en', 'ar'];
        $defaultLocale = strtolower((string) ($appConfig->defaultLocale ?? 'en'));
        $isControlPlane = str_starts_with($uriPath, 'controlplane');

        $settingsLocale = '';
        if (!$isControlPlane) {
            $activeClinicId = (int) (session()->get('active_clinic_id') ?? 0);
            if ($activeClinicId > 0) {
                $settingsLocale = strtolower((string) settings()->get('ui_language', 'en'));
            }
        }

        $resolved = $this->localeResolver->resolve($request, [
            'supported' => $supported,
            'default' => $defaultLocale,
            'session_key' => $isControlPlane ? 'cp_ui_locale' : 'ui_locale',
            'settings_locale' => $settingsLocale,
        ]);

        $this->localeResolver->apply($request, $resolved);
        $this->currentLocale = $resolved;
        $this->isRtl = $this->localeResolver->isRtl($resolved);
    }
}
