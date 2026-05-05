<?php

namespace App\Controllers\AppApi\V1;

use App\Models\PatientModel;
use CodeIgniter\Controller;
use CodeIgniter\HTTP\ResponseInterface;

class Patients extends Controller
{
    private PatientModel $patientModel;

    private array $sortMap = [
        'created_at' => 'created_at',
        'updated_at' => 'updated_at',
        'first_name' => 'first_name',
        'last_name' => 'last_name',
        'patient_id' => 'patient_id',
        'patient_number' => 'patient_id',
        'date_of_birth' => 'date_of_birth',
        'status' => 'status',
    ];

    private array $allowedFilters = ['status', 'gender', 'city', 'has_alerts'];

    public function __construct()
    {
        $this->patientModel = new PatientModel();
    }

    public function index(): ResponseInterface
    {
        $clinicId = $this->activeClinicId();
        if ($clinicId <= 0) {
            return $this->error('tenant_context_missing', 'Select a clinic before continuing.', 422);
        }

        $params = $this->listParams();
        if (isset($params['error'])) {
            return $this->error('validation_failed', 'Please correct the highlighted fields.', 422, [
                'fields' => $params['error'],
            ]);
        }

        $countBuilder = $this->baseListBuilder($clinicId);
        $this->applySearchAndFilters($countBuilder, $params['search'], $params['filters']);
        $total = (int) $countBuilder->countAllResults();

        $totalPages = $total > 0 ? (int) ceil($total / $params['per_page']) : 0;
        $offset = ($params['page'] - 1) * $params['per_page'];

        $builder = $this->baseListBuilder($clinicId);
        $this->applySearchAndFilters($builder, $params['search'], $params['filters']);
        $patients = $builder
            ->orderBy($this->sortMap[$params['sort']], $params['direction'])
            ->findAll($params['per_page'], $offset);

        return $this->ok([
            'patients' => array_map(fn (array $patient): array => $this->serializeListItem($patient), $patients),
        ], [
            'pagination' => [
                'page' => $params['page'],
                'per_page' => $params['per_page'],
                'total' => $total,
                'total_pages' => $totalPages,
            ],
            'sort' => [
                'field' => $params['sort'],
                'direction' => $params['direction'],
            ],
            'filters' => $params['filters'],
        ]);
    }

    public function show($id = null): ResponseInterface
    {
        $clinicId = $this->activeClinicId();
        if ($clinicId <= 0) {
            return $this->error('tenant_context_missing', 'Select a clinic before continuing.', 422);
        }

        $patientId = (int) $id;
        if ($patientId <= 0) {
            return $this->notFound();
        }

        $patient = $this->patientModel
            ->withoutTenantScope()
            ->where('clinic_id', $clinicId)
            ->where('id', $patientId)
            ->first();

        if (! $patient) {
            return $this->notFound();
        }

        return $this->ok([
            'patient' => $this->serializeDetail($patient),
        ]);
    }

    private function listParams(): array
    {
        $errors = [];
        $page = $this->positiveIntParam('page', 1, $errors);
        $perPage = $this->positiveIntParam('per_page', 25, $errors);
        $perPage = min($perPage, 100);

        $search = trim((string) ($this->request->getGet('search') ?? ''));
        if (mb_strlen($search) > 100) {
            $errors['search'] = ['Search cannot exceed 100 characters.'];
        }

        $sort = (string) ($this->request->getGet('sort') ?? 'created_at');
        if (! array_key_exists($sort, $this->sortMap)) {
            $errors['sort'] = ['Unsupported sort field.'];
        }

        $direction = strtolower((string) ($this->request->getGet('direction') ?? 'desc'));
        if (! in_array($direction, ['asc', 'desc'], true)) {
            $errors['direction'] = ['Direction must be asc or desc.'];
        }

        $filters = $this->request->getGet('filter') ?? [];
        if ($filters === null || $filters === '') {
            $filters = [];
        }
        if (! is_array($filters)) {
            $errors['filter'] = ['Filters must use bracket notation.'];
            $filters = [];
        }

        foreach (array_keys($filters) as $key) {
            if (! in_array((string) $key, $this->allowedFilters, true)) {
                $errors['filter.' . $key] = ['Unsupported filter.'];
            }
        }

        $filters = array_intersect_key($filters, array_flip($this->allowedFilters));
        $filters = array_filter($filters, static fn ($value): bool => $value !== null && $value !== '');

        $this->validateFilterValues($filters, $errors);

        if ($errors !== []) {
            return ['error' => $errors];
        }

        return [
            'page' => $page,
            'per_page' => $perPage,
            'search' => $search,
            'sort' => $sort,
            'direction' => $direction,
            'filters' => $filters,
        ];
    }

