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
        $clinicId = session()->get('active_clinic_id');
        if (!$clinicId) {
            return redirect()->to('/clinic/select')->with('error', 'Please select a clinic to view the dashboard.');
        }
        
        try {
            $data = [
                'title' => 'Dental Management Dashboard',
                'pageTitle' => 'Dashboard',
                'stats' => $this->getDashboardStatsByClinic($clinicId),
                'recent_patients' => $this->getRecentPatientsByClinic($clinicId),
                'upcoming_appointments' => $this->getUpcomingAppointmentsByClinic($clinicId),
                'recent_examinations' => $this->getRecentExaminationsByClinic($clinicId),
                'monthly_revenue' => $this->getMonthlyRevenueByClinic($clinicId)
            ];

            return $this->view('dashboard/index', $data);
        } catch (\Exception $e) {
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

    private function getDashboardStatsByClinic($clinicId)
    {
        $patientStats = $this->patientModel->getPatientStatsByClinic($clinicId);
        $examinationStats = $this->examinationModel->getExaminationStatsByClinic($clinicId);
        $appointmentStats = $this->appointmentModel->getAppointmentStatsByClinic($clinicId);
        $financeStats = $this->financeModel->getFinanceStatsByClinic($clinicId);
        $treatmentStats = $this->treatmentModel->getTreatmentStatsByClinic($clinicId);

        return [
            'total_patients' => $patientStats['total_patients'] ?? 0,
            'total_examinations' => $examinationStats['total_examinations'] ?? 0,
            'today_examinations' => $examinationStats['today_examinations'] ?? 0,
            'total_appointments' => $appointmentStats['total_appointments'] ?? 0,
            'today_appointments' => $appointmentStats['today_appointments'] ?? 0,
            'upcoming_appointments' => $appointmentStats['upcoming_appointments'] ?? 0,
            'total_revenue' => $financeStats['total_revenue'] ?? 0,
            'pending_payments' => $financeStats['pending_payments'] ?? 0,
            'overdue_payments' => $financeStats['overdue_payments'] ?? 0,
            'active_treatments' => $treatmentStats['active_treatments'] ?? 0,
            'completed_treatments' => $treatmentStats['completed_treatments'] ?? 0
        ];
    }

    public function getStats()
    {
        $clinicId = session()->get('active_clinic_id');
        if (!$clinicId) {
            return $this->response->setStatusCode(403)->setJSON(['error' => 'TENANT_CONTEXT_REQUIRED']);
        }
        $stats = $this->getDashboardStatsByClinic($clinicId);
        return $this->response->setJSON($stats);
    }

    public function testChartData()
    {
        return $this->response->setJSON(['test' => 'success', 'message' => 'Chart data endpoint is working']);
    }

    public function getChartData()
    {
        try {
            $clinicId = session()->get('active_clinic_id');
            if (!$clinicId) {
                return $this->response->setStatusCode(403)->setJSON(['error' => 'TENANT_CONTEXT_REQUIRED']);
            }

            $monthlyRevenue = $this->getMonthlyRevenueForChartByClinic($clinicId);
            $treatmentTypes = $this->getTreatmentTypesForChartByClinic($clinicId);
            
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

    private function getMonthlyRevenueForChartByClinic($clinicId)
    {
        try {
            $currentYear = date('Y');
            $monthlyRevenue = $this->financeModel->getMonthlyRevenueByClinic($clinicId, $currentYear);
            
            $revenueData = [];
            $existingData = [];
            
            foreach ($monthlyRevenue as $data) {
                $existingData[(int)$data['month']] = (float)$data['total_amount'];
            }
            
            for ($i = 1; $i <= 12; $i++) {
                $revenueData[] = [
                    'month' => $i,
                    'year' => $currentYear,
                    'total_amount' => $existingData[$i] ?? 0
                ];
            }
            
            return $revenueData;
        } catch (\Exception $e) {
            log_message('error', 'Monthly revenue chart error: ' . $e->getMessage());
            return [];
        }
    }

    private function getTreatmentTypesForChartByClinic($clinicId)
    {
        try {
            $treatmentTypes = $this->treatmentModel->getTreatmentTypesStatsByClinic($clinicId);
            
            $chartData = [];
            $defaultTypes = ['Cleaning', 'Extraction', 'Filling', 'Crown', 'Root Canal', 'Orthodontic', 'Implant', 'Other'];
            
            foreach ($defaultTypes as $type) {
                $found = false;
                foreach ($treatmentTypes as $treatment) {
                    if (strtolower($treatment['treatment_type'] ?? '') === strtolower($type)) {
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

    private function getRecentPatientsByClinic($clinicId)
    {
        try {
            return $this->patientModel->where('clinic_id', $clinicId)->orderBy('created_at', 'DESC')->limit(5)->findAll();
        } catch (\Exception $e) {
            log_message('error', 'Recent patients error: ' . $e->getMessage());
            return [];
        }
    }

    private function getUpcomingAppointmentsByClinic($clinicId)
    {
        try {
            return $this->appointmentModel->getUpcomingAppointmentsByClinic($clinicId, 5);
        } catch (\Exception $e) {
            log_message('error', 'Upcoming appointments error: ' . $e->getMessage());
            return [];
        }
    }

    private function getRecentExaminationsByClinic($clinicId)
    {
        try {
            return $this->examinationModel->getRecentExaminationsByClinic($clinicId, 5);
        } catch (\Exception $e) {
            log_message('error', 'Recent examinations error: ' . $e->getMessage());
            return [];
        }
    }

    private function getMonthlyRevenueByClinic($clinicId)
    {
        try {
            return $this->financeModel->getMonthlyRevenueByClinic($clinicId);
        } catch (\Exception $e) {
            log_message('error', 'Monthly revenue error: ' . $e->getMessage());
            return [];
        }
    }

    private function getBasicStats()
    {
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
