<?php

namespace App\Controllers\ControlPlane;

use App\Controllers\BaseController;

class Operations extends BaseController
{
    /**
     * GET /controlplane/operations
     */
    public function index()
    {
        // Global mode check handled by 'controlplane' filter
        return view('control_plane/operations', [
            'title' => 'Operations'
        ]);
    }
}
