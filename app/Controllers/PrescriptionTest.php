<?php

namespace App\Controllers;

class PrescriptionTest extends BaseController
{
    public function index()
    {
        $data = [
            'title' => 'Prescription Management - Test',
            'prescriptions' => [],
            'total_prescriptions' => 0,
            'active_prescriptions' => 0,
            'expired_prescriptions' => 0
        ];

        return view('prescription/index', $data);
    }
}
