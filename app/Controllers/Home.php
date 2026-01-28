<?php

namespace App\Controllers;

use CodeIgniter\Exceptions\PageNotFoundException;
use App\Models\ClinicSubscriptionModel;
use App\Services\PermissionService;

class Home extends BaseController
{
    public function index()
    {
        $session = session();

        if (!$this->ionAuth->loggedIn()) {
            return redirect()->to('/auth/login');
        }

        if ($session->get('global_mode') === true) {
            return redirect()->to('/controlplane/dashboard');
        }

        $clinicId = $session->get('active_clinic_id');
        if (!$clinicId) {
            return redirect()->to('/clinic/select');
        }

        // Preserve concealment behavior if subscription is invalid.
        $subModel = new ClinicSubscriptionModel();
        $sub = $subModel->withoutTenantScope()->getCurrentSubscription((int)$clinicId);
        if ($sub) {
            $sub = $subModel->markExpiredIfNeeded($sub);
        }
        $isValid = false;

        if ($sub) {
            $now = time();
            if ($sub['status'] === 'active') {
                $startOk = empty($sub['start_at']) || strtotime($sub['start_at']) <= $now;
                $endOk = empty($sub['end_at']) || strtotime($sub['end_at']) > $now;
                if ($startOk && $endOk) {
                    $isValid = true;
                }
            }
        }

        if (!$isValid) {
            throw PageNotFoundException::forPageNotFound("Access Denied: Subscription required.");
        }

        $userId = $this->ionAuth->getUserId();
        $permissionService = new PermissionService();

        if (!$permissionService->hasPermission($userId, 'dashboard', 'view')) {
            $fallbacks = [
                ['module' => 'patients', 'action' => 'view', 'route' => '/patients'],
                ['module' => 'appointments', 'action' => 'view', 'route' => '/appointments'],
                ['module' => 'examinations', 'action' => 'view', 'route' => '/examinations'],
                ['module' => 'treatments', 'action' => 'view', 'route' => '/treatments'],
                ['module' => 'prescriptions', 'action' => 'view', 'route' => '/prescriptions'],
                ['module' => 'finance', 'action' => 'view', 'route' => '/finance'],
                ['module' => 'reports', 'action' => 'view', 'route' => '/reports'],
                ['module' => 'inventory', 'action' => 'view', 'route' => '/inventory'],
                ['module' => 'users', 'action' => 'view', 'route' => '/users'],
                ['module' => 'settings', 'action' => 'view', 'route' => '/settings'],
            ];

            foreach ($fallbacks as $fallback) {
                if ($permissionService->hasPermission($userId, $fallback['module'], $fallback['action'])) {
                    return redirect()->to($fallback['route']);
                }
            }

            throw PageNotFoundException::forPageNotFound("Access Denied: Dashboard permission required.");
        }

        return redirect()->to('/dashboard');
    }
}
