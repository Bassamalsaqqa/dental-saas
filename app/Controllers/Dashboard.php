<?php

namespace App\Controllers;

use App\Models\PatientModel;
use App\Models\ExaminationModel;
use App\Models\AppointmentModel;
use App\Models\FinanceModel;
use App\Models\TreatmentModel;

class Dashboard extends BaseController
{
    protected $patientModel;
    protected $examinationModel;
    protected $appointmentModel;
    protected $financeModel;
    protected $treatmentModel;

    public function __construct()
    {
        $this->patientModel = new PatientModel();
        $this->examinationModel = new ExaminationModel();
        $this->appointmentModel = new AppointmentModel();
        $this->financeModel = new FinanceModel();
        $this->treatmentModel = new TreatmentModel();
    }

    public function index()
    {
        // Check if user is logged in (this will be handled by the auth filter)
        // But we can add additional checks here if needed
        
        try {
            $data = [
                'title' => 'Dental Management Dashboard',
                'pageTitle' => 'Dashboard',
                'stats' => $this->getDashboardStats(),
                'recent_patients' => $this->getRecentPatients(),
                'upcoming_appointments' => $this->getUpcomingAppointments(),
                'recent_examinations' => $this->getRecentExaminations(),
                'monthly_revenue' => $this->getMonthlyRevenue()
            ];

            return $this->view('dashboard/index', $data);
        } catch (\Exception $e) {
            // Log the error and show a simplified dashboard
            log_message('error', 'Dashboard error: ' . $e->getMessage());
            
            $data = [
                'title' => 'Dental Management Dashboard',
                'pageTitle' => 'Dashboard',
                'stats' => $this->getBasicStats(),
                'recent_patients' => [],
                'upcoming_appointments' => [],
                'recent_examinations' => [],
                'monthly_revenue' => []
            ];

            return $this->view('dashboard/index', $data);
        }
    }

    private function getDashboardStats()
    {
        $patientStats = $this->patientModel->countAllResults();
        $examinationStats = $this->examinationModel->getExaminationStats();
        $appointmentStats = $this->appointmentModel->getAppointmentStats();
        $financeStats = $this->financeModel->getFinanceStats();
        $treatmentStats = $this->treatmentModel->getTreatmentStats();

        return [
            'total_patients' => $patientStats,
            'total_examinations' => $examinationStats['total_examinations'],
            'today_examinations' => $examinationStats['today_examinations'],
            'total_appointments' => $appointmentStats['total_appointments'],
            'today_appointments' => $appointmentStats['today_appointments'],
            'upcoming_appointments' => $appointmentStats['upcoming_appointments'],
            'total_revenue' => $financeStats['total_revenue'],
            'pending_payments' => $financeStats['pending_payments'],
            'overdue_payments' => $financeStats['overdue_payments'],
            'active_treatments' => $treatmentStats['active_treatments'],
            'completed_treatments' => $treatmentStats['completed_treatments']
        ];
    }

    public function getStats()
    {
        $stats = $this->getDashboardStats();
        return $this->response->setJSON($stats);
    }

    public function testChartData()
    {
        return $this->response->setJSON(['test' => 'success', 'message' => 'Chart data endpoint is working']);
    }

    public function getChartData()
    {
        try {
            // Get monthly revenue data
            $monthlyRevenue = $this->getMonthlyRevenueForChart();
            
            // Get treatment types data
            $treatmentTypes = $this->getTreatmentTypesForChart();
            
            $data = [
                'monthly_revenue' => $monthlyRevenue,
                'treatment_types' => $treatmentTypes,
                'success' => true
            ];
            
            return $this->response->setJSON($data);
        } catch (\Exception $e) {
            log_message('error', 'Chart data error: ' . $e->getMessage());
            return $this->response->setJSON([
                'error' => 'Failed to fetch chart data',
                'message' => $e->getMessage(),
                'success' => false
            ]);
        }
    }

    private function getMonthlyRevenueForChart()
    {
        try {
            // Get revenue data for the current year
            $currentYear = date('Y');
            $monthlyRevenue = $this->financeModel->getMonthlyRevenue($currentYear);
            
            // Create a complete array for all 12 months
            $revenueData = [];
            $existingData = [];
            
            // Convert existing data to associative array for easy lookup
            foreach ($monthlyRevenue as $data) {
                $existingData[(int)$data['month']] = (float)$data['total_amount'];
            }
            
            // Generate data for each month (1-12)
            for ($i = 1; $i <= 12; $i++) {
                $revenueData[] = [
                    'month' => $i,
                    'year' => $currentYear,
                    'total_amount' => isset($existingData[$i]) ? $existingData[$i] : 0
                ];
            }
            
            return $revenueData;
        } catch (\Exception $e) {
            log_message('error', 'Monthly revenue chart error: ' . $e->getMessage());
            return [];
        }
    }

    private function getTreatmentTypesForChart()
    {
        try {
            // Get treatment types with counts using existing method
            $treatmentTypes = $this->treatmentModel->getTreatmentTypesStats();
            
            // Map to expected format
            $chartData = [];
            $defaultTypes = ['Cleaning', 'Extraction', 'Filling', 'Crown', 'Root Canal', 'Orthodontic', 'Implant', 'Other'];
            
            foreach ($defaultTypes as $type) {
                $found = false;
                foreach ($treatmentTypes as $treatment) {
                    if (strtolower($treatment['treatment_type']) === strtolower($type)) {
                        $chartData[] = [
                            'treatment_type' => $type,
                            'count' => (int)$treatment['count']
                        ];
                        $found = true;
                        break;
                    }
                }
                
                if (!$found) {
                    $chartData[] = [
                        'treatment_type' => $type,
                        'count' => 0
                    ];
                }
            }
            
            return $chartData;
        } catch (\Exception $e) {
            log_message('error', 'Treatment types chart error: ' . $e->getMessage());
            return [];
        }
    }

    private function getRecentPatients()
    {
        try {
            return $this->patientModel->orderBy('created_at', 'DESC')->limit(5)->findAll();
        } catch (\Exception $e) {
            log_message('error', 'Recent patients error: ' . $e->getMessage());
            return [];
        }
    }

    private function getUpcomingAppointments()
    {
        try {
            return $this->appointmentModel->getUpcomingAppointments(5);
        } catch (\Exception $e) {
            log_message('error', 'Upcoming appointments error: ' . $e->getMessage());
            return [];
        }
    }

    private function getRecentExaminations()
    {
        try {
            return $this->examinationModel->getRecentExaminations(5);
        } catch (\Exception $e) {
            log_message('error', 'Recent examinations error: ' . $e->getMessage());
            return [];
        }
    }

    private function getMonthlyRevenue()
    {
        try {
            return $this->financeModel->getMonthlyRevenue();
        } catch (\Exception $e) {
            log_message('error', 'Monthly revenue error: ' . $e->getMessage());
            return [];
        }
    }

    private function getBasicStats()
    {
        try {
            $patientStats = $this->patientModel->countAllResults();
            
            return [
                'total_patients' => $patientStats,
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
            ];
        } catch (\Exception $e) {
            log_message('error', 'Basic stats error: ' . $e->getMessage());
            return [
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
            ];
        }
    }
}
