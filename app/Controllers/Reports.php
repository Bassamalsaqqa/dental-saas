<?php

namespace App\Controllers;

use App\Models\PatientModel;
use App\Models\ExaminationModel;
use App\Models\AppointmentModel;
use App\Models\FinanceModel;
use App\Models\ProviderPayoutModel;
use App\Models\TreatmentModel;

class Reports extends BaseController
{
    private const ALLOWED_REPORT_TYPES = ['overview', 'patients', 'examinations', 'appointments', 'finance', 'treatments'];
    private const ALLOWED_DATE_RANGES = ['7', '30', '90', '365'];
    private const ALLOWED_EXPORT_FORMATS = ['pdf', 'excel', 'csv'];
    private const ALLOWED_DATE_MODES = ['relative', 'custom', 'as_of'];

    protected $patientModel;
    protected $examinationModel;
    protected $appointmentModel;
    protected $financeModel;
    protected $providerPayoutModel;
    protected $treatmentModel;
    protected $storageService;

    public function __construct()
    {
        $this->patientModel = new PatientModel();
        $this->examinationModel = new ExaminationModel();
        $this->appointmentModel = new AppointmentModel();
        $this->financeModel = new FinanceModel();
        $this->providerPayoutModel = new ProviderPayoutModel();
        $this->treatmentModel = new TreatmentModel();
        $this->storageService = new \App\Services\StorageService();
    }

    private function requireTenantClinicId()
    {
        $clinicId = session()->get('active_clinic_id');
        return !empty($clinicId) ? (int) $clinicId : null;
    }

    private function sanitizeReportType(?string $value): string
    {
        $value = (string) ($value ?? 'overview');
        return in_array($value, self::ALLOWED_REPORT_TYPES, true) ? $value : 'overview';
    }

    private function sanitizeDateRange(?string $value): string
    {
        $value = (string) ($value ?? '30');
        return in_array($value, self::ALLOWED_DATE_RANGES, true) ? $value : '30';
    }

    private function sanitizeExportFormat(?string $value): string
    {
        $value = strtolower((string) ($value ?? 'csv'));
        return in_array($value, self::ALLOWED_EXPORT_FORMATS, true) ? $value : 'csv';
    }

    private function sanitizeDateMode(?string $value): string
    {
        $value = strtolower((string) ($value ?? 'relative'));
        return in_array($value, self::ALLOWED_DATE_MODES, true) ? $value : 'relative';
    }

