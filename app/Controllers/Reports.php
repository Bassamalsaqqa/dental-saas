<?php

namespace App\Controllers;

use App\Models\PatientModel;
use App\Models\ExaminationModel;
use App\Models\AppointmentModel;
use App\Models\FinanceModel;
use App\Models\TreatmentModel;

class Reports extends BaseController
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
        try {
            // Check if user is logged in
            if (!$this->isLoggedIn()) {
                return redirect()->to('/auth/login');
            }

            $data = [
                'title' => 'Reports & Analytics',
                'date_range' => $this->request->getGet('date_range') ?? '30',
                'report_type' => $this->request->getGet('report_type') ?? 'overview',
            ];

            // Get date range
            $endDate = date('Y-m-d');
            $startDate = date('Y-m-d', strtotime("-{$data['date_range']} days"));

            // Generate reports based on type
            switch ($data['report_type']) {
                case 'patients':
                    $data['reports'] = $this->getPatientReports($startDate, $endDate);
                    break;
                case 'examinations':
                    $data['reports'] = $this->getExaminationReports($startDate, $endDate);
                    break;
                case 'appointments':
                    $data['reports'] = $this->getAppointmentReports($startDate, $endDate);
                    break;
                case 'finance':
                    $data['reports'] = $this->getFinanceReports($startDate, $endDate);
                    break;
                case 'treatments':
                    $data['reports'] = $this->getTreatmentReports($startDate, $endDate);
                    break;
                default:
                    $data['reports'] = $this->getOverviewReports($startDate, $endDate);
            }

            // Explicitly add user data to ensure it's available
            $userData = $this->getUserDataForView();
            $data = array_merge($data, $userData);


            return $this->view('reports/index', $data);
        } catch (\Exception $e) {
            // Log the error and show a simplified reports page
            log_message('error', 'Reports error: ' . $e->getMessage());
            
            $data = [
                'title' => 'Reports & Analytics',
                'date_range' => '30',
                'report_type' => 'overview',
                'reports' => $this->getBasicReports()
            ];

            // Ensure user data is still available in error state
            $userData = $this->getUserDataForView();
            $data = array_merge($data, $userData);

            return $this->view('reports/index', $data);
        }
    }

    public function getChartData()
    {
        try {
            $reportType = $this->request->getGet('report_type') ?? 'overview';
            $dateRange = $this->request->getGet('date_range') ?? '30';
            
            $endDate = date('Y-m-d');
            $startDate = date('Y-m-d', strtotime("-{$dateRange} days"));
            
            $data = [];
            
            switch ($reportType) {
                case 'patients':
                    $data = $this->getPatientReports($startDate, $endDate);
                    break;
                case 'examinations':
                    $data = $this->getExaminationReports($startDate, $endDate);
                    break;
                case 'appointments':
                    $data = $this->getAppointmentReports($startDate, $endDate);
                    break;
                case 'finance':
                    $data = $this->getFinanceReports($startDate, $endDate);
                    break;
                case 'treatments':
                    $data = $this->getTreatmentReports($startDate, $endDate);
                    break;
                default:
                    $data = $this->getOverviewReports($startDate, $endDate);
            }
            
            return $this->response->setJSON($data);
        } catch (\Exception $e) {
            log_message('error', 'Chart data error: ' . $e->getMessage());
            return $this->response->setJSON(['error' => 'Failed to fetch chart data']);
        }
    }

    public function export()
    {
        try {
            $format = $this->request->getGet('format') ?? 'pdf';
            $reportType = $this->request->getGet('type') ?? 'overview';
            $dateRange = $this->request->getGet('date_range') ?? '30';

            // Log the request for debugging
            log_message('info', 'Export request: format=' . $format . ', type=' . $reportType . ', date_range=' . $dateRange);

            $endDate = date('Y-m-d');
            $startDate = date('Y-m-d', strtotime("-{$dateRange} days"));

            $data = [
                'title' => 'Export Report',
                'report_type' => $reportType,
                'date_range' => $dateRange,
                'start_date' => $startDate,
                'end_date' => $endDate,
            ];

            switch ($reportType) {
                case 'patients':
                    $data['reports'] = $this->getPatientReports($startDate, $endDate);
                    break;
                case 'examinations':
                    $data['reports'] = $this->getExaminationReports($startDate, $endDate);
                    break;
                case 'appointments':
                    $data['reports'] = $this->getAppointmentReports($startDate, $endDate);
                    break;
                case 'finance':
                    $data['reports'] = $this->getFinanceReports($startDate, $endDate);
                    break;
                case 'treatments':
                    $data['reports'] = $this->getTreatmentReports($startDate, $endDate);
                    break;
                default:
                    $data['reports'] = $this->getOverviewReports($startDate, $endDate);
            }

            if ($format === 'pdf') {
                return $this->generatePDF($data);
            } elseif ($format === 'excel') {
                return $this->generateExcel($data);
            } else {
                return $this->generateCSV($data);
            }
        } catch (\Exception $e) {
            log_message('error', 'Export error: ' . $e->getMessage());
            
            // Return a simple error response
            $this->response->setStatusCode(500);
            return $this->response->setJSON([
                'error' => 'Export failed: ' . $e->getMessage()
            ]);
        }
    }

    public function test()
    {
        return $this->response->setJSON([
            'status' => 'success',
            'message' => 'Reports controller is working',
            'timestamp' => date('Y-m-d H:i:s')
        ]);
    }

    private function getOverviewReports($startDate, $endDate)
    {
        // Debug: Log the date range being used
        log_message('info', "Reports: Generating overview reports for date range: {$startDate} to {$endDate}");
        
        $monthlyRevenueData = $this->getMonthlyRevenueData();
        $examinationTypesData = $this->getExaminationTypesData();
        $appointmentStatusData = $this->getAppointmentStatusData();
        
        // Debug: Log the chart data
        log_message('info', 'Reports: Monthly revenue data: ' . json_encode($monthlyRevenueData));
        log_message('info', 'Reports: Examination types data: ' . json_encode($examinationTypesData));
        log_message('info', 'Reports: Appointment status data: ' . json_encode($appointmentStatusData));
        
        return [
            'summary' => [
                'total_patients' => $this->patientModel->countAll(),
                'new_patients' => $this->patientModel->where('created_at >=', $startDate)->countAll(),
                'total_examinations' => $this->examinationModel->countAll(),
                'examinations_this_period' => $this->examinationModel->where('examination_date >=', $startDate)->countAll(),
                'total_appointments' => $this->appointmentModel->countAll(),
                'appointments_this_period' => $this->appointmentModel->where('appointment_date >=', $startDate)->countAll(),
                'total_revenue' => $this->financeModel->selectSum('total_amount')->where('transaction_type', 'payment')->get()->getRow()->total_amount ?? 0,
                'revenue_this_period' => $this->financeModel->selectSum('total_amount')->where('transaction_type', 'payment')->where('created_at >=', $startDate)->get()->getRow()->total_amount ?? 0,
            ],
            'charts' => [
                'monthly_patients' => $this->getMonthlyPatientsData(),
                'monthly_revenue' => $monthlyRevenueData,
                'examination_types' => $examinationTypesData,
                'appointment_status' => $appointmentStatusData,
            ]
        ];
    }

    private function getPatientReports($startDate, $endDate)
    {
        return [
            'patients' => $this->patientModel->where('created_at >=', $startDate)->findAll(),
            'age_groups' => $this->getAgeGroupData(),
            'gender_distribution' => $this->getGenderDistributionData(),
            'new_vs_returning' => $this->getNewVsReturningData($startDate, $endDate),
        ];
    }

    private function getExaminationReports($startDate, $endDate)
    {
        return [
            'examinations' => $this->examinationModel->where('examination_date >=', $startDate)->findAll(),
            'examination_types' => $this->getExaminationTypesData(),
            'monthly_examinations' => $this->getMonthlyExaminationsData(),
            'tooth_conditions' => $this->getToothConditionsData(),
        ];
    }

    private function getAppointmentReports($startDate, $endDate)
    {
        return [
            'appointments' => $this->appointmentModel->where('appointment_date >=', $startDate)->findAll(),
            'appointment_types' => $this->getAppointmentTypesData(),
            'monthly_appointments' => $this->getMonthlyAppointmentsData(),
            'status_distribution' => $this->getAppointmentStatusData(),
        ];
    }

    private function getFinanceReports($startDate, $endDate)
    {
        return [
            'transactions' => $this->financeModel->where('created_at >=', $startDate)->findAll(),
            'monthly_revenue' => $this->getMonthlyRevenueData(),
            'payment_methods' => $this->getPaymentMethodsData(),
            'outstanding_payments' => $this->getOutstandingPaymentsData(),
        ];
    }

    private function getTreatmentReports($startDate, $endDate)
    {
        return [
            'treatments' => $this->treatmentModel->where('start_date >=', $startDate)->findAll(),
            'treatment_types' => $this->getTreatmentTypesData(),
            'monthly_treatments' => $this->getMonthlyTreatmentsData(),
            'status_distribution' => $this->getTreatmentStatusData(),
        ];
    }

    // Helper methods for data generation
    private function getMonthlyPatientsData()
    {
        $data = [];
        for ($i = 11; $i >= 0; $i--) {
            $date = date('Y-m-01', strtotime("-{$i} months"));
            $count = $this->patientModel->where('MONTH(created_at)', date('n', strtotime($date)))
                                      ->where('YEAR(created_at)', date('Y', strtotime($date)))
                                      ->countAll();
            $data[] = [
                'month' => date('M Y', strtotime($date)),
                'count' => $count
            ];
        }
        return $data;
    }

    private function getMonthlyRevenueData()
    {
        $data = [];
        for ($i = 11; $i >= 0; $i--) {
            $date = date('Y-m-01', strtotime("-{$i} months"));
            $revenue = $this->financeModel->selectSum('total_amount')
                                        ->where('transaction_type', 'payment')
                                        ->where('MONTH(created_at)', date('n', strtotime($date)))
                                        ->where('YEAR(created_at)', date('Y', strtotime($date)))
                                        ->get()->getRow()->total_amount ?? 0;
            $data[] = [
                'month' => date('M Y', strtotime($date)),
                'amount' => (float)$revenue
            ];
        }
        return $data;
    }

    private function getExaminationTypesData()
    {
        $types = $this->examinationModel->select('examination_type, COUNT(*) as count')
                                       ->groupBy('examination_type')
                                       ->findAll();
        return $types;
    }

    private function getAppointmentStatusData()
    {
        $statuses = $this->appointmentModel->select('status, COUNT(*) as count')
                                          ->groupBy('status')
                                          ->findAll();
        return $statuses;
    }

    private function getAgeGroupData()
    {
        try {
            $patients = $this->patientModel->select('date_of_birth')->findAll();
            $ageGroups = [
                '0-18' => 0,
                '19-35' => 0,
                '36-50' => 0,
                '51-65' => 0,
                '65+' => 0,
            ];
            
            foreach ($patients as $patient) {
                if ($patient['date_of_birth']) {
                    $age = date_diff(date_create($patient['date_of_birth']), date_create('today'))->y;
                    
                    if ($age <= 18) {
                        $ageGroups['0-18']++;
                    } elseif ($age <= 35) {
                        $ageGroups['19-35']++;
                    } elseif ($age <= 50) {
                        $ageGroups['36-50']++;
                    } elseif ($age <= 65) {
                        $ageGroups['51-65']++;
                    } else {
                        $ageGroups['65+']++;
                    }
                }
            }
            
            return $ageGroups;
        } catch (\Exception $e) {
            log_message('error', 'Age group data error: ' . $e->getMessage());
            return [
                '0-18' => 0,
                '19-35' => 0,
                '36-50' => 0,
                '51-65' => 0,
                '65+' => 0,
            ];
        }
    }

    private function getGenderDistributionData()
    {
        return $this->patientModel->select('gender, COUNT(*) as count')
                                 ->groupBy('gender')
                                 ->findAll();
    }

    private function getNewVsReturningData($startDate, $endDate)
    {
        $new = $this->patientModel->where('created_at >=', $startDate)->countAll();
        $returning = $this->patientModel->where('created_at <', $startDate)->countAll();
        
        return [
            'new' => $new,
            'returning' => $returning,
        ];
    }

    private function getMonthlyExaminationsData()
    {
        $data = [];
        for ($i = 11; $i >= 0; $i--) {
            $date = date('Y-m-01', strtotime("-{$i} months"));
            $count = $this->examinationModel->where('MONTH(examination_date)', date('n', strtotime($date)))
                                           ->where('YEAR(examination_date)', date('Y', strtotime($date)))
                                           ->countAll();
            $data[] = [
                'month' => date('M Y', strtotime($date)),
                'count' => $count
            ];
        }
        return $data;
    }

    private function getToothConditionsData()
    {
        // This would need to be implemented based on your odontogram data
        return [];
    }

    private function getAppointmentTypesData()
    {
        return $this->appointmentModel->select('appointment_type, COUNT(*) as count')
                                     ->groupBy('appointment_type')
                                     ->findAll();
    }

    private function getMonthlyAppointmentsData()
    {
        $data = [];
        for ($i = 11; $i >= 0; $i--) {
            $date = date('Y-m-01', strtotime("-{$i} months"));
            $count = $this->appointmentModel->where('MONTH(appointment_date)', date('n', strtotime($date)))
                                           ->where('YEAR(appointment_date)', date('Y', strtotime($date)))
                                           ->countAll();
            $data[] = [
                'month' => date('M Y', strtotime($date)),
                'count' => $count
            ];
        }
        return $data;
    }

    private function getPaymentMethodsData()
    {
        return $this->financeModel->select('payment_method, COUNT(*) as count')
                                 ->groupBy('payment_method')
                                 ->findAll();
    }

    private function getOutstandingPaymentsData()
    {
        return $this->financeModel->where('payment_status', 'pending')->findAll();
    }

    private function getTreatmentTypesData()
    {
        return $this->treatmentModel->select('treatment_type, COUNT(*) as count')
                                   ->groupBy('treatment_type')
                                   ->findAll();
    }

    private function getMonthlyTreatmentsData()
    {
        $data = [];
        for ($i = 11; $i >= 0; $i--) {
            $date = date('Y-m-01', strtotime("-{$i} months"));
            $count = $this->treatmentModel->where('MONTH(start_date)', date('n', strtotime($date)))
                                         ->where('YEAR(start_date)', date('Y', strtotime($date)))
                                         ->countAll();
            $data[] = [
                'month' => date('M Y', strtotime($date)),
                'count' => $count
            ];
        }
        return $data;
    }

    private function getTreatmentStatusData()
    {
        return $this->treatmentModel->select('status, COUNT(*) as count')
                                   ->groupBy('status')
                                   ->findAll();
    }

    private function generatePDF($data)
    {
        // Generate HTML content for PDF
        $html = $this->generateReportHTML($data);
        
        // Set headers for PDF download
        $this->response->setHeader('Content-Type', 'application/pdf');
        $this->response->setHeader('Content-Disposition', 'attachment; filename="report_' . date('Y-m-d') . '.pdf"');
        
        // For now, return HTML that can be printed as PDF by browser
        // In production, you would use a library like TCPDF or DomPDF
        $this->response->setHeader('Content-Type', 'text/html');
        $this->response->setHeader('Content-Disposition', 'inline; filename="report_' . date('Y-m-d') . '.html"');
        
        return $html;
    }

    private function generateExcel($data)
    {
        // Generate CSV content (Excel can open CSV files)
        $csv = $this->generateReportCSV($data);
        
        // Set headers for Excel download
        $this->response->setHeader('Content-Type', 'application/vnd.ms-excel');
        $this->response->setHeader('Content-Disposition', 'attachment; filename="report_' . date('Y-m-d') . '.xls"');
        
        return $csv;
    }

    private function generateCSV($data)
    {
        $csv = $this->generateReportCSV($data);
        
        // Set headers for CSV download
        $this->response->setHeader('Content-Type', 'text/csv');
        $this->response->setHeader('Content-Disposition', 'attachment; filename="report_' . date('Y-m-d') . '.csv"');
        
        return $csv;
    }

    private function generateReportHTML($data)
    {
        $html = '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Dental Practice Report - ' . date('Y-m-d') . '</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #333; padding-bottom: 20px; }
        .section { margin-bottom: 30px; }
        .section h2 { color: #333; border-bottom: 1px solid #ccc; padding-bottom: 10px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; font-weight: bold; }
        .summary { display: flex; flex-wrap: wrap; gap: 20px; margin-bottom: 20px; }
        .summary-item { background: #f9f9f9; padding: 15px; border-radius: 5px; min-width: 200px; }
        .summary-item h3 { margin: 0 0 10px 0; color: #333; }
        .summary-item p { margin: 0; font-size: 18px; font-weight: bold; color: #666; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Dental Practice Report</h1>
        <p>Generated on: ' . date('Y-m-d H:i:s') . '</p>
        <p>Report Type: ' . ucfirst($data['report_type']) . '</p>
        <p>Date Range: ' . $data['start_date'] . ' to ' . $data['end_date'] . '</p>
    </div>';

        if (isset($data['reports']['summary'])) {
            $html .= '<div class="section">
                <h2>Summary</h2>
                <div class="summary">';
            
            foreach ($data['reports']['summary'] as $key => $value) {
                $label = ucwords(str_replace('_', ' ', $key));
                $html .= '<div class="summary-item">
                    <h3>' . $label . '</h3>
                    <p>' . number_format($value) . '</p>
                </div>';
            }
            
            $html .= '</div></div>';
        }

        // Add detailed data tables
        if (isset($data['reports']['patients']) && is_array($data['reports']['patients'])) {
            $html .= '<div class="section">
                <h2>Patients</h2>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Phone</th>
                            <th>Email</th>
                            <th>Created Date</th>
                        </tr>
                    </thead>
                    <tbody>';
            
            foreach ($data['reports']['patients'] as $patient) {
                $html .= '<tr>
                    <td>' . ($patient['id'] ?? '') . '</td>
                    <td>' . ($patient['first_name'] ?? '') . ' ' . ($patient['last_name'] ?? '') . '</td>
                    <td>' . ($patient['phone'] ?? '') . '</td>
                    <td>' . ($patient['email'] ?? '') . '</td>
                    <td>' . ($patient['created_at'] ?? '') . '</td>
                </tr>';
            }
            
            $html .= '</tbody></table></div>'; 
        }

        if (isset($data['reports']['examinations']) && is_array($data['reports']['examinations'])) {
            $html .= '<div class="section">
                <h2>Examinations</h2>
                <table>
                    <thead> 
                        <tr>
                            <th>ID</th>
                            <th>Patient ID</th>
                            <th>Examination Type</th>
                            <th>Examination Date</th>
                            <th>Notes</th>
                        </tr>
                    </thead>
                    <tbody>';
            
            foreach ($data['reports']['examinations'] as $exam) {
                $html .= '<tr>
                    <td>' . ($exam['id'] ?? '') . '</td>
                    <td>' . ($exam['patient_id'] ?? '') . '</td>
                    <td>' . ($exam['examination_type'] ?? '') . '</td>
                    <td>' . ($exam['examination_date'] ?? '') . '</td>
                    <td>' . ($exam['notes'] ?? '') . '</td>
                </tr>';
            }
            
            $html .= '</tbody></table></div>';
        }

        if (isset($data['reports']['appointments']) && is_array($data['reports']['appointments'])) {
            $html .= '<div class="section">
                <h2>Appointments</h2>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Patient ID</th>
                            <th>Appointment Date</th>
                            <th>Appointment Type</th>
                            <th>Status</th>
                            <th>Notes</th>
                        </tr>
                    </thead>
                    <tbody>';
            
            foreach ($data['reports']['appointments'] as $appointment) {
                $html .= '<tr>
                    <td>' . ($appointment['id'] ?? '') . '</td>
                    <td>' . ($appointment['patient_id'] ?? '') . '</td>
                    <td>' . ($appointment['appointment_date'] ?? '') . '</td>
                    <td>' . ($appointment['appointment_type'] ?? '') . '</td>
                    <td>' . ($appointment['status'] ?? '') . '</td>
                    <td>' . ($appointment['notes'] ?? '') . '</td>
                </tr>';
            }
            
            $html .= '</tbody></table></div>';
        }

        if (isset($data['reports']['transactions']) && is_array($data['reports']['transactions'])) {
            $html .= '<div class="section">
                <h2>Financial Transactions</h2>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Patient ID</th>
                            <th>Transaction Type</th>
                            <th>Amount</th>
                            <th>Payment Method</th>
                            <th>Payment Status</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>';
            
            foreach ($data['reports']['transactions'] as $transaction) {
                $html .= '<tr>
                    <td>' . ($transaction['id'] ?? '') . '</td>
                    <td>' . ($transaction['patient_id'] ?? '') . '</td>
                    <td>' . ($transaction['transaction_type'] ?? '') . '</td>
                    <td>' . number_format($transaction['total_amount'] ?? 0, 2) . '</td>
                    <td>' . ($transaction['payment_method'] ?? '') . '</td>
                    <td>' . ($transaction['payment_status'] ?? '') . '</td>
                    <td>' . ($transaction['created_at'] ?? '') . '</td>
                </tr>';
            }
            
            $html .= '</tbody></table></div>';
        }

        $html .= '</body></html>';
        
        return $html;
    }

    private function generateReportCSV($data)
    {
        $csv = "Dental Practice Report\n";
        $csv .= "Generated on: " . date('Y-m-d H:i:s') . "\n";
        $csv .= "Report Type: " . ucfirst($data['report_type']) . "\n";
        $csv .= "Date Range: " . $data['start_date'] . " to " . $data['end_date'] . "\n\n";

        if (isset($data['reports']['summary'])) {
            $csv .= "SUMMARY\n";
            $csv .= "Metric,Value\n";
            
            foreach ($data['reports']['summary'] as $key => $value) {
                $label = ucwords(str_replace('_', ' ', $key));
                $csv .= '"' . $label . '",' . number_format($value) . "\n";
            }
            $csv .= "\n";
        }

        // Add detailed data based on report type
        if (isset($data['reports']['patients']) && is_array($data['reports']['patients'])) {
            $csv .= "PATIENTS\n";
            $csv .= "ID,First Name,Last Name,Phone,Email,Created Date\n";
            
            foreach ($data['reports']['patients'] as $patient) {
                $csv .= '"' . ($patient['id'] ?? '') . '",';
                $csv .= '"' . ($patient['first_name'] ?? '') . '",';
                $csv .= '"' . ($patient['last_name'] ?? '') . '",';
                $csv .= '"' . ($patient['phone'] ?? '') . '",';
                $csv .= '"' . ($patient['email'] ?? '') . '",';
                $csv .= '"' . ($patient['created_at'] ?? '') . '"' . "\n";
            }
        }

        if (isset($data['reports']['examinations']) && is_array($data['reports']['examinations'])) {
            $csv .= "\nEXAMINATIONS\n";
            $csv .= "ID,Patient ID,Examination Type,Examination Date,Notes\n";
            
            foreach ($data['reports']['examinations'] as $exam) {
                $csv .= '"' . ($exam['id'] ?? '') . '",';
                $csv .= '"' . ($exam['patient_id'] ?? '') . '",';
                $csv .= '"' . ($exam['examination_type'] ?? '') . '",';
                $csv .= '"' . ($exam['examination_date'] ?? '') . '",';
                $csv .= '"' . ($exam['notes'] ?? '') . '"' . "\n";
            }
        }

        if (isset($data['reports']['appointments']) && is_array($data['reports']['appointments'])) {
            $csv .= "\nAPPOINTMENTS\n";
            $csv .= "ID,Patient ID,Appointment Date,Appointment Type,Status,Notes\n";
            
            foreach ($data['reports']['appointments'] as $appointment) {
                $csv .= '"' . ($appointment['id'] ?? '') . '",';
                $csv .= '"' . ($appointment['patient_id'] ?? '') . '",';
                $csv .= '"' . ($appointment['appointment_date'] ?? '') . '",';
                $csv .= '"' . ($appointment['appointment_type'] ?? '') . '",';
                $csv .= '"' . ($appointment['status'] ?? '') . '",';
                $csv .= '"' . ($appointment['notes'] ?? '') . '"' . "\n";
            }
        }

        if (isset($data['reports']['transactions']) && is_array($data['reports']['transactions'])) {
            $csv .= "\nFINANCIAL TRANSACTIONS\n";
            $csv .= "ID,Patient ID,Transaction Type,Amount,Payment Method,Payment Status,Date\n";
            
            foreach ($data['reports']['transactions'] as $transaction) {
                $csv .= '"' . ($transaction['id'] ?? '') . '",';
                $csv .= '"' . ($transaction['patient_id'] ?? '') . '",';
                $csv .= '"' . ($transaction['transaction_type'] ?? '') . '",';
                $csv .= '"' . ($transaction['total_amount'] ?? '') . '",';
                $csv .= '"' . ($transaction['payment_method'] ?? '') . '",';
                $csv .= '"' . ($transaction['payment_status'] ?? '') . '",';
                $csv .= '"' . ($transaction['created_at'] ?? '') . '"' . "\n";
            }
        }

        return $csv;
    }

    private function getBasicReports()
    {
        try {
            return [
                'summary' => [
                    'total_patients' => $this->patientModel->countAllResults(),
                    'new_patients' => 0,
                    'total_examinations' => 0,
                    'examinations_this_period' => 0,
                    'total_appointments' => 0,
                    'appointments_this_period' => 0,
                    'total_revenue' => 0,
                    'revenue_this_period' => 0,
                ],
                'charts' => [
                    'monthly_patients' => [],
                    'monthly_revenue' => [],
                    'examination_types' => [],
                    'appointment_status' => [],
                ]
            ];
        } catch (\Exception $e) {
            log_message('error', 'Basic reports error: ' . $e->getMessage());
            return [
                'summary' => [
                    'total_patients' => 0,
                    'new_patients' => 0,
                    'total_examinations' => 0,
                    'examinations_this_period' => 0,
                    'total_appointments' => 0,
                    'appointments_this_period' => 0,
                    'total_revenue' => 0,
                    'revenue_this_period' => 0,
                ],
                'charts' => [
                    'monthly_patients' => [],
                    'monthly_revenue' => [],
                    'examination_types' => [],
                    'appointment_status' => [],
                ]
            ];
        }
    }
}
