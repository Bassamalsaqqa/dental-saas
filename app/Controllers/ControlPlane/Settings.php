<?php

namespace App\Controllers\ControlPlane;

use App\Controllers\BaseController;

class Settings extends BaseController
{
    /**
     * GET /controlplane/settings
     */
    public function index()
    {
        // Global mode check handled by 'controlplane' filter
        return view('control_plane/settings', [
            'title' => 'Settings'
        ]);
    }
}
