<?php

namespace App\Controllers\ControlPlane;

use App\Controllers\BaseController;

class Settings extends BaseController
{
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
            'title' => 'Control Plane Settings',
            'global_mode' => true
        ];

        return view('control_plane/settings', $data);
    }
}
