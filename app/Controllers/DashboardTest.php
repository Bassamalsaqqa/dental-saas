<?php

namespace App\Controllers;

class DashboardTest extends BaseController
{
    public function index()
    {
        // Simple dashboard without complex model calls
        $data = [
            'title' => 'Dental Management Dashboard - Test',
            'stats' => [
                'total_patients' => 0,
                'total_examinations' => 0,
                'today_examinations' => 0,
                'total_appointments' => 0,
                'today_appointments' => 0,
                'upcoming_appointments' => 0,
                'total_revenue' => 0,
                'pending_payments' => 0,
                'overdue_payments' => 0,
                'active_treatments' => 0,
                'completed_treatments' => 0
            ],
            'recent_patients' => [],
            'upcoming_appointments' => [],
            'recent_examinations' => [],
            'monthly_revenue' => []
        ];

        return view('dashboard/index', $data);
    }
}