    private function positiveIntParam(string $key, int $default, array &$errors): int
    {
        $value = $this->request->getGet($key);
        if ($value === null || $value === '') {
            return $default;
        }

        if (filter_var($value, FILTER_VALIDATE_INT) === false || (int) $value < 1) {
            $errors[$key] = [ucfirst($key) . ' must be a positive integer.'];
            return $default;
        }

        return (int) $value;
    }

    private function validateFilterValues(array $filters, array &$errors): void
    {
        if (isset($filters['gender']) && ! in_array($filters['gender'], ['male', 'female', 'other'], true)) {
            $errors['filter.gender'] = ['Unsupported gender filter.'];
        }

        if (isset($filters['status']) && mb_strlen((string) $filters['status']) > 50) {
            $errors['filter.status'] = ['Status filter is too long.'];
        }

        if (isset($filters['city']) && mb_strlen((string) $filters['city']) > 100) {
            $errors['filter.city'] = ['City filter is too long.'];
        }

        if (isset($filters['has_alerts']) && ! in_array(strtolower((string) $filters['has_alerts']), ['1', '0', 'true', 'false'], true)) {
            $errors['filter.has_alerts'] = ['has_alerts must be true or false.'];
        }
    }

    private function baseListBuilder(int $clinicId)
    {
        return $this->patientModel
            ->withoutTenantScope()
            ->select('id, patient_id, first_name, last_name, email, phone, date_of_birth, gender, city, status, alert_has_allergies, alert_taking_medications, alert_blood_thinners, alert_diabetes, alert_high_blood_pressure, alert_heart_condition, alert_pregnant, alert_anesthesia_problem, created_at, updated_at')
            ->where('clinic_id', $clinicId);
    }

    private function applySearchAndFilters($builder, string $search, array $filters): void
    {
        if ($search !== '') {
            $builder->groupStart()
                ->like('first_name', $search)
                ->orLike('last_name', $search)
                ->orLike("CONCAT(first_name, ' ', last_name)", $search)
                ->orLike('phone', $search)
                ->orLike('email', $search)
                ->orLike('patient_id', $search)
                ->orLike('city', $search)
                ->groupEnd();
        }

        if (isset($filters['status'])) {
            $builder->where('status', $filters['status']);
        }

        if (isset($filters['gender'])) {
            $builder->where('gender', $filters['gender']);
        }

        if (isset($filters['city'])) {
            $builder->where('city', $filters['city']);
        }

        if (array_key_exists('has_alerts', $filters)) {
            $hasAlerts = in_array(strtolower((string) $filters['has_alerts']), ['1', 'true'], true);
            $alertFields = [
                'alert_has_allergies',
                'alert_taking_medications',
                'alert_blood_thinners',
                'alert_diabetes',
                'alert_high_blood_pressure',
                'alert_heart_condition',
                'alert_pregnant',
                'alert_anesthesia_problem',
            ];

            $method = $hasAlerts ? 'where' : 'where';
            $builder->groupStart();
            foreach ($alertFields as $index => $field) {
                if ($hasAlerts) {
                    $index === 0 ? $builder->where($field, 1) : $builder->orWhere($field, 1);
                } else {
                    $builder->{$method}($field, 0);
                }
            }
            $builder->groupEnd();
        }
    }

    private function serializeListItem(array $patient): array
    {
        return [
            'id' => (int) $patient['id'],
            'patient_number' => (string) ($patient['patient_id'] ?? ''),
            'display_name' => $this->displayName($patient),
            'contact' => [
                'phone' => $patient['phone'] ?? null,
                'email' => $patient['email'] ?? null,
            ],
            'demographics' => [
                'gender' => $patient['gender'] ?? null,
                'date_of_birth' => $patient['date_of_birth'] ?? null,
                'age' => $this->age($patient['date_of_birth'] ?? null),
                'city' => $patient['city'] ?? null,
            ],
            'status' => (string) ($patient['status'] ?? 'active'),
            'medical' => [
                'has_alerts' => $this->alertFlagCount($patient) > 0,
                'alert_count' => $this->alertFlagCount($patient),
            ],
            'timestamps' => [
                'created_at' => $patient['created_at'] ?? null,
                'updated_at' => $patient['updated_at'] ?? null,
            ],
        ];
    }

