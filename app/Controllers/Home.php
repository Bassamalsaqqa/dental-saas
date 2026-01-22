<?php

namespace App\Controllers;

use CodeIgniter\Exceptions\PageNotFoundException;
use App\Models\ClinicSubscriptionModel;

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
        $isValid = false;

        if ($sub) {
            $now = date('Y-m-d H:i:s');
            if ($sub['status'] === 'active') {
                if (empty($sub['end_at']) || $sub['end_at'] > $now) {
                    $isValid = true;
                }
            }
        }

        if (!$isValid) {
            throw PageNotFoundException::forPageNotFound("Access Denied: Subscription required.");
        }

        return redirect()->to('/dashboard');
    }
}