    private function sanitizeDateInput(?string $value): ?string
    {
        $value = trim((string) ($value ?? ''));
        if ($value === '') {
            return null;
        }
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
            return null;
        }
        return $value;
    }

    private function getReportWindow(string $dateRange): array
    {
        $days = (int) $dateRange;
        $end = date('Y-m-d');
        $start = date('Y-m-d', strtotime("-{$days} days"));
        return [$start, $end];
    }

    private function getReportWindowAnchored(string $dateRange, string $endDate): array
    {
        $days = (int) $dateRange;
        $start = date('Y-m-d', strtotime($endDate . " -{$days} days"));
        return [$start, $endDate];
    }

    private function parseReportFilters(string $source = 'get'): array
    {
        $input = strtolower($source) === 'post' ? 'getPost' : 'getGet';
        $reportType = $this->sanitizeReportType($this->request->{$input}('report_type') ?? $this->request->{$input}('type'));
        $dateRange = $this->sanitizeDateRange($this->request->{$input}('date_range'));
        $dateMode = $this->sanitizeDateMode($this->request->{$input}('date_mode'));
        $customFrom = $this->sanitizeDateInput($this->request->{$input}('custom_from'));
        $customTo = $this->sanitizeDateInput($this->request->{$input}('custom_to'));
        $asOfDate = $this->sanitizeDateInput($this->request->{$input}('as_of_date')) ?: date('Y-m-d');

        if ($dateMode === 'custom') {
            if (!$customFrom || !$customTo) {
                $dateMode = 'relative';
            } elseif ($customFrom > $customTo) {
                [$customFrom, $customTo] = [$customTo, $customFrom];
            }
        }

        if ($dateMode === 'custom' && $customFrom && $customTo) {
            [$startDate, $endDate] = [$customFrom, $customTo];
        } elseif ($dateMode === 'as_of') {
            [$startDate, $endDate] = $this->getReportWindowAnchored($dateRange, $asOfDate);
        } else {
            [$startDate, $endDate] = $this->getReportWindow($dateRange);
            $dateMode = 'relative';
        }

        return [
            'report_type' => $reportType,
            'date_mode' => $dateMode,
            'date_range' => $dateRange,
            'custom_from' => $customFrom,
            'custom_to' => $customTo,
            'as_of_date' => $asOfDate,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'period_label' => $dateMode === 'custom'
                ? ($startDate . ' to ' . $endDate)
                : ($dateMode === 'as_of'
                    ? ('As of ' . $asOfDate . ' (window: ' . $startDate . ' to ' . $endDate . ')')
                    : ('Last ' . $dateRange . ' days (' . $startDate . ' to ' . $endDate . ')')),
        ];
    }

    private function makeDetailTable(string $id, string $title, array $columns, array $rows): array
    {
        return [
            'id' => $id,
            'title' => $title,
            'columns' => $columns,
            'rows' => $rows,
            'row_count' => count($rows),
        ];
    }

    private function buildDetailTables(string $reportType, array $reports): array
    {
        switch ($reportType) {
            case 'overview':
                return [
                    $this->makeDetailTable('overview_finance_transactions', 'Overview Revenue Transactions', [
                        ['key' => 'month_label', 'label' => 'Month'],
                        ['key' => 'transaction_id', 'label' => 'Transaction'],
                        ['key' => 'patient_id', 'label' => 'Patient'],
                        ['key' => 'payment_method', 'label' => 'Payment Method'],
                        ['key' => 'payment_status', 'label' => 'Status'],
                        ['key' => 'total_amount', 'label' => 'Amount'],
                        ['key' => 'created_at', 'label' => 'Created'],
                    ], $reports['detail_rows']['finance_transactions'] ?? []),
                    $this->makeDetailTable('overview_examinations', 'Overview Examinations', [
                        ['key' => 'examination_type', 'label' => 'Type'],
                        ['key' => 'patient_id', 'label' => 'Patient'],
                        ['key' => 'examination_date', 'label' => 'Date'],
                        ['key' => 'notes', 'label' => 'Notes'],
                    ], $reports['detail_rows']['examinations'] ?? []),
                    $this->makeDetailTable('overview_appointments', 'Overview Appointments', [
                        ['key' => 'status', 'label' => 'Status'],
                        ['key' => 'appointment_type', 'label' => 'Type'],
                        ['key' => 'patient_id', 'label' => 'Patient'],
                        ['key' => 'appointment_date', 'label' => 'Date'],
                        ['key' => 'notes', 'label' => 'Notes'],
                    ], $reports['detail_rows']['appointments'] ?? []),
                ];
            case 'patients':
                return [
                    $this->makeDetailTable('patients', 'Patients Included', [
                        ['key' => 'patient_id', 'label' => 'Patient ID'],
                        ['key' => 'full_name', 'label' => 'Name'],
                        ['key' => 'phone', 'label' => 'Phone'],
                        ['key' => 'email', 'label' => 'Email'],
                        ['key' => 'created_at', 'label' => 'Created'],
                    ], array_map(static function ($row) {
                        return [
                            'patient_id' => $row['patient_id'] ?? $row['id'] ?? '',
                            'full_name' => trim((string) (($row['first_name'] ?? '') . ' ' . ($row['last_name'] ?? ''))),
                            'phone' => $row['phone'] ?? '',
                            'email' => $row['email'] ?? '',
                            'created_at' => $row['created_at'] ?? '',
                        ];
                    }, $reports['patients'] ?? [])),
                ];
            case 'examinations':
                return [
                    $this->makeDetailTable('examinations', 'Examinations Included', [
                        ['key' => 'id', 'label' => 'ID'],
                        ['key' => 'patient_id', 'label' => 'Patient'],
                        ['key' => 'examination_type', 'label' => 'Type'],
                        ['key' => 'examination_date', 'label' => 'Date'],
                        ['key' => 'notes', 'label' => 'Notes'],
                    ], $reports['examinations'] ?? []),
                ];
            case 'appointments':
                return [
                    $this->makeDetailTable('appointments', 'Appointments Included', [
                        ['key' => 'id', 'label' => 'ID'],
                        ['key' => 'patient_id', 'label' => 'Patient'],
                        ['key' => 'appointment_date', 'label' => 'Date'],
                        ['key' => 'appointment_type', 'label' => 'Type'],
                        ['key' => 'status', 'label' => 'Status'],
                    ], $reports['appointments'] ?? []),
                ];
            case 'treatments':
                return [
                    $this->makeDetailTable('treatments', 'Treatments Included', [
                        ['key' => 'id', 'label' => 'ID'],
                        ['key' => 'patient_id', 'label' => 'Patient'],
                        ['key' => 'treatment_type', 'label' => 'Type'],
                        ['key' => 'start_date', 'label' => 'Start'],
                        ['key' => 'status', 'label' => 'Status'],
                    ], $reports['treatments'] ?? []),
                ];
            case 'finance':
                return [
                    $this->makeDetailTable('transactions', 'Financial Transactions', [
                        ['key' => 'transaction_id', 'label' => 'Transaction'],
                        ['key' => 'patient_id', 'label' => 'Patient'],
                        ['key' => 'transaction_type', 'label' => 'Type'],
                        ['key' => 'payment_method', 'label' => 'Payment Method'],
                        ['key' => 'payment_status', 'label' => 'Status'],
                        ['key' => 'total_amount', 'label' => 'Amount'],
                        ['key' => 'created_at', 'label' => 'Created'],
                    ], $reports['transactions'] ?? []),
                    $this->makeDetailTable('ar_aging_detail', 'A/R Aging Detail', [
                        ['key' => 'transaction_id', 'label' => 'Invoice'],
                        ['key' => 'patient_number', 'label' => 'Patient #'],
                        ['key' => 'full_name', 'label' => 'Patient'],
                        ['key' => 'due_date', 'label' => 'Due Date'],
                        ['key' => 'days_past_due', 'label' => 'Days Past Due'],
                        ['key' => 'aging_bucket', 'label' => 'Bucket'],
                        ['key' => 'balance_due', 'label' => 'Balance Due'],
                    ], array_map(static function ($row) {
                        $row['full_name'] = trim((string) (($row['first_name'] ?? '') . ' ' . ($row['last_name'] ?? '')));
                        return $row;
                    }, $reports['ar_aging_detail'] ?? [])),
                    $this->makeDetailTable('provider_payout_breakdown', 'Provider Payout Breakdown', [
                        ['key' => 'provider_label', 'label' => 'Provider'],
                        ['key' => 'payout_count', 'label' => 'Payouts'],
                        ['key' => 'gross_amount_total', 'label' => 'Gross'],
                        ['key' => 'provider_share_total', 'label' => 'Provider Share'],
                        ['key' => 'provider_paid_total', 'label' => 'Paid'],
                        ['key' => 'provider_balance_total', 'label' => 'Balance'],
                    ], $reports['provider_payout_breakdown'] ?? []),
                    $this->makeDetailTable('provider_payout_rows', 'Provider Payout Rows', [
                        ['key' => 'transaction_id', 'label' => 'Transaction'],
                        ['key' => 'patient_number', 'label' => 'Patient #'],
                        ['key' => 'patient_name', 'label' => 'Patient'],
                        ['key' => 'provider_label', 'label' => 'Provider'],
                        ['key' => 'status', 'label' => 'Status'],
                        ['key' => 'gross_amount', 'label' => 'Gross'],
                        ['key' => 'provider_share_amount', 'label' => 'Provider Share'],
                        ['key' => 'provider_paid_amount', 'label' => 'Paid'],
                        ['key' => 'provider_balance_due', 'label' => 'Balance'],
                    ], $reports['provider_payout_rows'] ?? []),
                ];
            default:
                return [];
        }
    }

    private function buildReportDataByType(string $reportType, string $startDate, string $endDate, ?string $asOfDate = null): array
    {
        switch ($reportType) {
            case 'patients':
                return $this->getPatientReports($startDate, $endDate);
            case 'examinations':
                return $this->getExaminationReports($startDate, $endDate);
            case 'appointments':
                return $this->getAppointmentReports($startDate, $endDate);
            case 'finance':
                return $this->getFinanceReports($startDate, $endDate, $asOfDate);
            case 'treatments':
                return $this->getTreatmentReports($startDate, $endDate);
            case 'overview':
            default:
                return $this->getOverviewReports($startDate, $endDate);
        }
    }

    private function paginateDetailRows(array $rows, int $page, int $perPage, string $search = '', ?string $drillQuery = null): array
    {
        $search = trim($search);
        $drillQuery = trim((string) ($drillQuery ?? ''));

        if ($search !== '' || $drillQuery !== '') {
            $rows = array_values(array_filter($rows, static function ($row) use ($search, $drillQuery): bool {
                $blob = strtolower(json_encode($row, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: '');
                $searchMatch = ($search === '') || str_contains($blob, strtolower($search));
                $drillMatch = ($drillQuery === '') || str_contains($blob, strtolower($drillQuery));
                return $searchMatch && $drillMatch;
            }));
        }

        $total = count($rows);
        $page = max(1, $page);
        $perPage = max(10, min($perPage, 200));
        $lastPage = max(1, (int) ceil($total / $perPage));
        $page = min($page, $lastPage);
        $offset = ($page - 1) * $perPage;

        return [
            'rows' => array_slice($rows, $offset, $perPage),
            'pagination' => [
                'page' => $page,
                'per_page' => $perPage,
                'total_rows' => $total,
                'last_page' => $lastPage,
                'from' => $total > 0 ? ($offset + 1) : 0,
                'to' => min($offset + $perPage, $total),
            ],
        ];
    }

    private function html(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }

    private function csvCell($value): string
    {
        $text = (string) ($value ?? '');
        // Prevent spreadsheet formula injection when exported CSV is opened in Excel/Sheets.
        if ($text !== '' && preg_match('/^[=\-+@]/', $text) === 1) {
            $text = "'" . $text;
        }
        return $text;
    }

    private function csvLine(array $cells): string
    {
        $fp = fopen('php://temp', 'r+');
        fputcsv($fp, array_map(fn($cell) => $this->csvCell($cell), $cells));
        rewind($fp);
        $line = stream_get_contents($fp) ?: '';
        fclose($fp);
        return $line;
    }

    public function index()
    {
        try {
            // Check if user is logged in
            if (!$this->isLoggedIn()) {
                return redirect()->to('/auth/login');
            }

            $clinicId = $this->requireTenantClinicId();
            if (!$clinicId) {
                return redirect()->to('/clinic/select')->with('error', 'Please select a clinic to view reports.');
            }

            $filters = $this->parseReportFilters('get');
            $dateRange = $filters['date_range'];
            $reportType = $filters['report_type'];
            $data = [
                'title' => 'Reports & Analytics',
                'date_range' => $dateRange,
                'report_type' => $reportType,
                'filters' => $filters,
            ];

            $startDate = $filters['start_date'];
            $endDate = $filters['end_date'];
            $asOfDate = $filters['as_of_date'];

            // Generate reports based on type
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
                    $data['reports'] = $this->getFinanceReports($startDate, $endDate, $asOfDate);
                    break;
                case 'treatments':
                    $data['reports'] = $this->getTreatmentReports($startDate, $endDate);
                    break;
                default:
                    $data['reports'] = $this->getOverviewReports($startDate, $endDate);
            }

            $data['detail_tables'] = $this->buildDetailTables($reportType, $data['reports']);

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
                'reports' => $this->getBasicReports(),
                'filters' => [
                    'report_type' => 'overview',
                    'date_mode' => 'relative',
                    'date_range' => '30',
                    'custom_from' => null,
                    'custom_to' => null,
                    'as_of_date' => date('Y-m-d'),
                    'start_date' => date('Y-m-d', strtotime('-30 days')),
                    'end_date' => date('Y-m-d'),
                    'period_label' => 'Last 30 days',
                ],
                'detail_tables' => [],
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
            $clinicId = $this->requireTenantClinicId();
            if (!$clinicId) {
                return $this->response->setStatusCode(403)->setJSON(['error' => 'TENANT_CONTEXT_REQUIRED']);
            }

            $filters = $this->parseReportFilters('get');
            $reportType = $filters['report_type'];
            $startDate = $filters['start_date'];
            $endDate = $filters['end_date'];
            $asOfDate = $filters['as_of_date'];
            
            $data = $this->buildReportDataByType($reportType, $startDate, $endDate, $asOfDate);
            
            $data['_detail_tables'] = $this->buildDetailTables($reportType, $data);
            $data['_filters'] = $filters;
            return $this->response->setJSON($data);
        } catch (\Exception $e) {
            log_message('error', 'Chart data error: ' . $e->getMessage());
            return $this->response->setJSON(['error' => 'Failed to fetch chart data']);
        }
    }

    public function getDetailData()
    {
        try {
            $clinicId = $this->requireTenantClinicId();
            if (!$clinicId) {
                return $this->response->setStatusCode(403)->setJSON(['error' => 'TENANT_CONTEXT_REQUIRED']);
            }

            $filters = $this->parseReportFilters('get');
            $reportType = $filters['report_type'];
            $startDate = $filters['start_date'];
            $endDate = $filters['end_date'];
            $asOfDate = $filters['as_of_date'];

            $tableId = trim((string) $this->request->getGet('table_id'));
            $page = max(1, (int) ($this->request->getGet('page') ?? 1));
            $perPage = (int) ($this->request->getGet('per_page') ?? 25);
            $search = (string) ($this->request->getGet('q') ?? '');
            $drillTable = trim((string) ($this->request->getGet('drill_table') ?? ''));
            $drillQuery = (string) ($this->request->getGet('drill_query') ?? '');

            $reports = $this->buildReportDataByType($reportType, $startDate, $endDate, $asOfDate);
            $detailTables = $this->buildDetailTables($reportType, $reports);

            $table = null;
            foreach ($detailTables as $candidate) {
                if (($candidate['id'] ?? '') === $tableId) {
                    $table = $candidate;
                    break;
                }
            }

            if ($table === null) {
                return $this->response->setStatusCode(404)->setJSON(['error' => 'DETAIL_TABLE_NOT_FOUND']);
            }

            $appliedDrillQuery = ($drillTable !== '' && $drillTable === $tableId) ? $drillQuery : '';
            $paged = $this->paginateDetailRows($table['rows'] ?? [], $page, $perPage, $search, $appliedDrillQuery);

            return $this->response->setJSON([
                'table' => [
                    'id' => $table['id'],
                    'title' => $table['title'],
                    'columns' => $table['columns'] ?? [],
                ],
                'rows' => $paged['rows'],
                'pagination' => $paged['pagination'],
                'filters' => $filters,
                'search' => $search,
                'drill' => [
                    'table' => $drillTable,
                    'query' => $appliedDrillQuery,
                ],
            ]);
        } catch (\Throwable $e) {
            log_message('error', 'Reports detail-data error: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON(['error' => 'REPORT_DETAIL_FETCH_FAILED']);
        }
    }

    public function export()
    {
        try {
            if (strtoupper($this->request->getMethod()) !== 'POST') {
                return $this->response->setStatusCode(405)->setJSON([
                    'error' => 'EXPORT_REQUIRES_POST',
                ]);
            }

            $clinicId = $this->requireTenantClinicId();
            if (!$clinicId) {
                return $this->response->setStatusCode(403)->setJSON(['error' => 'TENANT_CONTEXT_REQUIRED']);
            }

            $filters = $this->parseReportFilters('post');
            $format = $this->sanitizeExportFormat($this->request->getPost('format'));
            $reportType = $filters['report_type'];
            $dateRange = $filters['date_range'];
            $startDate = $filters['start_date'];
            $endDate = $filters['end_date'];
            $asOfDate = $filters['as_of_date'];

            $data = [
                'title' => 'Export Report',
                'report_type' => $reportType,
                'date_range' => $dateRange,
                'date_mode' => $filters['date_mode'],
                'as_of_date' => $asOfDate,
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
                    $data['reports'] = $this->getFinanceReports($startDate, $endDate, $asOfDate);
                    break;
                case 'treatments':
                    $data['reports'] = $this->getTreatmentReports($startDate, $endDate);
                    break;
                default:
                    $data['reports'] = $this->getOverviewReports($startDate, $endDate);
            }

            $data['detail_tables'] = $this->buildDetailTables($reportType, $data['reports']);

            if ($format === 'pdf') {
                $attachment = $this->generatePDF($data);
            } elseif ($format === 'excel') {
                $attachment = $this->generateExcel($data);
            } else {
                $attachment = $this->generateCSV($data);
            }

            return redirect()->to(base_url('file/download/' . $attachment['id']));
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
        $clinicId = session()->get('active_clinic_id');
        if (!$clinicId) {
            return $this->getBasicReports();
        }

        $monthlyRevenueData = $this->getMonthlyRevenueData($startDate, $endDate);
        $examinationTypesData = $this->getExaminationTypesData($startDate, $endDate);
        $appointmentStatusData = $this->getAppointmentStatusData($startDate, $endDate);
        
        $totalRevenueStats = $this->financeModel->getFinanceStatsByClinic($clinicId);
        $periodRevenueStats = $this->financeModel->getFinanceStatsByClinic($clinicId, $startDate, $endDate);
        $startDateTime = $startDate . ' 00:00:00';
        $endDateTime = $endDate . ' 23:59:59';

        $overviewFinanceTransactions = $this->financeModel
            ->where('clinic_id', $clinicId)
            ->where('created_at >=', $startDateTime)
            ->where('created_at <=', $endDateTime)
            ->orderBy('created_at', 'DESC')
            ->findAll(250);
        foreach ($overviewFinanceTransactions as &$txn) {
            $txn['month_label'] = !empty($txn['created_at']) ? date('M Y', strtotime((string) $txn['created_at'])) : '';
        }
        unset($txn);

        $overviewExaminations = $this->examinationModel
            ->where('clinic_id', $clinicId)
            ->where('examination_date >=', $startDate)
            ->where('examination_date <=', $endDate)
            ->orderBy('examination_date', 'DESC')
            ->findAll(250);

        $overviewAppointments = $this->appointmentModel
            ->where('clinic_id', $clinicId)
            ->where('appointment_date >=', $startDate)
            ->where('appointment_date <=', $endDate)
            ->orderBy('appointment_date', 'DESC')
            ->findAll(250);

        return [
            'summary' => [
                'total_patients' => $this->patientModel->where('clinic_id', $clinicId)->countAllResults(),
                'new_patients' => $this->patientModel->where('clinic_id', $clinicId)->where('created_at >=', $startDate . ' 00:00:00')->where('created_at <=', $endDate . ' 23:59:59')->countAllResults(),
                'total_examinations' => $this->examinationModel->where('clinic_id', $clinicId)->countAllResults(),
                'examinations_this_period' => $this->examinationModel->where('clinic_id', $clinicId)->where('examination_date >=', $startDate)->where('examination_date <=', $endDate)->countAllResults(),
                'total_appointments' => $this->appointmentModel->where('clinic_id', $clinicId)->countAllResults(),
                'appointments_this_period' => $this->appointmentModel->where('clinic_id', $clinicId)->where('appointment_date >=', $startDate)->where('appointment_date <=', $endDate)->countAllResults(),
                'total_revenue' => $totalRevenueStats['total_revenue'] ?? 0,
                'revenue_this_period' => $periodRevenueStats['total_revenue'] ?? 0,
            ],
            'charts' => [
                'monthly_patients' => $this->getMonthlyPatientsData($startDate, $endDate),
                'monthly_revenue' => $monthlyRevenueData,
                'examination_types' => $examinationTypesData,
                'appointment_status' => $appointmentStatusData,
            ],
            'detail_rows' => [
                'finance_transactions' => $overviewFinanceTransactions,
                'examinations' => $overviewExaminations,
                'appointments' => $overviewAppointments,
            ],
        ];
    }

    private function getPatientReports($startDate, $endDate)
    {
        $clinicId = session()->get('active_clinic_id');
        if (!$clinicId) {
            return [
                'patients' => [],
                'age_groups' => [],
                'gender_distribution' => [],
                'new_vs_returning' => ['new' => 0, 'returning' => 0],
            ];
        }

        return [
            'patients' => $this->patientModel->where('clinic_id', $clinicId)->where('created_at >=', $startDate . ' 00:00:00')->where('created_at <=', $endDate . ' 23:59:59')->findAll(),
            'age_groups' => $this->getAgeGroupData(),
            'gender_distribution' => $this->getGenderDistributionData($startDate, $endDate),
            'new_vs_returning' => $this->getNewVsReturningData($startDate, $endDate),
        ];
    }

    private function getExaminationReports($startDate, $endDate)
    {
        $clinicId = session()->get('active_clinic_id');
        if (!$clinicId) {
            return [
                'examinations' => [],
                'examination_types' => [],
                'monthly_examinations' => [],
                'tooth_conditions' => [],
            ];
        }

        return [
            'examinations' => $this->examinationModel->where('clinic_id', $clinicId)->where('examination_date >=', $startDate)->where('examination_date <=', $endDate)->findAll(),
            'examination_types' => $this->getExaminationTypesData($startDate, $endDate),
            'monthly_examinations' => $this->getMonthlyExaminationsData($startDate, $endDate),
            'tooth_conditions' => $this->getToothConditionsData(),
        ];
    }

    private function getAppointmentReports($startDate, $endDate)
    {
        $clinicId = session()->get('active_clinic_id');
        if (!$clinicId) {
            return [
                'appointments' => [],
                'appointment_types' => [],
                'monthly_appointments' => [],
                'status_distribution' => [],
            ];
        }

        return [
            'appointments' => $this->appointmentModel->where('clinic_id', $clinicId)->where('appointment_date >=', $startDate)->where('appointment_date <=', $endDate)->findAll(),
            'appointment_types' => $this->getAppointmentTypesData($startDate, $endDate),
            'monthly_appointments' => $this->getMonthlyAppointmentsData($startDate, $endDate),
            'status_distribution' => $this->getAppointmentStatusData($startDate, $endDate),
        ];
    }

    private function getFinanceReports($startDate, $endDate, ?string $asOfDate = null)
    {
        $clinicId = session()->get('active_clinic_id');
        if (!$clinicId) {
            return [
                'transactions' => [],
                'monthly_revenue' => [],
                'payment_methods' => [],
                'outstanding_payments' => [],
                'summary' => [],
            ];
        }

        $startDateTime = $startDate . ' 00:00:00';
        $endDateTime = $endDate . ' 23:59:59';
        $financeStats = $this->financeModel->getFinanceStatsByClinic($clinicId, $startDate, $endDate);
        $providerStats = $this->providerPayoutModel->getStatsByClinic((int) $clinicId, $startDate, $endDate);

        $grossCollections = floatval($financeStats['total_revenue'] ?? 0);
        $providerShareAccrual = floatval($providerStats['total_provider_share'] ?? 0);
        $providerPaidCash = floatval($providerStats['total_provider_paid_cash'] ?? 0);
        $providerPayableOutstanding = floatval($providerStats['total_provider_balance'] ?? 0);
        $pendingInvoiceAmount = floatval($financeStats['pending_payments'] ?? 0);

        return [
            'transactions' => $this->financeModel
                ->where('clinic_id', $clinicId)
                ->where('created_at >=', $startDateTime)
                ->where('created_at <=', $endDateTime)
                ->findAll(),
            'monthly_revenue' => $this->getMonthlyRevenueData($startDate, $endDate),
            'payment_methods' => $this->getPaymentMethodsData($startDate, $endDate),
            'outstanding_payments' => $this->getOutstandingPaymentsData($startDate, $endDate),
            'ar_aging_summary' => $this->financeModel->getAccountsReceivableAgingSummaryByClinic($clinicId, $asOfDate ?: $endDate),
            'ar_aging_detail' => $this->financeModel->getAccountsReceivableAgingDetailByClinic($clinicId, $asOfDate ?: $endDate, 100),
            'provider_payout_status' => $this->providerPayoutModel->getStatusSummaryByClinic((int) $clinicId, $startDate, $endDate),
            'provider_payout_breakdown' => $this->providerPayoutModel->getProviderBreakdownByClinic((int) $clinicId, $startDate, $endDate, 10),
            'provider_payout_rows' => $this->providerPayoutModel->getReportRowsByClinic((int) $clinicId, $startDate, $endDate, 250),
            'summary' => [
                'gross_collections' => $grossCollections,
                'provider_share_accrual' => $providerShareAccrual,
                'provider_paid_cash' => $providerPaidCash,
                'provider_payable_outstanding' => $providerPayableOutstanding,
                'net_clinic_revenue_cash' => $grossCollections - $providerPaidCash,
                'net_clinic_revenue_accrual' => $grossCollections - $providerShareAccrual,
                'pending_invoice_amount' => $pendingInvoiceAmount,
                'outstanding_invoices_count' => intval($financeStats['outstanding_invoices'] ?? 0),
                'total_transactions' => intval($financeStats['total_transactions'] ?? 0),
            ],
        ];
    }

    private function getTreatmentReports($startDate, $endDate)
    {
        $clinicId = session()->get('active_clinic_id');
        if (!$clinicId) {
            return [
                'treatments' => [],
                'treatment_types' => [],
                'monthly_treatments' => [],
                'status_distribution' => [],
            ];
        }

        return [
            'treatments' => $this->treatmentModel->where('clinic_id', $clinicId)->where('start_date >=', $startDate)->where('start_date <=', $endDate)->findAll(),
            'treatment_types' => $this->getTreatmentTypesData($startDate, $endDate),
            'monthly_treatments' => $this->getMonthlyTreatmentsData($startDate, $endDate),
            'status_distribution' => $this->getTreatmentStatusData($startDate, $endDate),
        ];
    }

    // Helper methods for data generation
    private function getMonthlyBuckets(?string $startDate = null, ?string $endDate = null): array
    {
        if (!$startDate || !$endDate) {
            $startDate = date('Y-m-01', strtotime('-11 months'));
            $endDate = date('Y-m-d');
        }

        $cursor = date('Y-m-01', strtotime($startDate));
        $endCursor = date('Y-m-01', strtotime($endDate));
        $months = [];

        while (strtotime($cursor) <= strtotime($endCursor)) {
            $months[] = $cursor;
            $cursor = date('Y-m-01', strtotime($cursor . ' +1 month'));
        }

        return $months;
    }

    private function getMonthlyPatientsData(?string $startDate = null, ?string $endDate = null)
    {
        $clinicId = session()->get('active_clinic_id');
        if (!$clinicId) {
            return [];
        }

        $data = [];
        foreach ($this->getMonthlyBuckets($startDate, $endDate) as $date) {
            $monthStart = date('Y-m-01 00:00:00', strtotime($date));
            $monthEnd = date('Y-m-t 23:59:59', strtotime($date));
            $count = $this->patientModel->where('clinic_id', $clinicId)
                                      ->where('created_at >=', $monthStart)
                                      ->where('created_at <=', $monthEnd)
                                      ->countAllResults();
            $data[] = [
                'month' => date('M Y', strtotime($date)),
                'count' => $count
            ];
        }
        return $data;
    }

    private function getMonthlyRevenueData(?string $startDate = null, ?string $endDate = null)
    {
        $clinicId = session()->get('active_clinic_id');
        if (!$clinicId) {
            return [];
        }

        if ($startDate && $endDate) {
            return $this->financeModel->getMonthlyRevenueByClinicRange((int) $clinicId, $startDate, $endDate);
        }

        $year = date('Y');
        $monthlyRevenue = $this->financeModel->getMonthlyRevenueByClinic($clinicId, $year);
        $existingData = [];

        foreach ($monthlyRevenue as $row) {
            $existingData[(int) $row['month']] = floatval($row['total_amount'] ?? 0);
        }

        $data = [];
        for ($i = 11; $i >= 0; $i--) {
            $date = date('Y-m-01', strtotime("-{$i} months"));
            $monthIndex = (int) date('n', strtotime($date));
            $data[] = [
                'month' => date('M Y', strtotime($date)),
                'amount' => $existingData[$monthIndex] ?? 0
            ];
        }

        return $data;
    }

    private function getExaminationTypesData(?string $startDate = null, ?string $endDate = null)
    {
        $query = $this->examinationModel->select('examination_type, COUNT(*) as count')
                                       ->where('clinic_id', session()->get('active_clinic_id'));
        if ($startDate) {
            $query->where('examination_date >=', $startDate);
        }
        if ($endDate) {
            $query->where('examination_date <=', $endDate);
        }
        $types = $query->groupBy('examination_type')->findAll();
        return $types;
    }

    private function getAppointmentStatusData(?string $startDate = null, ?string $endDate = null)
    {
        $query = $this->appointmentModel->select('status, COUNT(*) as count')
                                          ->where('clinic_id', session()->get('active_clinic_id'));
        if ($startDate) {
            $query->where('appointment_date >=', $startDate);
        }
        if ($endDate) {
            $query->where('appointment_date <=', $endDate);
        }
        $statuses = $query->groupBy('status')->findAll();
        return $statuses;
    }

    private function getAgeGroupData()
    {
        try {
            $clinicId = session()->get('active_clinic_id');
            if (!$clinicId) {
                return [];
            }

            $patients = $this->patientModel->select('date_of_birth')->where('clinic_id', $clinicId)->findAll();
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

    private function getGenderDistributionData(?string $startDate = null, ?string $endDate = null)
    {
        $query = $this->patientModel->select('gender, COUNT(*) as count')
                                 ->where('clinic_id', session()->get('active_clinic_id'));
        if ($startDate) {
            $query->where('created_at >=', $startDate . ' 00:00:00');
        }
        if ($endDate) {
            $query->where('created_at <=', $endDate . ' 23:59:59');
        }
        return $query->groupBy('gender')->findAll();
    }

    private function getNewVsReturningData($startDate, $endDate)
    {
        $clinicId = session()->get('active_clinic_id');
        if (!$clinicId) {
            return ['new' => 0, 'returning' => 0];
        }

        $new = $this->patientModel->where('clinic_id', $clinicId)
            ->where('created_at >=', $startDate . ' 00:00:00')
            ->where('created_at <=', $endDate . ' 23:59:59')
            ->countAllResults();
        $returning = $this->patientModel->where('clinic_id', $clinicId)->where('created_at <', $startDate)->countAllResults();
        
        return [
            'new' => $new,
            'returning' => $returning,
        ];
    }

    private function getMonthlyExaminationsData(?string $startDate = null, ?string $endDate = null)
    {
        $clinicId = session()->get('active_clinic_id');
        if (!$clinicId) {
            return [];
        }

        $data = [];
        foreach ($this->getMonthlyBuckets($startDate, $endDate) as $date) {
            $monthStart = date('Y-m-01', strtotime($date));
            $monthEnd = date('Y-m-t', strtotime($date));
            $count = $this->examinationModel->where('clinic_id', $clinicId)
                                           ->where('examination_date >=', $monthStart)
                                           ->where('examination_date <=', $monthEnd)
                                           ->countAllResults();
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

    private function getAppointmentTypesData(?string $startDate = null, ?string $endDate = null)
    {
        $query = $this->appointmentModel->select('appointment_type, COUNT(*) as count')
                                     ->where('clinic_id', session()->get('active_clinic_id'));
        if ($startDate) {
            $query->where('appointment_date >=', $startDate);
        }
        if ($endDate) {
            $query->where('appointment_date <=', $endDate);
        }
        return $query->groupBy('appointment_type')->findAll();
    }

    private function getMonthlyAppointmentsData(?string $startDate = null, ?string $endDate = null)
    {
        $clinicId = session()->get('active_clinic_id');
        if (!$clinicId) {
            return [];
        }

        $data = [];
        foreach ($this->getMonthlyBuckets($startDate, $endDate) as $date) {
            $monthStart = date('Y-m-01', strtotime($date));
            $monthEnd = date('Y-m-t', strtotime($date));
            $count = $this->appointmentModel->where('clinic_id', $clinicId)
                                           ->where('appointment_date >=', $monthStart)
                                           ->where('appointment_date <=', $monthEnd)
                                           ->countAllResults();
            $data[] = [
                'month' => date('M Y', strtotime($date)),
                'count' => $count
            ];
        }
        return $data;
    }

    private function getPaymentMethodsData(?string $startDate = null, ?string $endDate = null)
    {
        $clinicId = session()->get('active_clinic_id');
        if (!$clinicId) {
            return [];
        }

        $rows = $this->financeModel->getPaymentMethodsStats($startDate, $endDate, $clinicId);
        return array_map(function ($row) {
            return [
                'payment_method' => $row['payment_method'] ?? 'unknown',
                'count' => $row['transaction_count'] ?? 0,
                'total_amount' => $row['total_amount'] ?? 0,
            ];
        }, $rows);
    }

    private function getOutstandingPaymentsData(?string $startDate = null, ?string $endDate = null)
    {
        $clinicId = session()->get('active_clinic_id');
        if (!$clinicId) {
            return [];
        }

        $query = $this->financeModel->where('clinic_id', $clinicId)
            ->where('transaction_type', 'invoice')
            ->where('balance_due >', 0);

        if ($startDate) {
            $query->where('created_at >=', $startDate . ' 00:00:00');
        }
        if ($endDate) {
            $query->where('created_at <=', $endDate . ' 23:59:59');
        }

        return $query->findAll();
    }

    private function getTreatmentTypesData(?string $startDate = null, ?string $endDate = null)
    {
        $query = $this->treatmentModel->select('treatment_type, COUNT(*) as count')
                                   ->where('clinic_id', session()->get('active_clinic_id'));
        if ($startDate) {
            $query->where('start_date >=', $startDate);
        }
        if ($endDate) {
            $query->where('start_date <=', $endDate);
        }
        return $query->groupBy('treatment_type')->findAll();
    }

    private function getMonthlyTreatmentsData(?string $startDate = null, ?string $endDate = null)
    {
        $clinicId = session()->get('active_clinic_id');
        if (!$clinicId) {
            return [];
        }

        $data = [];
        foreach ($this->getMonthlyBuckets($startDate, $endDate) as $date) {
            $monthStart = date('Y-m-01', strtotime($date));
            $monthEnd = date('Y-m-t', strtotime($date));
            $count = $this->treatmentModel->where('clinic_id', $clinicId)
                                         ->where('start_date >=', $monthStart)
                                         ->where('start_date <=', $monthEnd)
                                         ->countAllResults();
            $data[] = [
                'month' => date('M Y', strtotime($date)),
                'count' => $count
            ];
        }
        return $data;
    }

    private function getTreatmentStatusData(?string $startDate = null, ?string $endDate = null)
    {
        $query = $this->treatmentModel->select('status, COUNT(*) as count')
                                   ->where('clinic_id', session()->get('active_clinic_id'));
        if ($startDate) {
            $query->where('start_date >=', $startDate);
        }
        if ($endDate) {
            $query->where('start_date <=', $endDate);
        }
        return $query->groupBy('status')->findAll();
    }

    private function generatePDF($data)
    {
        $clinicId = session()->get('active_clinic_id');
        // Placeholder: stores HTML until a real PDF renderer is integrated.
        $html = $this->generateReportHTML($data);
        
        // Persist artifact
        $fileName = 'report_' . $data['report_type'] . '_' . date('Y-m-d') . '.html';
        $attachment = $this->storageService->storeExport(
            $html, 
            $fileName, 
            'text/html', 
            $clinicId, 
            'report', 
            0, 
            'report_html'
        );
        
        return $attachment;
    }

    private function generateExcel($data)
    {
        $clinicId = session()->get('active_clinic_id');
        // CSV-compatible export (Excel-readable) until XLSX support is added.
        $csv = $this->generateReportCSV($data);
        
        // Persist artifact
        $fileName = 'report_' . $data['report_type'] . '_' . date('Y-m-d') . '.csv';
        $attachment = $this->storageService->storeExport(
            $csv, 
            $fileName, 
            'text/csv', 
            $clinicId, 
            'report', 
            0, 
            'report_excel'
        );
        
        return $attachment;
    }

    private function generateCSV($data)
    {
        $clinicId = session()->get('active_clinic_id');
        $csv = $this->generateReportCSV($data);
        
        // Persist artifact
        $fileName = 'report_' . $data['report_type'] . '_' . date('Y-m-d') . '.csv';
        $attachment = $this->storageService->storeExport(
            $csv, 
            $fileName, 
            'text/csv', 
            $clinicId, 
            'report', 
            0, 
            'report_csv'
        );
        
        return $attachment;
    }

    private function generateReportHTML($data)
    {
        $html = '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Dental Practice Report - ' . $this->html(date('Y-m-d')) . '</title>
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
        <p>Generated on: ' . $this->html(date('Y-m-d H:i:s')) . '</p>
        <p>Report Type: ' . $this->html(ucfirst((string) ($data['report_type'] ?? 'overview'))) . '</p>
        <p>Date Range: ' . $this->html((string) ($data['start_date'] ?? '')) . ' to ' . $this->html((string) ($data['end_date'] ?? '')) . '</p>
    </div>';

        if (isset($data['reports']['summary'])) {
            $html .= '<div class="section">
                <h2>Summary</h2>
                <div class="summary">';
            
            foreach ($data['reports']['summary'] as $key => $value) {
                $label = ucwords(str_replace('_', ' ', (string) $key));
                $displayValue = is_numeric($value) ? number_format((float) $value, 2) : (string) $value;
                $html .= '<div class="summary-item">
                    <h3>' . $this->html($label) . '</h3>
                    <p>' . $this->html($displayValue) . '</p>
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
                    <td>' . $this->html((string) ($patient['id'] ?? '')) . '</td>
                    <td>' . $this->html(trim((string) (($patient['first_name'] ?? '') . ' ' . ($patient['last_name'] ?? '')))) . '</td>
                    <td>' . $this->html((string) ($patient['phone'] ?? '')) . '</td>
                    <td>' . $this->html((string) ($patient['email'] ?? '')) . '</td>
                    <td>' . $this->html((string) ($patient['created_at'] ?? '')) . '</td>
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
                    <td>' . $this->html((string) ($exam['id'] ?? '')) . '</td>
                    <td>' . $this->html((string) ($exam['patient_id'] ?? '')) . '</td>
                    <td>' . $this->html((string) ($exam['examination_type'] ?? '')) . '</td>
                    <td>' . $this->html((string) ($exam['examination_date'] ?? '')) . '</td>
                    <td>' . $this->html((string) ($exam['notes'] ?? '')) . '</td>
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
                    <td>' . $this->html((string) ($appointment['id'] ?? '')) . '</td>
                    <td>' . $this->html((string) ($appointment['patient_id'] ?? '')) . '</td>
                    <td>' . $this->html((string) ($appointment['appointment_date'] ?? '')) . '</td>
                    <td>' . $this->html((string) ($appointment['appointment_type'] ?? '')) . '</td>
                    <td>' . $this->html((string) ($appointment['status'] ?? '')) . '</td>
                    <td>' . $this->html((string) ($appointment['notes'] ?? '')) . '</td>
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
                    <td>' . $this->html((string) ($transaction['id'] ?? '')) . '</td>
                    <td>' . $this->html((string) ($transaction['patient_id'] ?? '')) . '</td>
                    <td>' . $this->html((string) ($transaction['transaction_type'] ?? '')) . '</td>
                    <td>' . $this->html(number_format((float) ($transaction['total_amount'] ?? 0), 2)) . '</td>
                    <td>' . $this->html((string) ($transaction['payment_method'] ?? '')) . '</td>
                    <td>' . $this->html((string) ($transaction['payment_status'] ?? '')) . '</td>
                    <td>' . $this->html((string) ($transaction['created_at'] ?? '')) . '</td>
                </tr>';
            }
            
            $html .= '</tbody></table></div>';
        }

        $html .= '</body></html>';
        
        return $html;
    }

    private function generateReportCSV($data)
    {
        $csv = '';
        $csv .= $this->csvLine(['Dental Practice Report']);
        $csv .= $this->csvLine(['Generated on', date('Y-m-d H:i:s')]);
        $csv .= $this->csvLine(['Report Type', ucfirst((string) ($data['report_type'] ?? 'overview'))]);
        $csv .= $this->csvLine(['Date Range', (string) ($data['start_date'] ?? ''), (string) ($data['end_date'] ?? '')]);
        $csv .= "\n";

        if (isset($data['reports']['summary'])) {
            $csv .= $this->csvLine(['SUMMARY']);
            $csv .= $this->csvLine(['Metric', 'Value']);
            
            foreach ($data['reports']['summary'] as $key => $value) {
                $label = ucwords(str_replace('_', ' ', $key));
                $csv .= $this->csvLine([$label, is_numeric($value) ? number_format((float) $value, 2, '.', '') : (string) $value]);
            }
            $csv .= "\n";
        }

        // Add detailed data based on report type
        if (isset($data['reports']['patients']) && is_array($data['reports']['patients'])) {
            $csv .= $this->csvLine(['PATIENTS']);
            $csv .= $this->csvLine(['ID', 'First Name', 'Last Name', 'Phone', 'Email', 'Created Date']);
            
            foreach ($data['reports']['patients'] as $patient) {
                $csv .= $this->csvLine([
                    $patient['id'] ?? '',
                    $patient['first_name'] ?? '',
                    $patient['last_name'] ?? '',
                    $patient['phone'] ?? '',
                    $patient['email'] ?? '',
                    $patient['created_at'] ?? '',
                ]);
            }
        }

        if (isset($data['reports']['examinations']) && is_array($data['reports']['examinations'])) {
            $csv .= "\n" . $this->csvLine(['EXAMINATIONS']);
            $csv .= $this->csvLine(['ID', 'Patient ID', 'Examination Type', 'Examination Date', 'Notes']);
            
            foreach ($data['reports']['examinations'] as $exam) {
                $csv .= $this->csvLine([
                    $exam['id'] ?? '',
                    $exam['patient_id'] ?? '',
                    $exam['examination_type'] ?? '',
                    $exam['examination_date'] ?? '',
                    $exam['notes'] ?? '',
                ]);
            }
        }

        if (isset($data['reports']['appointments']) && is_array($data['reports']['appointments'])) {
            $csv .= "\n" . $this->csvLine(['APPOINTMENTS']);
            $csv .= $this->csvLine(['ID', 'Patient ID', 'Appointment Date', 'Appointment Type', 'Status', 'Notes']);
            
            foreach ($data['reports']['appointments'] as $appointment) {
                $csv .= $this->csvLine([
                    $appointment['id'] ?? '',
                    $appointment['patient_id'] ?? '',
                    $appointment['appointment_date'] ?? '',
                    $appointment['appointment_type'] ?? '',
                    $appointment['status'] ?? '',
                    $appointment['notes'] ?? '',
                ]);
            }
        }

        if (isset($data['reports']['transactions']) && is_array($data['reports']['transactions'])) {
            $csv .= "\n" . $this->csvLine(['FINANCIAL TRANSACTIONS']);
            $csv .= $this->csvLine(['ID', 'Patient ID', 'Transaction Type', 'Amount', 'Payment Method', 'Payment Status', 'Date']);
            
            foreach ($data['reports']['transactions'] as $transaction) {
                $csv .= $this->csvLine([
                    $transaction['id'] ?? '',
                    $transaction['patient_id'] ?? '',
                    $transaction['transaction_type'] ?? '',
                    $transaction['total_amount'] ?? '',
                    $transaction['payment_method'] ?? '',
                    $transaction['payment_status'] ?? '',
                    $transaction['created_at'] ?? '',
                ]);
            }
        }

        if (isset($data['reports']['ar_aging_summary']) && is_array($data['reports']['ar_aging_summary'])) {
            $s = $data['reports']['ar_aging_summary'];
            $csv .= "\n" . $this->csvLine(['AR AGING SUMMARY']);
            $csv .= $this->csvLine(['Bucket', 'Amount']);
            foreach ([
                'current' => 'Current',
                'days_1_30' => '1-30 Days',
                'days_31_60' => '31-60 Days',
                'days_61_90' => '61-90 Days',
                'days_91_plus' => '91+ Days',
                'total_balance_due' => 'Total',
            ] as $key => $label) {
                $csv .= $this->csvLine([$label, (float) ($s[$key] ?? 0)]);
            }
        }

        if (isset($data['reports']['ar_aging_detail']) && is_array($data['reports']['ar_aging_detail'])) {
            $csv .= "\n" . $this->csvLine(['AR AGING DETAIL']);
            $csv .= $this->csvLine(['Invoice', 'Patient', 'Due Date', 'Days Past Due', 'Bucket', 'Balance Due']);
            foreach ($data['reports']['ar_aging_detail'] as $row) {
                $csv .= $this->csvLine([
                    $row['transaction_id'] ?? ('#' . ($row['id'] ?? '')),
                    trim((string) (($row['first_name'] ?? '') . ' ' . ($row['last_name'] ?? ''))),
                    $row['due_date'] ?? '',
                    $row['days_past_due'] ?? 0,
                    $row['aging_bucket'] ?? 'current',
                    $row['balance_due'] ?? 0,
                ]);
            }
        }

        if (isset($data['reports']['provider_payout_breakdown']) && is_array($data['reports']['provider_payout_breakdown'])) {
            $csv .= "\n" . $this->csvLine(['PROVIDER PAYOUT BREAKDOWN']);
            $csv .= $this->csvLine(['Provider', 'Payout Count', 'Gross', 'Provider Share', 'Provider Paid', 'Provider Balance']);
            foreach ($data['reports']['provider_payout_breakdown'] as $row) {
                $csv .= $this->csvLine([
                    $row['provider_label'] ?? 'Unknown',
                    $row['payout_count'] ?? 0,
                    $row['gross_amount_total'] ?? 0,
                    $row['provider_share_total'] ?? 0,
                    $row['provider_paid_total'] ?? 0,
                    $row['provider_balance_total'] ?? 0,
                ]);
            }
        }

        if (isset($data['reports']['provider_payout_rows']) && is_array($data['reports']['provider_payout_rows'])) {
            $csv .= "\n" . $this->csvLine(['PROVIDER PAYOUT ROWS']);
            $csv .= $this->csvLine(['Transaction', 'Patient #', 'Patient', 'Provider', 'Status', 'Gross', 'Provider Share', 'Paid', 'Balance']);
            foreach ($data['reports']['provider_payout_rows'] as $row) {
                $csv .= $this->csvLine([
                    $row['transaction_id'] ?? '',
                    $row['patient_number'] ?? '',
                    $row['patient_name'] ?? '',
                    $row['provider_label'] ?? '',
                    $row['status'] ?? '',
                    $row['gross_amount'] ?? 0,
                    $row['provider_share_amount'] ?? 0,
                    $row['provider_paid_amount'] ?? 0,
                    $row['provider_balance_due'] ?? 0,
                ]);
            }
        }

        return $csv;
    }

    private function getBasicReports()
    {
        try {
            $clinicId = session()->get('active_clinic_id');
            if (!$clinicId) {
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

            return [
                'summary' => [
                    'total_patients' => $this->patientModel->where('clinic_id', $clinicId)->countAllResults(),
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
