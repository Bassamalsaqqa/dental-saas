<?php

namespace App\Controllers;

use App\Models\PatientModel;
use App\Models\ExaminationModel;
use App\Models\AppointmentModel;
use App\Models\FinanceModel;
use App\Models\ProviderPayoutModel;
use App\Models\TreatmentModel;
use App\Services\PlanGuard;

class Reports extends BaseController
{
    private const ALLOWED_REPORT_TYPES = ['overview', 'patients', 'examinations', 'appointments', 'treatments'];
    private const ALLOWED_DATE_RANGES = ['7', '30', '90', '365'];
    private const ALLOWED_EXPORT_FORMATS = ['pdf', 'excel', 'csv'];
    private const ALLOWED_DATE_MODES = ['relative', 'custom'];
    private const ALLOWED_COMPARE_MODES = ['none', 'previous_period'];
    private const ALLOWED_FINANCE_TRANSACTION_TYPES = ['payment', 'refund', 'expense', 'void', 'provider_payout'];
    private const ALLOWED_FINANCE_PAYMENT_METHODS = ['cash', 'card', 'bank_transfer', 'check', 'insurance', 'other'];
    private const ALLOWED_FINANCE_PAYMENT_STATUSES = ['paid', 'pending', 'partial', 'overdue', 'cancelled'];
    private const ALLOWED_APPOINTMENT_STATUSES = ['scheduled', 'confirmed', 'completed', 'cancelled', 'no_show', 'pending'];
    private const ALLOWED_TREATMENT_STATUSES = ['planned', 'in_progress', 'completed', 'cancelled', 'on_hold'];

    protected $patientModel;
    protected $examinationModel;
    protected $appointmentModel;
    protected $financeModel;
    protected $providerPayoutModel;
    protected $treatmentModel;
    protected $storageService;
    protected $db;
    protected PlanGuard $planGuard;

    public function __construct()
    {
        $this->db = db_connect();
        $this->patientModel = new PatientModel();
        $this->examinationModel = new ExaminationModel();
        $this->appointmentModel = new AppointmentModel();
        $this->financeModel = new FinanceModel();
        $this->providerPayoutModel = new ProviderPayoutModel();
        $this->treatmentModel = new TreatmentModel();
        $this->storageService = new \App\Services\StorageService();
        $this->planGuard = new PlanGuard();
    }

    private function canAccessFinanceReports(int $clinicId): bool
    {
        return $this->planGuard->isFeatureEnabled($clinicId, 'finance_reports.enabled', false)
            || $this->planGuard->isFeatureEnabled($clinicId, 'reports', false);
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

    private function redirectLegacyFinanceReport()
    {
        $clinicId = $this->requireTenantClinicId();
        if ($clinicId !== null && !$this->canAccessFinanceReports($clinicId)) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Access Denied: Finance reports are not available in the current plan.');
        }

        $customFrom = $this->sanitizeDateInput($this->request->getGet('custom_from'));
        $customTo = $this->sanitizeDateInput($this->request->getGet('custom_to'));
        $startDate = $this->sanitizeDateInput($this->request->getGet('start_date'));
        $endDate = $this->sanitizeDateInput($this->request->getGet('end_date'));
        $dateMode = $this->sanitizeDateMode($this->request->getGet('date_mode'));
        $dateRange = $this->sanitizeDateRange($this->request->getGet('date_range'));

        if ($dateMode === 'custom' && $customFrom && $customTo) {
            if ($customFrom > $customTo) {
                [$customFrom, $customTo] = [$customTo, $customFrom];
            }
            $startDate = $customFrom;
            $endDate = $customTo;
        }

        if (!$startDate || !$endDate) {
            [$startDate, $endDate] = $this->getReportWindow($dateRange);
        }

        return redirect()->to(base_url('finance/reports?' . http_build_query([
            'start_date' => $startDate,
            'end_date' => $endDate,
        ])));
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
        return $this->normalizeDateOnly($value);
    }

    private function sanitizeCompareMode(?string $value): string
    {
        $value = strtolower((string) ($value ?? 'none'));
        return in_array($value, self::ALLOWED_COMPARE_MODES, true) ? $value : 'none';
    }

