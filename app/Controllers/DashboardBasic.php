<?php

namespace App\Controllers;

class DashboardBasic extends BaseController
{
    public function index()
    {
        $data = [
            'title' => 'Dental Management Dashboard',
            'pageTitle' => 'Dashboard'
        ];

        return $this->view('dashboard/simple', $data);
    }
}
