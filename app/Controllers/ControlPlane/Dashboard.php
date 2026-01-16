<?php

namespace App\Controllers\ControlPlane;

use App\Controllers\BaseController;

class Dashboard extends BaseController
{
    /**
     * Enforce Superadmin Global Mode check
     */
    private function ensureGlobalMode()
    {
        if (!session()->get('global_mode')) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Access Denied');
        }
    }

    public function index()
    {
        $this->ensureGlobalMode();

        $data = [
            'title' => 'Control Plane Dashboard',
            'global_mode' => true
        ];

        return view('control_plane/dashboard', $data);
    }
}