    private function serializeDetail(array $patient): array
    {
        return [
            'id' => (int) $patient['id'],
            'patient_number' => (string) ($patient['patient_id'] ?? ''),
            'first_name' => (string) ($patient['first_name'] ?? ''),
            'last_name' => (string) ($patient['last_name'] ?? ''),
            'display_name' => $this->displayName($patient),
            'phone' => $patient['phone'] ?? null,
            'email' => $patient['email'] ?? null,
            'date_of_birth' => $patient['date_of_birth'] ?? null,
            'gender' => $patient['gender'] ?? null,
            'city' => $patient['city'] ?? null,
            'status' => (string) ($patient['status'] ?? 'active'),
            'medical' => [
                'medical_history' => $patient['medical_history'] ?? null,
                'allergies' => $patient['allergies'] ?? null,
                'alerts' => $this->alerts($patient),
            ],
            'notes' => $patient['notes'] ?? null,
            'timestamps' => [
                'created_at' => $patient['created_at'] ?? null,
                'updated_at' => $patient['updated_at'] ?? null,
            ],
        ];
    }

    private function alerts(array $patient): array
    {
        $definitions = [
            'allergies' => ['alert_has_allergies', 'Allergies', 'alert_allergies_details'],
            'medications' => ['alert_taking_medications', 'Taking medications', 'alert_medications_details'],
            'blood_thinners' => ['alert_blood_thinners', 'Blood thinners', 'alert_blood_thinners_details'],
            'diabetes' => ['alert_diabetes', 'Diabetes', 'alert_diabetes_details'],
            'high_blood_pressure' => ['alert_high_blood_pressure', 'High blood pressure', 'alert_blood_pressure_details'],
            'heart_condition' => ['alert_heart_condition', 'Heart condition', 'alert_heart_condition_details'],
            'pregnant' => ['alert_pregnant', 'Pregnancy', 'alert_pregnancy_details'],
            'anesthesia_problem' => ['alert_anesthesia_problem', 'Anesthesia issue', 'alert_anesthesia_problem_details'],
        ];

        $alerts = [];
        foreach ($definitions as $code => [$flag, $label, $detailsField]) {
            if ((int) ($patient[$flag] ?? 0) !== 1) {
                continue;
            }

            $alerts[] = [
                'code' => $code,
                'label' => $label,
                'details' => $patient[$detailsField] ?? null,
            ];
        }

        return $alerts;
    }

    private function alertCount(array $patient): int
    {
        return count($this->alerts($patient));
    }

    private function alertFlagCount(array $patient): int
    {
        $fields = [
            'alert_has_allergies',
            'alert_taking_medications',
            'alert_blood_thinners',
            'alert_diabetes',
            'alert_high_blood_pressure',
            'alert_heart_condition',
            'alert_pregnant',
            'alert_anesthesia_problem',
        ];

        $count = 0;
        foreach ($fields as $field) {
            if ((int) ($patient[$field] ?? 0) === 1) {
                $count++;
            }
        }

        return $count;
    }

    private function displayName(array $patient): string
    {
        return trim((string) ($patient['first_name'] ?? '') . ' ' . (string) ($patient['last_name'] ?? ''));
    }

    private function age(?string $dateOfBirth): ?int
    {
        if (! $dateOfBirth || $dateOfBirth === '0000-00-00') {
            return null;
        }

        try {
            return (new \DateTimeImmutable($dateOfBirth))->diff(new \DateTimeImmutable('today'))->y;
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function activeClinicId(): int
    {
        return (int) (session()->get('active_clinic_id') ?? 0);
    }

    private function ok(array $data, array $meta = []): ResponseInterface
    {
        return $this->response->setJSON([
            'data' => $data,
            'meta' => $meta + ['request_id' => $this->requestId()],
        ]);
    }

    private function notFound(): ResponseInterface
    {
        return $this->error('not_found', 'Patient not found.', 404);
    }

    private function error(string $code, string $message, int $status, array $details = []): ResponseInterface
    {
        return $this->response
            ->setStatusCode($status)
            ->setJSON([
                'error' => [
                    'code' => $code,
                    'message' => $message,
                    'details' => $details,
                ],
                'meta' => [
                    'request_id' => $this->requestId(),
                ],
            ]);
    }

    private function requestId(): string
    {
        return 'req_' . bin2hex(random_bytes(8));
    }
}