    private function sanitizeTokenFilter(?string $value, array $allowed = []): string
    {
        $value = strtolower(trim((string) ($value ?? '')));
        if ($value === '') {
            return '';
        }
        if (preg_match('/^[a-z0-9_\- ]+$/', $value) !== 1) {
            return '';
        }
        if (!empty($allowed) && !in_array($value, $allowed, true)) {
            return '';
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

    private function getPreviousPeriodWindow(string $startDate, string $endDate): array
    {
        $startTs = strtotime($startDate . ' 00:00:00');
        $endTs = strtotime($endDate . ' 00:00:00');
        $days = (int) floor(($endTs - $startTs) / 86400) + 1;
        $prevEnd = date('Y-m-d', strtotime($startDate . ' -1 day'));
        $prevStart = date('Y-m-d', strtotime($prevEnd . ' -' . max(0, $days - 1) . ' days'));
        return [$prevStart, $prevEnd];
    }

    private function parseReportFilters(string $source = 'get'): array
    {
        $input = strtolower($source) === 'post' ? 'getPost' : 'getGet';
        $clinicId = $this->requireTenantClinicId();
        $reportType = $this->sanitizeReportType($this->request->{$input}('report_type') ?? $this->request->{$input}('type'));
        $dateRange = $this->sanitizeDateRange($this->request->{$input}('date_range'));
        $dateMode = $this->sanitizeDateMode($this->request->{$input}('date_mode'));
        $compareMode = $this->sanitizeCompareMode($this->request->{$input}('compare_mode'));
        $customFrom = $this->sanitizeDateInput($this->request->{$input}('custom_from'));
        $customTo = $this->sanitizeDateInput($this->request->{$input}('custom_to'));
        $patientIdRaw = (int) ($this->request->{$input}('patient_id') ?? 0);
        $transactionType = $this->sanitizeTokenFilter($this->request->{$input}('transaction_type'), self::ALLOWED_FINANCE_TRANSACTION_TYPES);
        $paymentMethod = $this->sanitizeTokenFilter($this->request->{$input}('payment_method'), self::ALLOWED_FINANCE_PAYMENT_METHODS);
        $paymentStatus = $this->sanitizeTokenFilter($this->request->{$input}('payment_status'), self::ALLOWED_FINANCE_PAYMENT_STATUSES);
        $examinationType = $this->sanitizeTokenFilter($this->request->{$input}('examination_type'));
        $appointmentStatus = $this->sanitizeTokenFilter($this->request->{$input}('appointment_status'), self::ALLOWED_APPOINTMENT_STATUSES);
        $appointmentType = $this->sanitizeTokenFilter($this->request->{$input}('appointment_type'));
        $treatmentStatus = $this->sanitizeTokenFilter($this->request->{$input}('treatment_status'), self::ALLOWED_TREATMENT_STATUSES);
        $treatmentType = $this->sanitizeTokenFilter($this->request->{$input}('treatment_type'));
        $patientId = null;

        if ($clinicId && $patientIdRaw > 0) {
            $patientExists = $this->patientModel
                ->where('clinic_id', (int) $clinicId)
                ->where('id', $patientIdRaw)
                ->first();
            if (!empty($patientExists)) {
                $patientId = $patientIdRaw;
            }
        }

        if ($dateMode === 'custom') {
            if (!$customFrom || !$customTo) {
                $dateMode = 'relative';
            } elseif ($customFrom > $customTo) {
                [$customFrom, $customTo] = [$customTo, $customFrom];
            }
        }

        if ($dateMode === 'custom' && $customFrom && $customTo) {
            [$startDate, $endDate] = [$customFrom, $customTo];
        } else {
            [$startDate, $endDate] = $this->getReportWindow($dateRange);
            $dateMode = 'relative';
        }

        [$compareStartDate, $compareEndDate] = [null, null];
        if ($compareMode === 'previous_period') {
            [$compareStartDate, $compareEndDate] = $this->getPreviousPeriodWindow($startDate, $endDate);
        }

        return [
            'report_type' => $reportType,
            'date_mode' => $dateMode,
            'compare_mode' => $compareMode,
            'date_range' => $dateRange,
            'custom_from' => $customFrom,
            'custom_to' => $customTo,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'compare_start_date' => $compareStartDate,
            'compare_end_date' => $compareEndDate,
            'patient_id' => $patientId,
            'transaction_type' => $transactionType,
            'payment_method' => $paymentMethod,
            'payment_status' => $paymentStatus,
            'examination_type' => $examinationType,
            'appointment_status' => $appointmentStatus,
            'appointment_type' => $appointmentType,
            'treatment_status' => $treatmentStatus,
            'treatment_type' => $treatmentType,
            'period_label' => $dateMode === 'custom'
                ? lang('TenantMisc.customPeriodLabel', ['from' => $startDate, 'to' => $endDate])
                : lang('TenantMisc.relativePeriodLabel', ['days' => $dateRange, 'from' => $startDate, 'to' => $endDate]),
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

    private function localizeFinanceTransactionType(string $type): string
    {
        return match ($type) {
            'payment' => lang('TenantMisc.payment'),
            'expense' => lang('TenantMisc.expense'),
            'refund' => lang('TenantMisc.refund'),
            'void' => lang('TenantMisc.void'),
            'provider_payout' => lang('TenantMisc.providerPayoutCash'),
            default => $type !== '' ? ucfirst(str_replace('_', ' ', $type)) : lang('TenantMisc.unknown'),
        };
    }

    private function localizeFinancePaymentMethod(string $method): string
    {
        return match ($method) {
            'cash' => lang('TenantMisc.cash'),
            'card' => lang('TenantMisc.card'),
            'bank_transfer' => lang('TenantMisc.bankTransfer'),
            'check' => lang('TenantMisc.check'),
            'insurance' => lang('TenantMisc.insurance'),
            'other' => lang('TenantMisc.other'),
            '', null => '',
            default => ucfirst(str_replace('_', ' ', $method)),
        };
    }

    private function localizeFinanceSourceLabel(string $sourceLabel, string $eventSource): string
    {
        return match ($eventSource) {
            'direct_transaction' => lang('TenantMisc.directTransaction'),
            'invoice_settlement' => lang('TenantMisc.invoiceSettlement'),
            'provider_payout' => lang('TenantMisc.providerPayoutCash'),
            default => $sourceLabel !== '' ? $sourceLabel : ucfirst(str_replace('_', ' ', $eventSource)),
        };
    }

    private function localizeAgingBucket(string $bucket): string
    {
        return match ($bucket) {
            'current' => lang('TenantMisc.current'),
            'days_1_30' => lang('TenantMisc.days1to30'),
            'days_31_60' => lang('TenantMisc.days31to60'),
            'days_61_90' => lang('TenantMisc.days61to90'),
            'days_91_plus' => lang('TenantMisc.days91Plus'),
            default => $bucket !== '' ? strtoupper(str_replace('_', ' ', $bucket)) : lang('TenantMisc.unknown'),
        };
    }

    private function buildPatientLabelMap(array $rows): array
    {
        $clinicId = $this->requireTenantClinicId();
        if (!$clinicId || empty($rows)) {
            return [];
        }

        $patientIds = [];
        foreach ($rows as $row) {
            $id = (int) ($row['patient_id'] ?? 0);
            if ($id > 0) {
                $patientIds[$id] = true;
            }
        }
        if (empty($patientIds)) {
            return [];
        }

        $patients = $this->patientModel
            ->select('id, first_name, last_name')
            ->where('clinic_id', $clinicId)
            ->whereIn('id', array_keys($patientIds))
            ->findAll();

        $map = [];
        foreach ($patients as $patient) {
            $id = (int) ($patient['id'] ?? 0);
            if ($id <= 0) {
                continue;
            }
            $name = trim((string) (($patient['first_name'] ?? '') . ' ' . ($patient['last_name'] ?? '')));
            if ($name === '') {
                $name = 'Patient #' . $id;
            }
            $map[$id] = $name;
        }

        return $map;
    }

    private function applyPatientLabels(array $rows, array $patientLabelMap): array
    {
        if (empty($rows) || empty($patientLabelMap)) {
            return $rows;
        }

        foreach ($rows as &$row) {
            $id = (int) ($row['patient_id'] ?? 0);
            if ($id > 0 && isset($patientLabelMap[$id])) {
                $row['patient_id'] = $patientLabelMap[$id];
                if (!isset($row['patient_name']) || trim((string) $row['patient_name']) === '') {
                    $row['patient_name'] = $patientLabelMap[$id];
                }
            }
        }
        unset($row);

        return $rows;
    }

    private function summarizePatientBalances(array $rows): array
    {
        $summary = [
            'balance_due_total' => 0.0,
            'open_invoices' => 0,
            'patients_with_balance' => 0,
            'overdue_balance_total' => 0.0,
        ];

        $patientBuckets = [];

        foreach ($rows as $row) {
            $balanceDue = (float) ($row['balance_due'] ?? 0);
            if ($balanceDue <= 0) {
                continue;
            }

            $summary['balance_due_total'] += $balanceDue;
            $summary['open_invoices']++;

            if (((int) ($row['days_past_due'] ?? 0)) > 0) {
                $summary['overdue_balance_total'] += $balanceDue;
            }

            $patientId = (int) ($row['patient_id'] ?? 0);
            if ($patientId > 0) {
                $patientBuckets[$patientId] = true;
            }
        }

        $summary['patients_with_balance'] = count($patientBuckets);

        return $summary;
    }

    private function buildTopBalancePatients(array $rows, int $limit = 8): array
    {
        $grouped = [];

        foreach ($rows as $row) {
            $patientId = (int) ($row['patient_id'] ?? 0);
            if ($patientId <= 0) {
                continue;
            }

            if (!isset($grouped[$patientId])) {
                $fullName = trim((string) (($row['first_name'] ?? '') . ' ' . ($row['last_name'] ?? '')));
                $grouped[$patientId] = [
                    'patient_id' => $patientId,
                    'patient_number' => (string) ($row['patient_number'] ?? ''),
                    'full_name' => $fullName !== '' ? $fullName : ('Patient #' . $patientId),
                    'open_invoices' => 0,
                    'balance_due' => 0.0,
                    'overdue_balance' => 0.0,
                    'max_days_past_due' => 0,
                    'row_href' => base_url('patient/' . $patientId),
                ];
            }

            $balanceDue = (float) ($row['balance_due'] ?? 0);
            $daysPastDue = max(0, (int) ($row['days_past_due'] ?? 0));
            $grouped[$patientId]['open_invoices']++;
            $grouped[$patientId]['balance_due'] += $balanceDue;
            $grouped[$patientId]['max_days_past_due'] = max($grouped[$patientId]['max_days_past_due'], $daysPastDue);

            if ($daysPastDue > 0) {
                $grouped[$patientId]['overdue_balance'] += $balanceDue;
            }
        }

        $rows = array_values($grouped);
        usort($rows, static function (array $a, array $b): int {
            $balanceComparison = ($b['balance_due'] <=> $a['balance_due']);
            if ($balanceComparison !== 0) {
                return $balanceComparison;
            }

            return $b['max_days_past_due'] <=> $a['max_days_past_due'];
        });

        return array_slice($rows, 0, $limit);
    }

    private function buildDetailTables(string $reportType, array $reports): array
    {
        $patientLabelMap = $this->buildPatientLabelMap(array_merge(
            $reports['detail_rows']['finance_transactions'] ?? [],
            $reports['detail_rows']['examinations'] ?? [],
            $reports['detail_rows']['appointments'] ?? [],
            $reports['examinations'] ?? [],
            $reports['appointments'] ?? [],
            $reports['treatments'] ?? [],
            $reports['transactions'] ?? []
        ));

        switch ($reportType) {
            case 'overview':
                $overviewFinanceRows = $this->applyPatientLabels($reports['detail_rows']['finance_transactions'] ?? [], $patientLabelMap);
                $overviewExamRows = $this->applyPatientLabels($reports['detail_rows']['examinations'] ?? [], $patientLabelMap);
                $overviewAppointmentRows = $this->applyPatientLabels($reports['detail_rows']['appointments'] ?? [], $patientLabelMap);
                return [
                    $this->makeDetailTable('overview_finance_transactions', lang('TenantMisc.overviewRevenueTransactions'), [
                        ['key' => 'month_label', 'label' => lang('TenantMisc.month')],
                        ['key' => 'transaction_id', 'label' => lang('TenantMisc.transaction')],
                        ['key' => 'patient_id', 'label' => lang('TenantMisc.patient')],
                        ['key' => 'payment_method', 'label' => lang('TenantMisc.paymentMethodFinance')],
                        ['key' => 'payment_status', 'label' => lang('TenantMisc.status')],
                        ['key' => 'total_amount', 'label' => lang('TenantMisc.totalAmount')],
                        ['key' => 'created_at', 'label' => lang('TenantMisc.created')],
                    ], $overviewFinanceRows),
                    $this->makeDetailTable('overview_examinations', lang('TenantMisc.overviewExaminations'), [
                        ['key' => 'examination_type', 'label' => lang('TenantMisc.type')],
                        ['key' => 'patient_id', 'label' => lang('TenantMisc.patient')],
                        ['key' => 'examination_date', 'label' => lang('TenantMisc.date')],
                        ['key' => 'notes', 'label' => lang('TenantMisc.notes')],
                    ], $overviewExamRows),
                    $this->makeDetailTable('overview_appointments', lang('TenantMisc.overviewAppointments'), [
                        ['key' => 'status', 'label' => lang('TenantMisc.status')],
                        ['key' => 'appointment_type', 'label' => lang('TenantMisc.type')],
                        ['key' => 'patient_id', 'label' => lang('TenantMisc.patient')],
                        ['key' => 'appointment_date', 'label' => lang('TenantMisc.date')],
                        ['key' => 'notes', 'label' => lang('TenantMisc.notes')],
                    ], $overviewAppointmentRows),
                ];
            case 'patients':
                return [
                    $this->makeDetailTable('patients', lang('TenantMisc.patientsIncluded'), [
                        ['key' => 'patient_id', 'label' => lang('TenantMisc.patientId')],
                        ['key' => 'full_name', 'label' => lang('TenantMisc.name')],
                        ['key' => 'phone', 'label' => lang('TenantMisc.phone')],
                        ['key' => 'email', 'label' => lang('TenantMisc.email')],
                        ['key' => 'created_at', 'label' => lang('TenantMisc.created')],
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
                $examRows = $this->applyPatientLabels($reports['examinations'] ?? [], $patientLabelMap);
                return [
                    $this->makeDetailTable('examinations', lang('TenantMisc.examinationsIncluded'), [
                        ['key' => 'id', 'label' => lang('TenantMisc.id')],
                        ['key' => 'patient_id', 'label' => lang('TenantMisc.patient')],
                        ['key' => 'examination_type', 'label' => lang('TenantMisc.type')],
                        ['key' => 'examination_date', 'label' => lang('TenantMisc.date')],
                        ['key' => 'notes', 'label' => lang('TenantMisc.notes')],
                    ], $examRows),
                ];
            case 'appointments':
                $appointmentRows = $this->applyPatientLabels($reports['appointments'] ?? [], $patientLabelMap);
                return [
                    $this->makeDetailTable('appointments', lang('TenantMisc.appointmentsIncluded'), [
                        ['key' => 'id', 'label' => lang('TenantMisc.id')],
                        ['key' => 'patient_id', 'label' => lang('TenantMisc.patient')],
                        ['key' => 'appointment_date', 'label' => lang('TenantMisc.date')],
                        ['key' => 'appointment_type', 'label' => lang('TenantMisc.type')],
                        ['key' => 'status', 'label' => lang('TenantMisc.status')],
                    ], $appointmentRows),
                ];
            case 'treatments':
                $treatmentRows = $this->applyPatientLabels($reports['treatments'] ?? [], $patientLabelMap);
                return [
                    $this->makeDetailTable('treatments', lang('TenantMisc.treatmentsIncluded'), [
                        ['key' => 'id', 'label' => lang('TenantMisc.id')],
                        ['key' => 'patient_id', 'label' => lang('TenantMisc.patient')],
                        ['key' => 'treatment_type', 'label' => lang('TenantMisc.type')],
                        ['key' => 'start_date', 'label' => lang('TenantMisc.start')],
                        ['key' => 'status', 'label' => lang('TenantMisc.status')],
                    ], $treatmentRows),
                ];
            default:
                return [];
        }
    }

    private function buildReportDataByType(string $reportType, string $startDate, string $endDate, ?array $compareWindow = null, array $filters = []): array
    {
        switch ($reportType) {
            case 'patients':
                return $this->getPatientReports($startDate, $endDate, $filters);
            case 'examinations':
                return $this->getExaminationReports($startDate, $endDate, $filters);
            case 'appointments':
                return $this->getAppointmentReports($startDate, $endDate, $filters);
            case 'treatments':
                return $this->getTreatmentReports($startDate, $endDate, $filters);
            case 'overview':
            default:
                return $this->getOverviewReports($startDate, $endDate, $compareWindow);
        }
    }

    private function paginateDetailRows(array $rows, int $page, int $perPage, string $search = '', ?string $drillQuery = null, ?string $sortBy = null, string $sortDir = 'asc'): array
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

        $sortBy = trim((string) ($sortBy ?? ''));
        $sortDir = strtolower($sortDir) === 'desc' ? 'desc' : 'asc';
        if ($sortBy !== '') {
            usort($rows, static function ($a, $b) use ($sortBy, $sortDir) {
                $va = $a[$sortBy] ?? null;
                $vb = $b[$sortBy] ?? null;
                $cmp = 0;
                if (is_numeric($va) && is_numeric($vb)) {
                    $cmp = (float) $va <=> (float) $vb;
                } else {
                    $cmp = strcasecmp((string) $va, (string) $vb);
                }
                return $sortDir === 'desc' ? -$cmp : $cmp;
            });
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
            'sorting' => [
                'sort_by' => $sortBy,
                'sort_dir' => $sortDir,
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

            $requestedReportType = strtolower((string) ($this->request->getGet('report_type') ?? $this->request->getGet('type') ?? 'overview'));
            if ($requestedReportType === 'finance') {
                return $this->redirectLegacyFinanceReport();
            }

            $clinicId = $this->requireTenantClinicId();
            if (!$clinicId) {
                return redirect()->to('/clinic/select')->with('error', lang('TenantMisc.selectClinicToViewReports'));
            }

            $filters = $this->parseReportFilters('get');
            $dateRange = $filters['date_range'];
            $reportType = $filters['report_type'];
            $data = [
                'title' => lang('TenantMisc.reportsAnalyticsTitle'),
                'loadSelect2' => true,
                'date_range' => $dateRange,
                'report_type' => $reportType,
                'filters' => $filters,
                'report_patients' => $this->patientModel
                    ->select('id, first_name, last_name, patient_number, phone')
                    ->where('clinic_id', $clinicId)
                    ->orderBy('first_name', 'ASC')
                    ->orderBy('last_name', 'ASC')
                    ->findAll(300),
            ];

            $startDate = $filters['start_date'];
            $endDate = $filters['end_date'];
            $compareWindow = ($filters['compare_mode'] ?? 'none') === 'previous_period'
                ? ['start_date' => $filters['compare_start_date'], 'end_date' => $filters['compare_end_date']]
                : null;

            $data['reports'] = $this->buildReportDataByType($reportType, $startDate, $endDate, $compareWindow, $filters);

            $data['detail_tables'] = $this->buildDetailTables($reportType, $data['reports']);

            // Explicitly add user data to ensure it's available
            $userData = $this->getUserDataForView();
            $data = array_merge($data, $userData);


            return $this->view('reports/index', $data);
        } catch (\Exception $e) {
            // Log the error and show a simplified reports page
            log_message('error', 'Reports error: ' . $e->getMessage());
            
            $data = [
                'title' => lang('TenantMisc.reportsAnalyticsTitle'),
                'loadSelect2' => true,
                'date_range' => '30',
                'report_type' => 'overview',
                'reports' => $this->getBasicReports(),
                'filters' => [
                    'report_type' => 'overview',
                    'date_mode' => 'relative',
                    'compare_mode' => 'none',
                    'date_range' => '30',
                    'custom_from' => null,
                    'custom_to' => null,
                    'start_date' => date('Y-m-d', strtotime('-30 days')),
                    'end_date' => date('Y-m-d'),
                    'compare_start_date' => null,
                    'compare_end_date' => null,
                    'period_label' => lang('TenantMisc.relativePeriodLabel', [
                        'days' => 30,
                        'from' => date('Y-m-d', strtotime('-30 days')),
                        'to' => date('Y-m-d'),
                    ]),
                ],
                'detail_tables' => [],
                'report_patients' => [],
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
            $compareWindow = ($filters['compare_mode'] ?? 'none') === 'previous_period'
                ? ['start_date' => $filters['compare_start_date'], 'end_date' => $filters['compare_end_date']]
                : null;
            $data = $this->buildReportDataByType($reportType, $startDate, $endDate, $compareWindow, $filters);
            
            $data['_detail_tables'] = $this->buildDetailTables($reportType, $data);
            $data['_filters'] = $filters;
            return $this->response->setJSON($data);
        } catch (\Exception $e) {
            log_message('error', 'Chart data error: ' . $e->getMessage());
            return $this->response->setJSON(['error' => lang('TenantMisc.failedFetchChartData')]);
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
            $tableId = trim((string) $this->request->getGet('table_id'));
            $page = max(1, (int) ($this->request->getGet('page') ?? 1));
            $perPage = (int) ($this->request->getGet('per_page') ?? 25);
            $search = (string) ($this->request->getGet('q') ?? '');
            $sortBy = trim((string) ($this->request->getGet('sort_by') ?? ''));
            $sortDir = strtolower((string) ($this->request->getGet('sort_dir') ?? 'asc'));
            $drillTable = trim((string) ($this->request->getGet('drill_table') ?? ''));
            $drillQuery = (string) ($this->request->getGet('drill_query') ?? '');

            $compareWindow = ($filters['compare_mode'] ?? 'none') === 'previous_period'
                ? ['start_date' => $filters['compare_start_date'], 'end_date' => $filters['compare_end_date']]
                : null;
            $reports = $this->buildReportDataByType($reportType, $startDate, $endDate, $compareWindow, $filters);
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
            $allowedSortKeys = array_map(static fn($c) => (string) ($c['key'] ?? ''), $table['columns'] ?? []);
            if (!in_array($sortBy, $allowedSortKeys, true)) {
                $sortBy = '';
            }
            $paged = $this->paginateDetailRows($table['rows'] ?? [], $page, $perPage, $search, $appliedDrillQuery, $sortBy, $sortDir);

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
                'sorting' => $paged['sorting'],
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
            $data = [
                'title' => lang('TenantMisc.exportReportTitle'),
                'report_type' => $reportType,
                'date_range' => $dateRange,
                'date_mode' => $filters['date_mode'],
                'start_date' => $startDate,
                'end_date' => $endDate,
            ];

            $compareWindow = ($filters['compare_mode'] ?? 'none') === 'previous_period'
                ? ['start_date' => $filters['compare_start_date'], 'end_date' => $filters['compare_end_date']]
                : null;
            $data['reports'] = $this->buildReportDataByType($reportType, $startDate, $endDate, $compareWindow, $filters);

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

    private function getOverviewReports($startDate, $endDate, ?array $compareWindow = null)
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

        $result = [
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

        if ($compareWindow && !empty($compareWindow['start_date']) && !empty($compareWindow['end_date'])) {
            $compareStart = (string) $compareWindow['start_date'];
            $compareEnd = (string) $compareWindow['end_date'];
            $compareRevenueStats = $this->financeModel->getFinanceStatsByClinic($clinicId, $compareStart, $compareEnd);
            $result['comparison'] = [
                'label' => $compareStart . ' to ' . $compareEnd,
                'summary' => [
                    'new_patients' => $this->patientModel->where('clinic_id', $clinicId)->where('created_at >=', $compareStart . ' 00:00:00')->where('created_at <=', $compareEnd . ' 23:59:59')->countAllResults(),
                    'examinations_this_period' => $this->examinationModel->where('clinic_id', $clinicId)->where('examination_date >=', $compareStart)->where('examination_date <=', $compareEnd)->countAllResults(),
                    'appointments_this_period' => $this->appointmentModel->where('clinic_id', $clinicId)->where('appointment_date >=', $compareStart)->where('appointment_date <=', $compareEnd)->countAllResults(),
                    'revenue_this_period' => $compareRevenueStats['total_revenue'] ?? 0,
                ],
            ];
        }

        return $result;
    }

    private function getPatientReports($startDate, $endDate, array $filters = [])
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

        $patientsQuery = $this->patientModel
            ->where('clinic_id', $clinicId)
            ->where('created_at >=', $startDate . ' 00:00:00')
            ->where('created_at <=', $endDate . ' 23:59:59');
        if (!empty($filters['patient_id'])) {
            $patientsQuery->where('id', (int) $filters['patient_id']);
        }

        $balanceRows = $this->getHistoricalAccountsReceivableAgingDetail((int) $clinicId, $endDate, 250);
        if (!empty($filters['patient_id'])) {
            $selectedPatientId = (int) $filters['patient_id'];
            $balanceRows = array_values(array_filter($balanceRows, static function (array $row) use ($selectedPatientId): bool {
                return (int) ($row['patient_id'] ?? 0) === $selectedPatientId;
            }));
        }

        return [
            'patients' => $patientsQuery->findAll(),
            'age_groups' => $this->getAgeGroupData($filters),
            'gender_distribution' => $this->getGenderDistributionData($startDate, $endDate, $filters),
            'new_vs_returning' => $this->getNewVsReturningData($startDate, $endDate, $filters),
            'financial_snapshot' => $this->summarizePatientBalances($balanceRows),
            'top_balance_patients' => $this->buildTopBalancePatients($balanceRows, 8),
        ];
    }

    private function getExaminationReports($startDate, $endDate, array $filters = [])
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

        $examQuery = $this->examinationModel
            ->where('clinic_id', $clinicId)
            ->where('examination_date >=', $startDate)
            ->where('examination_date <=', $endDate);
        if (!empty($filters['patient_id'])) {
            $examQuery->where('patient_id', (int) $filters['patient_id']);
        }
        if (!empty($filters['examination_type'])) {
            $examQuery->where('examination_type', (string) $filters['examination_type']);
        }

        return [
            'examinations' => $examQuery->findAll(),
            'examination_types' => $this->getExaminationTypesData($startDate, $endDate, $filters),
            'monthly_examinations' => $this->getMonthlyExaminationsData($startDate, $endDate, $filters),
            'tooth_conditions' => $this->getToothConditionsData(),
        ];
    }

    private function getAppointmentReports($startDate, $endDate, array $filters = [])
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

        $appointmentsQuery = $this->appointmentModel
            ->where('clinic_id', $clinicId)
            ->where('appointment_date >=', $startDate)
            ->where('appointment_date <=', $endDate);
        if (!empty($filters['patient_id'])) {
            $appointmentsQuery->where('patient_id', (int) $filters['patient_id']);
        }
        if (!empty($filters['appointment_status'])) {
            $appointmentsQuery->where('status', (string) $filters['appointment_status']);
        }
        if (!empty($filters['appointment_type'])) {
            $appointmentsQuery->where('appointment_type', (string) $filters['appointment_type']);
        }

        return [
            'appointments' => $appointmentsQuery->findAll(),
            'appointment_types' => $this->getAppointmentTypesData($startDate, $endDate, $filters),
            'monthly_appointments' => $this->getMonthlyAppointmentsData($startDate, $endDate, $filters),
            'status_distribution' => $this->getAppointmentStatusData($startDate, $endDate, $filters),
        ];
    }

    private function getFinanceReports($startDate, $endDate, ?array $compareWindow = null, array $filters = [])
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

        $transactions = $this->buildFinanceCashLedger((int) $clinicId, $startDate, $endDate, $filters);
        $ledgerSummary = $this->summarizeFinanceCashLedger($transactions);
        $arAgingDetail = $this->getHistoricalAccountsReceivableAgingDetail((int) $clinicId, $endDate, 100);
        $arAgingSummary = $this->summarizeAccountsReceivableBuckets($arAgingDetail, $endDate);
        $apAgingDetail = $this->financeModel->getAccountsPayableAgingDetailByClinic((int) $clinicId, $endDate, 100);
        $apAgingSummary = $this->financeModel->getAccountsPayableAgingSummaryByClinic((int) $clinicId, $endDate);
        $providerPayoutStats = $this->providerPayoutModel->getStatsByClinic((int) $clinicId, null, $endDate);

        $moneyIn = (float) ($ledgerSummary['gross_collections'] ?? 0);
        $moneyOut = (float) (($ledgerSummary['refund_total'] ?? 0) + ($ledgerSummary['expense_total'] ?? 0) + ($ledgerSummary['provider_paid_cash'] ?? 0));
        $customersOweUs = (float) ($arAgingSummary['total_balance_due'] ?? 0);
        $expensePayables = (float) ($apAgingSummary['total_balance_due'] ?? 0);
        $providerPayables = (float) ($providerPayoutStats['total_provider_balance'] ?? 0);
        $weOweOthers = $expensePayables + $providerPayables;
        $netPosition = $moneyIn - $moneyOut + $customersOweUs - $weOweOthers;

        $result = [
            'transactions' => $transactions,
            'monthly_revenue' => $this->groupFinanceCashLedgerByMonth($transactions),
            'payment_methods' => $this->groupFinanceCashLedgerByPaymentMethod($transactions),
            'outstanding_payments' => $arAgingDetail,
            'ar_aging_summary' => $arAgingSummary,
            'ar_aging_detail' => $arAgingDetail,
            'ap_aging_summary' => $apAgingSummary,
            'ap_aging_detail' => $apAgingDetail,
            'provider_payout_status' => [],
            'provider_payout_breakdown' => [],
            'provider_payout_rows' => [],
            'transaction_type_mix' => $this->groupFinanceCashLedgerByType($transactions),
            'summary' => [
                'gross_collections' => (float) ($ledgerSummary['gross_collections'] ?? 0),
                'provider_paid_cash' => (float) ($ledgerSummary['provider_paid_cash'] ?? 0),
                'total_expenses' => (float) ($ledgerSummary['expense_total'] ?? 0),
                'operating_result_cash' => (float) (($ledgerSummary['gross_collections'] ?? 0) - ($ledgerSummary['provider_paid_cash'] ?? 0) - ($ledgerSummary['expense_total'] ?? 0)),
                'net_clinic_revenue_cash' => (float) (($ledgerSummary['gross_collections'] ?? 0) - ($ledgerSummary['provider_paid_cash'] ?? 0)),
                'pending_invoice_amount' => (float) ($arAgingSummary['total_balance_due'] ?? 0),
                'outstanding_invoices_count' => (int) ($arAgingSummary['invoice_count'] ?? 0),
                'provider_balance_due' => $weOweOthers,
                'total_transactions' => (int) ($ledgerSummary['count'] ?? count($transactions)),
                'refund_count' => (int) ($ledgerSummary['refund_count'] ?? 0),
                'refund_total' => (float) ($ledgerSummary['refund_total'] ?? 0),
                'discount_total' => 0.0,
                'report_basis' => 'cash',
                'money_in' => round($moneyIn, 2),
                'money_out' => round($moneyOut, 2),
                'customers_owe_us' => round($customersOweUs, 2),
                'we_owe_others' => round($weOweOthers, 2),
                'expense_payables' => round($expensePayables, 2),
                'provider_payables' => round($providerPayables, 2),
                'net_position' => round($netPosition, 2),
                'as_of_date' => $endDate,
            ],
        ];

        if ($compareWindow && !empty($compareWindow['start_date']) && !empty($compareWindow['end_date'])) {
            $cStart = (string) $compareWindow['start_date'];
            $cEnd = (string) $compareWindow['end_date'];
            $compareTransactions = $this->buildFinanceCashLedger((int) $clinicId, $cStart, $cEnd, $filters);
            $compareSummary = $this->summarizeFinanceCashLedger($compareTransactions);
            $compareAr = $this->summarizeAccountsReceivableBuckets(
                $this->getHistoricalAccountsReceivableAgingDetail((int) $clinicId, $cEnd, 100),
                $cEnd
            );
            $compareAp = $this->financeModel->getAccountsPayableAgingSummaryByClinic((int) $clinicId, $cEnd);
            $compareProviderStats = $this->providerPayoutModel->getStatsByClinic((int) $clinicId, null, $cEnd);
            $compareMoneyIn = (float) ($compareSummary['gross_collections'] ?? 0);
            $compareMoneyOut = (float) (($compareSummary['refund_total'] ?? 0) + ($compareSummary['expense_total'] ?? 0) + ($compareSummary['provider_paid_cash'] ?? 0));
            $compareCustomersOweUs = (float) ($compareAr['total_balance_due'] ?? 0);
            $compareWeOweOthers = (float) ($compareAp['total_balance_due'] ?? 0) + (float) ($compareProviderStats['total_provider_balance'] ?? 0);
            $result['comparison'] = [
                'label' => $cStart . ' to ' . $cEnd,
                'summary' => [
                    'money_in' => round($compareMoneyIn, 2),
                    'money_out' => round($compareMoneyOut, 2),
                    'customers_owe_us' => round($compareCustomersOweUs, 2),
                    'we_owe_others' => round($compareWeOweOthers, 2),
                    'net_position' => round($compareMoneyIn - $compareMoneyOut + $compareCustomersOweUs - $compareWeOweOthers, 2),
                    'total_transactions' => (int) ($compareSummary['count'] ?? 0),
                ],
            ];
        }

        return $result;
    }

    private function buildFinanceCashLedger(int $clinicId, string $startDate, string $endDate, array $filters = []): array
    {
        $baseCurrency = settings()->get('currency', 'ILS');
        $directReportDateExpr = "CASE WHEN f.transaction_type = 'expense' THEN COALESCE(f.paid_date, f.transaction_date, DATE(f.created_at)) ELSE COALESCE(f.transaction_date, f.paid_date, DATE(f.created_at)) END";
        $selectedPatientId = (int) ($filters['patient_id'] ?? 0);
        $selectedTransactionType = (string) ($filters['transaction_type'] ?? '');
        $selectedPaymentMethod = (string) ($filters['payment_method'] ?? '');
        $financeAmountExpr = $this->db->fieldExists('total_amount', 'finances')
            ? 'f.total_amount'
            : '(f.amount - COALESCE(f.discount_amount, 0) + COALESCE(f.tax_amount, 0))';
        $rows = [];

        $financeRows = $this->db->table('finances f')
            ->select("
                f.id,
                f.transaction_id,
                f.patient_id,
                f.transaction_type,
                f.payment_method,
                f.payment_status,
                f.description,
                f.created_at,
                f.paid_date,
                f.transaction_date,
                f.currency,
                f.exchange_rate,
                f.amount_original,
                f.amount_paid,
                f.counterparty_name,
                {$financeAmountExpr} AS amount_base,
                (
                    SELECT COUNT(*)
                    FROM finance_payments fp_exists
                    WHERE fp_exists.clinic_id = f.clinic_id
                      AND fp_exists.finance_id = f.id
                ) AS settlement_count,
                p.first_name AS patient_first_name,
                p.last_name AS patient_last_name,
                p.patient_id AS patient_number
            ", false)
            ->join('patients p', 'p.id = f.patient_id AND p.clinic_id = f.clinic_id', 'left')
            ->where('f.clinic_id', $clinicId)
            ->whereIn('f.transaction_type', ['payment', 'refund', 'expense'])
            ->where($directReportDateExpr . ' >= ' . $this->db->escape($startDate), null, false)
            ->where($directReportDateExpr . ' <= ' . $this->db->escape($endDate), null, false)
            ->orderBy($directReportDateExpr, 'DESC', false)
            ->orderBy('f.id', 'DESC')
            ->get()
            ->getResultArray();

        foreach ($financeRows as $row) {
            $type = (string) ($row['transaction_type'] ?? '');
            $patientId = (int) ($row['patient_id'] ?? 0);
            if ($selectedPatientId > 0 && $patientId !== $selectedPatientId) {
                continue;
            }
            if ($selectedTransactionType !== '' && $selectedTransactionType !== $type) {
                continue;
            }
            $method = (string) ($row['payment_method'] ?? '');
            if ($selectedPaymentMethod !== '' && $selectedPaymentMethod !== $method) {
                continue;
            }

            $amountBase = floatval($row['amount_base'] ?? 0);
            if ($type === 'expense') {
                $settlementCount = (int) ($row['settlement_count'] ?? 0);
                $legacyPaidAmount = round(floatval($row['amount_paid'] ?? 0), 2);
                if ($settlementCount > 0 || $legacyPaidAmount <= 0) {
                    continue;
                }
                $amountBase = min($amountBase, $legacyPaidAmount);
            }
            $signedAmount = in_array($type, ['refund', 'expense'], true) ? -$amountBase : $amountBase;
            $patientName = trim((string) (($row['patient_first_name'] ?? '') . ' ' . ($row['patient_last_name'] ?? '')));
            $reportDate = $type === 'expense'
                ? (!empty($row['paid_date']) ? (string) $row['paid_date'] : (!empty($row['transaction_date']) ? (string) $row['transaction_date'] : substr((string) ($row['created_at'] ?? ''), 0, 10)))
                : (!empty($row['transaction_date']) ? (string) $row['transaction_date'] : (!empty($row['paid_date']) ? (string) $row['paid_date'] : substr((string) ($row['created_at'] ?? ''), 0, 10)));

            $rows[] = [
                'id' => (int) ($row['id'] ?? 0),
                'finance_id' => (int) ($row['id'] ?? 0),
                'provider_payout_id' => null,
                'transaction_id' => (string) ($row['transaction_id'] ?? ('F-' . ($row['id'] ?? ''))),
                'patient_id' => $patientId,
                'patient_name' => $patientName,
                'patient_number' => $row['patient_number'] ?? null,
                'transaction_type' => $type,
                'event_source' => 'direct_transaction',
                'source_label' => 'Direct transaction',
                'payment_method' => $method,
                'payment_status' => $row['payment_status'] ?? null,
                'notes' => $row['description'] ?? null,
                'counterparty_name' => $row['counterparty_name'] ?? null,
                'currency' => $row['currency'] ?: $baseCurrency,
                'exchange_rate' => floatval($row['exchange_rate'] ?? 1),
                'amount_original' => isset($row['amount_original']) ? floatval($row['amount_original']) : $amountBase,
                'amount_base' => $amountBase,
                'signed_amount_base' => $signedAmount,
                'report_date' => $reportDate,
                'created_at' => (string) ($row['created_at'] ?? ''),
                'event_at' => $reportDate . ' 00:00:00',
            ];
        }

        $paymentFxSelect = $this->supportsFinancePaymentExchangeFields()
            ? "
                fp.currency AS settlement_currency,
                fp.amount_original AS settlement_amount_original,
                fp.exchange_rate AS settlement_exchange_rate,
            "
            : "
                NULL AS settlement_currency,
                NULL AS settlement_amount_original,
                NULL AS settlement_exchange_rate,
            ";

        $invoicePaymentRows = $this->db->table('finance_payments fp')
            ->select("
                fp.id,
                fp.type,
                fp.payment_method,
                fp.payment_date,
                fp.notes,
                fp.amount,
                f.transaction_type AS finance_transaction_type,
                f.counterparty_name,
                {$paymentFxSelect}
                f.id AS finance_id,
                f.patient_id,
                f.transaction_id AS invoice_transaction_id,
                f.currency,
                p.first_name AS patient_first_name,
                p.last_name AS patient_last_name,
                p.patient_id AS patient_number
            ")
            ->join('finances f', 'f.id = fp.finance_id AND f.clinic_id = fp.clinic_id', 'inner')
            ->join('patients p', 'p.id = f.patient_id AND p.clinic_id = f.clinic_id', 'left')
            ->where('fp.clinic_id', $clinicId)
            ->where('fp.payment_date >=', $startDate)
            ->where('fp.payment_date <=', $endDate)
            ->whereIn('f.transaction_type', ['invoice', 'expense'])
            ->whereIn('fp.type', ['payment', 'refund', 'void', 'adjustment'])
            ->orderBy('fp.payment_date', 'DESC')
            ->orderBy('fp.id', 'DESC')
            ->get()
            ->getResultArray();

        foreach ($invoicePaymentRows as $row) {
            $type = (string) ($row['type'] ?? '');
            $financeTransactionType = (string) ($row['finance_transaction_type'] ?? 'invoice');
            $patientId = (int) ($row['patient_id'] ?? 0);
            if ($selectedPatientId > 0 && $patientId !== $selectedPatientId) {
                continue;
            }
            $method = (string) ($row['payment_method'] ?? '');
            if ($selectedPaymentMethod !== '' && $selectedPaymentMethod !== $method) {
                continue;
            }

            $amountBase = floatval($row['amount'] ?? 0);
            $patientName = trim((string) (($row['patient_first_name'] ?? '') . ' ' . ($row['patient_last_name'] ?? '')));

            if ($financeTransactionType === 'expense') {
                $normalizedType = 'expense';
                if ($selectedTransactionType !== '' && $selectedTransactionType !== $normalizedType) {
                    continue;
                }
                $signedAmount = in_array($type, ['refund', 'void'], true) ? $amountBase : -$amountBase;
                $eventSource = 'expense_settlement';
                $sourceLabel = 'Expense settlement';
                $transactionId = (string) (($row['invoice_transaction_id'] ?? 'Expense') . ' / EXP-' . ($row['id'] ?? ''));
                $patientName = '';
                $patientNumber = null;
                $counterparty = $row['counterparty_name'] ?? null;
            } else {
                $normalizedType = $type === 'void' ? 'void' : $type;
                if ($selectedTransactionType !== '' && $selectedTransactionType !== $normalizedType) {
                    continue;
                }
                $signedAmount = in_array($type, ['refund', 'void'], true) ? -$amountBase : $amountBase;
                $eventSource = 'invoice_settlement';
                $sourceLabel = 'Invoice settlement';
                $transactionId = (string) (($row['invoice_transaction_id'] ?? 'Invoice') . ' / PMT-' . ($row['id'] ?? ''));
                $patientNumber = $row['patient_number'] ?? null;
                $counterparty = null;
            }

            $rows[] = [
                'id' => (int) ($row['id'] ?? 0),
                'finance_id' => (int) ($row['finance_id'] ?? 0),
                'provider_payout_id' => null,
                'transaction_id' => $transactionId,
                'patient_id' => $patientId,
                'patient_name' => $patientName,
                'patient_number' => $patientNumber,
                'transaction_type' => $normalizedType,
                'event_source' => $eventSource,
                'source_label' => $sourceLabel,
                'payment_method' => $method,
                'payment_status' => null,
                'notes' => $row['notes'] ?? null,
                'counterparty_name' => $counterparty,
                'currency' => $row['settlement_currency'] ?: ($row['currency'] ?: $baseCurrency),
                'exchange_rate' => floatval($row['settlement_exchange_rate'] ?? 1),
                'amount_original' => isset($row['settlement_amount_original']) ? floatval($row['settlement_amount_original']) : $amountBase,
                'amount_base' => $amountBase,
                'signed_amount_base' => $signedAmount,
                'report_date' => (string) ($row['payment_date'] ?? ''),
                'created_at' => ((string) ($row['payment_date'] ?? '')) . ' 00:00:00',
                'event_at' => ((string) ($row['payment_date'] ?? '')) . ' 00:00:00',
            ];
        }

        $providerPaymentRows = $this->db->table('provider_payout_payments ppp')
            ->select("
                ppp.id,
                ppp.provider_payout_id,
                ppp.amount,
                ppp.payment_method,
                ppp.paid_date,
                ppp.notes,
                pp.patient_id,
                pp.provider_user_id,
                pp.provider_name_snapshot,
                pp.finance_id,
                f.transaction_id,
                p.first_name AS patient_first_name,
                p.last_name AS patient_last_name,
                p.patient_id AS patient_number,
                u.first_name AS provider_first_name,
                u.last_name AS provider_last_name
            ")
            ->join('provider_payouts pp', 'pp.id = ppp.provider_payout_id AND pp.clinic_id = ppp.clinic_id', 'inner')
            ->join('finances f', 'f.id = pp.finance_id AND f.clinic_id = pp.clinic_id', 'left')
            ->join('patients p', 'p.id = pp.patient_id AND p.clinic_id = pp.clinic_id', 'left')
            ->join('users u', 'u.id = pp.provider_user_id', 'left')
            ->where('ppp.clinic_id', $clinicId)
            ->where('ppp.paid_date >=', $startDate)
            ->where('ppp.paid_date <=', $endDate)
            ->orderBy('ppp.paid_date', 'DESC')
            ->orderBy('ppp.id', 'DESC')
            ->get()
            ->getResultArray();

        foreach ($providerPaymentRows as $row) {
            $patientId = (int) ($row['patient_id'] ?? 0);
            if ($selectedPatientId > 0 && $patientId !== $selectedPatientId) {
                continue;
            }
            if ($selectedTransactionType !== '' && $selectedTransactionType !== 'provider_payout') {
                continue;
            }
            $method = (string) ($row['payment_method'] ?? '');
            if ($selectedPaymentMethod !== '' && $selectedPaymentMethod !== $method) {
                continue;
            }

            $providerLabel = trim((string) (($row['provider_first_name'] ?? '') . ' ' . ($row['provider_last_name'] ?? '')));
            if ($providerLabel === '') {
                $providerLabel = (string) ($row['provider_name_snapshot'] ?? ('Provider #' . (string) ($row['provider_user_id'] ?? '0')));
            }
            $patientName = trim((string) (($row['patient_first_name'] ?? '') . ' ' . ($row['patient_last_name'] ?? '')));
            $amountBase = floatval($row['amount'] ?? 0);

            $rows[] = [
                'id' => (int) ($row['id'] ?? 0),
                'finance_id' => (int) ($row['finance_id'] ?? 0),
                'provider_payout_id' => (int) ($row['provider_payout_id'] ?? 0),
                'transaction_id' => (string) (($row['transaction_id'] ?? 'Finance') . ' / PYP-' . ($row['id'] ?? '')),
                'patient_id' => $patientId,
                'patient_name' => $patientName,
                'patient_number' => $row['patient_number'] ?? null,
                'transaction_type' => 'provider_payout',
                'event_source' => 'provider_payout',
                'source_label' => 'Provider payout',
                'payment_method' => $method,
                'payment_status' => null,
                'notes' => $row['notes'] ?? 'Provider payout cash payment',
                'counterparty_name' => $providerLabel,
                'currency' => $baseCurrency,
                'exchange_rate' => 1.0,
                'amount_original' => $amountBase,
                'amount_base' => $amountBase,
                'signed_amount_base' => -$amountBase,
                'report_date' => (string) ($row['paid_date'] ?? ''),
                'created_at' => ((string) ($row['paid_date'] ?? '')) . ' 00:00:00',
                'event_at' => ((string) ($row['paid_date'] ?? '')) . ' 00:00:00',
            ];
        }

        usort($rows, static function (array $a, array $b): int {
            return strcmp((string) ($b['event_at'] ?? ''), (string) ($a['event_at'] ?? ''));
        });

        return $rows;
    }

    private function summarizeFinanceCashLedger(array $rows): array
    {
        $grossCollections = 0.0;
        $expenseTotal = 0.0;
        $providerPaidCash = 0.0;
        $refundCount = 0;
        $refundTotal = 0.0;
        $safeCashIn = 0.0;
        $safeCashOut = 0.0;

        foreach ($rows as $row) {
            $type = (string) ($row['transaction_type'] ?? '');
            $signed = floatval($row['signed_amount_base'] ?? 0);
            $method = strtolower((string) ($row['payment_method'] ?? ''));

            if (in_array($type, ['payment', 'refund', 'void'], true)) {
                $grossCollections += $signed;
            }
            if ($type === 'expense') {
                $expenseTotal += abs($signed);
            }
            if ($type === 'provider_payout') {
                $providerPaidCash += abs($signed);
            }
            if (in_array($type, ['refund', 'void'], true)) {
                $refundCount++;
                $refundTotal += abs($signed);
            }

            if ($method === 'cash') {
                if ($type === 'payment' && $signed > 0) {
                    $safeCashIn += $signed;
                }

                if (in_array($type, ['refund', 'void', 'expense', 'provider_payout'], true)) {
                    $safeCashOut += abs($signed);
                }
            }
        }

        return [
            'count' => count($rows),
            'gross_collections' => round($grossCollections, 2),
            'expense_total' => round($expenseTotal, 2),
            'provider_paid_cash' => round($providerPaidCash, 2),
            'refund_count' => $refundCount,
            'refund_total' => round($refundTotal, 2),
            'safe_cash_in' => round($safeCashIn, 2),
            'safe_cash_out' => round($safeCashOut, 2),
            'safe_cash_net' => round($safeCashIn - $safeCashOut, 2),
        ];
    }

    private function groupFinanceCashLedgerByMonth(array $rows): array
    {
        $monthly = [];
        foreach ($rows as $row) {
            $date = (string) ($row['report_date'] ?? '');
            if ($date === '') {
                continue;
            }
            $monthKey = date('Y-m-01', strtotime($date));
            $monthLabel = date('M Y', strtotime($monthKey));
            if (!isset($monthly[$monthKey])) {
                $monthly[$monthKey] = ['month' => $monthLabel, 'total_amount' => 0.0];
            }
            $monthly[$monthKey]['total_amount'] += floatval($row['signed_amount_base'] ?? 0);
        }
        ksort($monthly);
        return array_values($monthly);
    }

    private function groupFinanceCashLedgerByPaymentMethod(array $rows): array
    {
        $methods = [];
        foreach ($rows as $row) {
            $type = (string) ($row['transaction_type'] ?? '');
            if ($type !== 'payment') {
                continue;
            }
            $method = (string) ($row['payment_method'] ?? 'unknown');
            if ($method === '') {
                $method = 'unknown';
            }
            if (!isset($methods[$method])) {
                $methods[$method] = [
                    'payment_method' => $method,
                    'total_amount' => 0.0,
                    'transaction_count' => 0,
                ];
            }
            $methods[$method]['total_amount'] += floatval($row['signed_amount_base'] ?? 0);
            $methods[$method]['transaction_count'] += 1;
        }
        uasort($methods, static fn(array $a, array $b): int => $b['total_amount'] <=> $a['total_amount']);
        return array_values($methods);
    }

    private function groupFinanceCashLedgerByType(array $rows): array
    {
        $types = [];
        foreach ($rows as $row) {
            $type = (string) ($row['transaction_type'] ?? 'unknown');
            if (!isset($types[$type])) {
                $types[$type] = ['transaction_type' => $type, 'total_amount' => 0.0];
            }
            $types[$type]['total_amount'] += floatval($row['signed_amount_base'] ?? 0);
        }
        return array_values($types);
    }

    private function getHistoricalAccountsReceivableAgingDetail(int $clinicId, string $asOfDate, int $limit = 100): array
    {
        $limit = max(1, min($limit, 10000));
        $asOfDateTime = $asOfDate . ' 23:59:59';
        $sumField = $this->db->fieldExists('total_amount', 'finances')
            ? 'f.total_amount'
            : '(f.amount - COALESCE(f.discount_amount, 0) + COALESCE(f.tax_amount, 0))';

        $rows = $this->db->table('finances f')
            ->select("
                f.id,
                f.patient_id,
                f.transaction_id,
                {$sumField} AS total_amount,
                f.due_date,
                f.transaction_date,
                f.created_at,
                p.first_name,
                p.last_name,
                p.patient_id AS patient_number,
                COALESCE((
                    SELECT SUM(
                        CASE
                            WHEN fp.type IN ('payment','adjustment') THEN fp.amount
                            WHEN fp.type IN ('refund','void') THEN -fp.amount
                            ELSE 0
                        END
                    )
                    FROM finance_payments fp
                    WHERE fp.clinic_id = f.clinic_id
                      AND fp.finance_id = f.id
                      AND fp.payment_date <= " . $this->db->escape($asOfDate) . "
                ), 0) AS paid_total
            ", false)
            ->join('patients p', 'p.id = f.patient_id AND p.clinic_id = f.clinic_id', 'left')
            ->where('f.clinic_id', $clinicId)
            ->where('f.transaction_type', 'invoice')
            ->where('f.created_at <=', $asOfDateTime)
            ->having('total_amount - paid_total >', 0, false)
            ->orderBy('f.due_date', 'ASC')
            ->limit($limit)
            ->get()
            ->getResultArray();

        foreach ($rows as &$row) {
            $totalAmount = floatval($row['total_amount'] ?? 0);
            $paidTotal = floatval($row['paid_total'] ?? 0);
            $balanceDue = max(0, $totalAmount - $paidTotal);
            $dueDate = !empty($row['due_date'])
                ? (string) $row['due_date']
                : (!empty($row['transaction_date']) ? (string) $row['transaction_date'] : substr((string) ($row['created_at'] ?? $asOfDate), 0, 10));
            $row['due_date'] = $dueDate;
            $daysPastDue = (int) floor((strtotime($asOfDate) - strtotime($dueDate)) / 86400);
            $row['balance_due'] = round($balanceDue, 2);
            $row['days_past_due'] = max(0, $daysPastDue);
            $row['aging_bucket'] = $this->mapAgingBucket($daysPastDue);
        }
        unset($row);

        return $rows;
    }

    private function summarizeAccountsReceivableBuckets(array $rows, string $asOfDate): array
    {
        $summary = [
            'current' => 0.0,
            'days_1_30' => 0.0,
            'days_31_60' => 0.0,
            'days_61_90' => 0.0,
            'days_91_plus' => 0.0,
            'total_balance_due' => 0.0,
            'invoice_count' => 0,
            'as_of_date' => $asOfDate,
        ];

        foreach ($rows as $row) {
            $balance = (float) ($row['balance_due'] ?? 0);
            $bucket = (string) ($row['aging_bucket'] ?? 'current');
            if (!array_key_exists($bucket, $summary)) {
                $bucket = 'current';
            }
            $summary[$bucket] += $balance;
            $summary['total_balance_due'] += $balance;
            $summary['invoice_count']++;
        }

        return $summary;
    }

    private function mapAgingBucket(int $daysPastDue): string
    {
        if ($daysPastDue <= 0) {
            return 'current';
        }
        if ($daysPastDue <= 30) {
            return 'days_1_30';
        }
        if ($daysPastDue <= 60) {
            return 'days_31_60';
        }
        if ($daysPastDue <= 90) {
            return 'days_61_90';
        }
        return 'days_91_plus';
    }

    private function supportsFinancePaymentExchangeFields(): bool
    {
        return $this->db->fieldExists('currency', 'finance_payments')
            && $this->db->fieldExists('amount_original', 'finance_payments')
            && $this->db->fieldExists('exchange_rate', 'finance_payments');
    }

    private function getTreatmentReports($startDate, $endDate, array $filters = [])
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

        $treatmentQuery = $this->treatmentModel
            ->where('clinic_id', $clinicId)
            ->where('start_date >=', $startDate)
            ->where('start_date <=', $endDate);
        if (!empty($filters['patient_id'])) {
            $treatmentQuery->where('patient_id', (int) $filters['patient_id']);
        }
        if (!empty($filters['treatment_status'])) {
            $treatmentQuery->where('status', (string) $filters['treatment_status']);
        }
        if (!empty($filters['treatment_type'])) {
            $treatmentQuery->where('treatment_type', (string) $filters['treatment_type']);
        }

        return [
            'treatments' => $treatmentQuery->findAll(),
            'treatment_types' => $this->getTreatmentTypesData($startDate, $endDate, $filters),
            'monthly_treatments' => $this->getMonthlyTreatmentsData($startDate, $endDate, $filters),
            'status_distribution' => $this->getTreatmentStatusData($startDate, $endDate, $filters),
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

    private function getExaminationTypesData(?string $startDate = null, ?string $endDate = null, array $filters = [])
    {
        $query = $this->examinationModel->select('examination_type, COUNT(*) as count')
                                       ->where('clinic_id', session()->get('active_clinic_id'));
        if ($startDate) {
            $query->where('examination_date >=', $startDate);
        }
        if ($endDate) {
            $query->where('examination_date <=', $endDate);
        }
        if (!empty($filters['patient_id'])) {
            $query->where('patient_id', (int) $filters['patient_id']);
        }
        if (!empty($filters['examination_type'])) {
            $query->where('examination_type', (string) $filters['examination_type']);
        }
        $types = $query->groupBy('examination_type')->findAll();
        return $types;
    }

    private function getAppointmentStatusData(?string $startDate = null, ?string $endDate = null, array $filters = [])
    {
        $query = $this->appointmentModel->select('status, COUNT(*) as count')
                                          ->where('clinic_id', session()->get('active_clinic_id'));
        if ($startDate) {
            $query->where('appointment_date >=', $startDate);
        }
        if ($endDate) {
            $query->where('appointment_date <=', $endDate);
        }
        if (!empty($filters['patient_id'])) {
            $query->where('patient_id', (int) $filters['patient_id']);
        }
        if (!empty($filters['appointment_status'])) {
            $query->where('status', (string) $filters['appointment_status']);
        }
        if (!empty($filters['appointment_type'])) {
            $query->where('appointment_type', (string) $filters['appointment_type']);
        }
        $statuses = $query->groupBy('status')->findAll();
        return $statuses;
    }

    private function getAgeGroupData(array $filters = [])
    {
        try {
            $clinicId = session()->get('active_clinic_id');
            if (!$clinicId) {
                return [];
            }

            $patientsQuery = $this->patientModel->select('date_of_birth')->where('clinic_id', $clinicId);
            if (!empty($filters['patient_id'])) {
                $patientsQuery->where('id', (int) $filters['patient_id']);
            }
            $patients = $patientsQuery->findAll();
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

    private function getGenderDistributionData(?string $startDate = null, ?string $endDate = null, array $filters = [])
    {
        $query = $this->patientModel->select('gender, COUNT(*) as count')
                                 ->where('clinic_id', session()->get('active_clinic_id'));
        if ($startDate) {
            $query->where('created_at >=', $startDate . ' 00:00:00');
        }
        if ($endDate) {
            $query->where('created_at <=', $endDate . ' 23:59:59');
        }
        if (!empty($filters['patient_id'])) {
            $query->where('id', (int) $filters['patient_id']);
        }
        return $query->groupBy('gender')->findAll();
    }

    private function getNewVsReturningData($startDate, $endDate, array $filters = [])
    {
        $clinicId = session()->get('active_clinic_id');
        if (!$clinicId) {
            return ['new' => 0, 'returning' => 0];
        }

        $newQuery = $this->patientModel->where('clinic_id', $clinicId)
            ->where('created_at >=', $startDate . ' 00:00:00')
            ->where('created_at <=', $endDate . ' 23:59:59');
        $returningQuery = $this->patientModel->where('clinic_id', $clinicId)->where('created_at <', $startDate);
        if (!empty($filters['patient_id'])) {
            $newQuery->where('id', (int) $filters['patient_id']);
            $returningQuery->where('id', (int) $filters['patient_id']);
        }
        $new = $newQuery->countAllResults();
        $returning = $returningQuery->countAllResults();
        
        return [
            'new' => $new,
            'returning' => $returning,
        ];
    }

    private function getMonthlyExaminationsData(?string $startDate = null, ?string $endDate = null, array $filters = [])
    {
        $clinicId = session()->get('active_clinic_id');
        if (!$clinicId) {
            return [];
        }

        $data = [];
        foreach ($this->getMonthlyBuckets($startDate, $endDate) as $date) {
            $monthStart = date('Y-m-01', strtotime($date));
            $monthEnd = date('Y-m-t', strtotime($date));
            $countQuery = $this->examinationModel->where('clinic_id', $clinicId)
                                           ->where('examination_date >=', $monthStart)
                                           ->where('examination_date <=', $monthEnd);
            if (!empty($filters['patient_id'])) {
                $countQuery->where('patient_id', (int) $filters['patient_id']);
            }
            if (!empty($filters['examination_type'])) {
                $countQuery->where('examination_type', (string) $filters['examination_type']);
            }
            $count = $countQuery->countAllResults();
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

    private function getAppointmentTypesData(?string $startDate = null, ?string $endDate = null, array $filters = [])
    {
        $query = $this->appointmentModel->select('appointment_type, COUNT(*) as count')
                                     ->where('clinic_id', session()->get('active_clinic_id'));
        if ($startDate) {
            $query->where('appointment_date >=', $startDate);
        }
        if ($endDate) {
            $query->where('appointment_date <=', $endDate);
        }
        if (!empty($filters['patient_id'])) {
            $query->where('patient_id', (int) $filters['patient_id']);
        }
        if (!empty($filters['appointment_status'])) {
            $query->where('status', (string) $filters['appointment_status']);
        }
        if (!empty($filters['appointment_type'])) {
            $query->where('appointment_type', (string) $filters['appointment_type']);
        }
        return $query->groupBy('appointment_type')->findAll();
    }

    private function getMonthlyAppointmentsData(?string $startDate = null, ?string $endDate = null, array $filters = [])
    {
        $clinicId = session()->get('active_clinic_id');
        if (!$clinicId) {
            return [];
        }

        $data = [];
        foreach ($this->getMonthlyBuckets($startDate, $endDate) as $date) {
            $monthStart = date('Y-m-01', strtotime($date));
            $monthEnd = date('Y-m-t', strtotime($date));
            $countQuery = $this->appointmentModel->where('clinic_id', $clinicId)
                                           ->where('appointment_date >=', $monthStart)
                                           ->where('appointment_date <=', $monthEnd);
            if (!empty($filters['patient_id'])) {
                $countQuery->where('patient_id', (int) $filters['patient_id']);
            }
            if (!empty($filters['appointment_status'])) {
                $countQuery->where('status', (string) $filters['appointment_status']);
            }
            if (!empty($filters['appointment_type'])) {
                $countQuery->where('appointment_type', (string) $filters['appointment_type']);
            }
            $count = $countQuery->countAllResults();
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

    private function getTreatmentTypesData(?string $startDate = null, ?string $endDate = null, array $filters = [])
    {
        $query = $this->treatmentModel->select('treatment_type, COUNT(*) as count')
                                   ->where('clinic_id', session()->get('active_clinic_id'));
        if ($startDate) {
            $query->where('start_date >=', $startDate);
        }
        if ($endDate) {
            $query->where('start_date <=', $endDate);
        }
        if (!empty($filters['patient_id'])) {
            $query->where('patient_id', (int) $filters['patient_id']);
        }
        if (!empty($filters['treatment_status'])) {
            $query->where('status', (string) $filters['treatment_status']);
        }
        if (!empty($filters['treatment_type'])) {
            $query->where('treatment_type', (string) $filters['treatment_type']);
        }
        return $query->groupBy('treatment_type')->findAll();
    }

    private function getMonthlyTreatmentsData(?string $startDate = null, ?string $endDate = null, array $filters = [])
    {
        $clinicId = session()->get('active_clinic_id');
        if (!$clinicId) {
            return [];
        }

        $data = [];
        foreach ($this->getMonthlyBuckets($startDate, $endDate) as $date) {
            $monthStart = date('Y-m-01', strtotime($date));
            $monthEnd = date('Y-m-t', strtotime($date));
            $countQuery = $this->treatmentModel->where('clinic_id', $clinicId)
                                         ->where('start_date >=', $monthStart)
                                         ->where('start_date <=', $monthEnd);
            if (!empty($filters['patient_id'])) {
                $countQuery->where('patient_id', (int) $filters['patient_id']);
            }
            if (!empty($filters['treatment_status'])) {
                $countQuery->where('status', (string) $filters['treatment_status']);
            }
            if (!empty($filters['treatment_type'])) {
                $countQuery->where('treatment_type', (string) $filters['treatment_type']);
            }
            $count = $countQuery->countAllResults();
            $data[] = [
                'month' => date('M Y', strtotime($date)),
                'count' => $count
            ];
        }
        return $data;
    }

    private function getTreatmentStatusData(?string $startDate = null, ?string $endDate = null, array $filters = [])
    {
        $query = $this->treatmentModel->select('status, COUNT(*) as count')
                                   ->where('clinic_id', session()->get('active_clinic_id'));
        if ($startDate) {
            $query->where('start_date >=', $startDate);
        }
        if ($endDate) {
            $query->where('start_date <=', $endDate);
        }
        if (!empty($filters['patient_id'])) {
            $query->where('patient_id', (int) $filters['patient_id']);
        }
        if (!empty($filters['treatment_status'])) {
            $query->where('status', (string) $filters['treatment_status']);
        }
        if (!empty($filters['treatment_type'])) {
            $query->where('treatment_type', (string) $filters['treatment_type']);
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
                <h2>' . $this->html(lang('TenantMisc.financialTransactions')) . '</h2>
                <table>
                    <thead>
                        <tr>
                            <th>' . $this->html(lang('TenantMisc.reportDate')) . '</th>
                            <th>' . $this->html(lang('TenantMisc.recordedAt')) . '</th>
                            <th>' . $this->html(lang('TenantMisc.transaction')) . '</th>
                            <th>' . $this->html(lang('TenantMisc.patientPayee')) . '</th>
                            <th>' . $this->html(lang('TenantMisc.type')) . '</th>
                            <th>' . $this->html(lang('TenantMisc.source')) . '</th>
                            <th>' . $this->html(lang('TenantMisc.paymentMethodFinance')) . '</th>
                            <th>' . $this->html(lang('TenantMisc.totalAmount')) . '</th>
                        </tr>
                    </thead>
                    <tbody>';
            
            foreach ($data['reports']['transactions'] as $transaction) {
                $subject = trim((string) ($transaction['patient_name'] ?? ''));
                if ($subject === '') {
                    $subject = (string) ($transaction['counterparty_name'] ?? '');
                }
                $html .= '<tr>
                    <td>' . $this->html((string) ($transaction['report_date'] ?? '')) . '</td>
                    <td>' . $this->html((string) ($transaction['created_at'] ?? '')) . '</td>
                    <td>' . $this->html((string) ($transaction['transaction_id'] ?? '')) . '</td>
                    <td>' . $this->html($subject) . '</td>
                    <td>' . $this->html($this->localizeFinanceTransactionType((string) ($transaction['transaction_type'] ?? ''))) . '</td>
                    <td>' . $this->html($this->localizeFinanceSourceLabel((string) ($transaction['source_label'] ?? ''), (string) ($transaction['event_source'] ?? 'finance'))) . '</td>
                    <td>' . $this->html($this->localizeFinancePaymentMethod((string) ($transaction['payment_method'] ?? ''))) . '</td>
                    <td>' . $this->html(number_format((float) ($transaction['signed_amount_base'] ?? $transaction['total_amount'] ?? 0), 2)) . '</td>
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
            $csv .= "\n" . $this->csvLine([lang('TenantMisc.financialTransactions')]);
            $csv .= $this->csvLine([
                lang('TenantMisc.reportDate'),
                lang('TenantMisc.recordedAt'),
                lang('TenantMisc.transaction'),
                lang('TenantMisc.patientPayee'),
                lang('TenantMisc.type'),
                lang('TenantMisc.source'),
                lang('TenantMisc.paymentMethodFinance'),
                lang('TenantMisc.totalAmount'),
            ]);
            
            foreach ($data['reports']['transactions'] as $transaction) {
                $subject = trim((string) ($transaction['patient_name'] ?? ''));
                if ($subject === '') {
                    $subject = (string) ($transaction['counterparty_name'] ?? '');
                }
                $csv .= $this->csvLine([
                    $transaction['report_date'] ?? '',
                    $transaction['created_at'] ?? '',
                    $transaction['transaction_id'] ?? '',
                    $subject,
                    $this->localizeFinanceTransactionType((string) ($transaction['transaction_type'] ?? '')),
                    $this->localizeFinanceSourceLabel((string) ($transaction['source_label'] ?? ''), (string) ($transaction['event_source'] ?? 'finance')),
                    $this->localizeFinancePaymentMethod((string) ($transaction['payment_method'] ?? '')),
                    $transaction['signed_amount_base'] ?? ($transaction['total_amount'] ?? ''),
                ]);
            }
        }

        if (isset($data['reports']['ar_aging_summary']) && is_array($data['reports']['ar_aging_summary'])) {
            $s = $data['reports']['ar_aging_summary'];
            $csv .= "\n" . $this->csvLine([lang('TenantMisc.arAgingDistribution')]);
            $csv .= $this->csvLine([lang('TenantMisc.bucket'), lang('TenantMisc.totalAmount')]);
            foreach ([
                'current' => lang('TenantMisc.current'),
                'days_1_30' => lang('TenantMisc.days1to30'),
                'days_31_60' => lang('TenantMisc.days31to60'),
                'days_61_90' => lang('TenantMisc.days61to90'),
                'days_91_plus' => lang('TenantMisc.days91Plus'),
                'total_balance_due' => lang('TenantMisc.totalAmount'),
            ] as $key => $label) {
                $csv .= $this->csvLine([$label, (float) ($s[$key] ?? 0)]);
            }
        }

        if (isset($data['reports']['ar_aging_detail']) && is_array($data['reports']['ar_aging_detail'])) {
            $csv .= "\n" . $this->csvLine([lang('TenantMisc.arAgingDetail')]);
            $csv .= $this->csvLine([
                lang('TenantMisc.invoice'),
                lang('TenantMisc.patient'),
                lang('TenantMisc.dueDate'),
                lang('TenantMisc.daysPastDue'),
                lang('TenantMisc.bucket'),
                lang('TenantMisc.balanceDue'),
            ]);
            foreach ($data['reports']['ar_aging_detail'] as $row) {
                $csv .= $this->csvLine([
                    $row['transaction_id'] ?? ('#' . ($row['id'] ?? '')),
                    trim((string) (($row['first_name'] ?? '') . ' ' . ($row['last_name'] ?? ''))),
                    $row['due_date'] ?? '',
                    $row['days_past_due'] ?? 0,
                    $this->localizeAgingBucket((string) ($row['aging_bucket'] ?? 'current')),
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
