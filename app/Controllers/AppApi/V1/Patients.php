<?php

namespace App\Controllers\AppApi\V1;

use App\Models\PatientModel;
use App\Services\ActivityLogger;
use CodeIgniter\Controller;
use CodeIgniter\HTTP\ResponseInterface;

class Patients extends Controller
{
    private PatientModel $patientModel;
    private ActivityLogger $activityLogger;

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

    private array $writeFields = [
        'first_name',
        'last_name',
        'email',
        'phone',
        'date_of_birth',
        'gender',
        'city',
        'medical_history',
        'allergies',
        'alert_has_allergies',
        'alert_allergies_details',
        'alert_taking_medications',
        'alert_medications_details',
        'alert_blood_thinners',
        'alert_blood_thinners_details',
        'alert_diabetes',
        'alert_diabetes_details',
        'alert_high_blood_pressure',
        'alert_blood_pressure_details',
        'alert_heart_condition',
        'alert_heart_condition_details',
        'alert_pregnant',
        'alert_pregnancy_details',
        'alert_anesthesia_problem',
        'alert_anesthesia_problem_details',
        'insurance_provider',
        'insurance_number',
        'notes',
    ];

    private array $booleanFields = [
        'alert_has_allergies',
        'alert_taking_medications',
        'alert_blood_thinners',
        'alert_diabetes',
        'alert_high_blood_pressure',
        'alert_heart_condition',
        'alert_pregnant',
        'alert_anesthesia_problem',
    ];

    public function __construct()
    {
        $this->patientModel = new PatientModel();
        $this->activityLogger = new ActivityLogger();
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

    public function store(): ResponseInterface
    {
        $clinicId = $this->activeClinicId();
        if ($clinicId <= 0) {
            return $this->error('tenant_context_missing', 'Select a clinic before continuing.', 422);
        }

        $input = $this->inputData();
        $payload = $this->patientPayload($input);
        $errors = $this->validatePatientPayload($payload, false, $clinicId);

        if ($errors !== []) {
            return $this->validationFailed($errors);
        }

        $payload['clinic_id'] = $clinicId;
        $payload['status'] = 'active';

        $userId = $this->currentUserId();
        if ($userId > 0) {
            $payload['created_by'] = $userId;
        }

        $model = new PatientModel();
        $model->skipValidation(true);

        $id = $model->insert($payload);
        if (! $id) {
            log_message('error', 'App API patient create failed: ' . json_encode($model->errors()));
            return $this->error('server_error', 'Unable to create patient.', 500);
        }

        $patient = $this->findPatient($clinicId, (int) $id);
        if (! $patient) {
            return $this->error('server_error', 'Unable to load created patient.', 500);
        }

        $this->logPatientActivity('create', (int) $id, "New patient '{$payload['first_name']} {$payload['last_name']}' registered successfully");

        return $this->ok([
            'patient' => $this->serializeDetail($patient),
        ], [
            'message' => 'Patient created.',
            'csrf' => $this->csrfPayload(),
        ], 201);
    }

    public function update($id = null): ResponseInterface
    {
        $clinicId = $this->activeClinicId();
        if ($clinicId <= 0) {
            return $this->error('tenant_context_missing', 'Select a clinic before continuing.', 422);
        }

        $patientId = (int) $id;
        if ($patientId <= 0) {
            return $this->notFound();
        }

        $patient = $this->findPatient($clinicId, $patientId);
        if (! $patient) {
            return $this->notFound();
        }

        $payload = $this->patientPayload($this->inputData());
        if ($payload === []) {
            return $this->validationFailed([
                'form' => ['Submit at least one editable patient field.'],
            ]);
        }

        $errors = $this->validatePatientPayload($payload, true, $clinicId, $patientId);
        if ($errors !== []) {
            return $this->validationFailed($errors);
        }

        $model = new PatientModel();
        $model->skipValidation(true);

        if (! $model->withoutTenantScope()->where('clinic_id', $clinicId)->where('id', $patientId)->set($payload)->update()) {
            log_message('error', 'App API patient update failed: ' . json_encode($model->errors()));
            return $this->error('server_error', 'Unable to update patient.', 500);
        }

        $updated = $this->findPatient($clinicId, $patientId);
        if (! $updated) {
            return $this->notFound();
        }

        $this->logPatientActivity('update', $patientId, "Patient '{$updated['first_name']} {$updated['last_name']}' information updated");

        return $this->ok([
            'patient' => $this->serializeDetail($updated),
        ], [
            'message' => 'Patient updated.',
            'csrf' => $this->csrfPayload(),
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
            'insurance' => [
                'provider' => $patient['insurance_provider'] ?? null,
                'number' => $patient['insurance_number'] ?? null,
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

    private function inputData(): array
    {
        if (str_contains(strtolower((string) $this->request->getHeaderLine('Content-Type')), 'application/json')) {
            try {
                $json = $this->request->getJSON(true);
                if (is_array($json)) {
                    return $json;
                }
            } catch (\Throwable $e) {
                // Fall back to CI's form/raw parsers below; feature tests and some clients
                // can send non-JSON bodies while still declaring JSON accept headers.
            }
        }

        $post = $this->request->getPost();
        if (is_array($post) && $post !== []) {
            return $post;
        }

        $raw = $this->request->getRawInput();
        if (is_array($raw) && $raw !== []) {
            return $raw;
        }

        $vars = $this->request->getVar();
        return is_array($vars) ? $vars : [];
    }

    private function patientPayload(array $input): array
    {
        $payload = [];

        foreach ($this->writeFields as $field) {
            if (! array_key_exists($field, $input)) {
                continue;
            }

            if (in_array($field, $this->booleanFields, true)) {
                $payload[$field] = $this->boolValue($input[$field]) ? 1 : 0;
                continue;
            }

            $value = is_string($input[$field]) ? trim($input[$field]) : $input[$field];
            $payload[$field] = $value === '' ? null : $value;
        }

        $this->clearDisabledAlertDetails($payload);

        return $payload;
    }

    private function clearDisabledAlertDetails(array &$payload): void
    {
        $detailsByFlag = [
            'alert_has_allergies' => 'alert_allergies_details',
            'alert_taking_medications' => 'alert_medications_details',
            'alert_blood_thinners' => 'alert_blood_thinners_details',
            'alert_diabetes' => 'alert_diabetes_details',
            'alert_high_blood_pressure' => 'alert_blood_pressure_details',
            'alert_heart_condition' => 'alert_heart_condition_details',
            'alert_pregnant' => 'alert_pregnancy_details',
            'alert_anesthesia_problem' => 'alert_anesthesia_problem_details',
        ];

        foreach ($detailsByFlag as $flag => $detailsField) {
            if (array_key_exists($flag, $payload) && (int) $payload[$flag] !== 1) {
                $payload[$detailsField] = null;
            }
        }
    }

    private function validatePatientPayload(array $payload, bool $partial, int $clinicId, ?int $patientId = null): array
    {
        $errors = [];
        $required = ['first_name', 'last_name', 'phone', 'date_of_birth', 'gender'];

        if (! $partial) {
            foreach ($required as $field) {
                if (! array_key_exists($field, $payload) || $payload[$field] === null) {
                    $errors[$field][] = $this->fieldLabel($field) . ' is required.';
                }
            }
        } else {
            foreach ($required as $field) {
                if (array_key_exists($field, $payload) && $payload[$field] === null) {
                    $errors[$field][] = $this->fieldLabel($field) . ' is required.';
                }
            }
        }

        $this->validateName($payload, $errors, 'first_name', 'First name');
        $this->validateName($payload, $errors, 'last_name', 'Last name');

        if (array_key_exists('email', $payload) && $payload['email'] !== null) {
            if (! filter_var($payload['email'], FILTER_VALIDATE_EMAIL)) {
                $errors['email'][] = 'Please enter a valid email address.';
            } elseif ($this->emailExistsInClinic((string) $payload['email'], $clinicId, $patientId)) {
                $errors['email'][] = 'This email is already registered to another patient in this clinic.';
            }
        }

        if (array_key_exists('phone', $payload) && $payload['phone'] !== null && ! preg_match('/^\d{9,15}$/', (string) $payload['phone'])) {
            $errors['phone'][] = 'Phone number must contain 9 to 15 digits.';
        }

        if (array_key_exists('date_of_birth', $payload) && $payload['date_of_birth'] !== null && strtotime((string) $payload['date_of_birth']) === false) {
            $errors['date_of_birth'][] = 'Enter a valid date of birth.';
        }

        if (array_key_exists('gender', $payload) && $payload['gender'] !== null && ! in_array($payload['gender'], ['male', 'female', 'other'], true)) {
            $errors['gender'][] = 'Please select a valid gender.';
        }

        foreach (['city', 'insurance_provider', 'insurance_number'] as $field) {
            if (array_key_exists($field, $payload) && is_string($payload[$field]) && mb_strlen($payload[$field]) > 100) {
                $errors[$field][] = $this->fieldLabel($field) . ' cannot exceed 100 characters.';
            }
        }

        return $errors;
    }

    private function validateName(array $payload, array &$errors, string $field, string $label): void
    {
        if (! array_key_exists($field, $payload) || $payload[$field] === null) {
            return;
        }

        $length = mb_strlen((string) $payload[$field]);
        if ($length < 2) {
            $errors[$field][] = $label . ' must be at least 2 characters long.';
        }
        if ($length > 50) {
            $errors[$field][] = $label . ' cannot exceed 50 characters.';
        }
    }

    private function emailExistsInClinic(string $email, int $clinicId, ?int $patientId): bool
    {
        return (new PatientModel())->emailExistsInClinic($email, $clinicId, $patientId);
    }

    private function findPatient(int $clinicId, int $patientId): ?array
    {
        $patient = (new PatientModel())
            ->withoutTenantScope()
            ->where('clinic_id', $clinicId)
            ->where('id', $patientId)
            ->first();

        return is_array($patient) ? $patient : null;
    }

    private function boolValue($value): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        if (is_numeric($value)) {
            return (int) $value === 1;
        }

        return in_array(strtolower(trim((string) $value)), ['1', 'true', 'yes', 'on'], true);
    }

    private function fieldLabel(string $field): string
    {
        return ucfirst(str_replace('_', ' ', $field));
    }

    private function currentUserId(): int
    {
        return (int) (session()->get('user_id') ?? 0);
    }

    private function logPatientActivity(string $action, int $patientId, string $description): void
    {
        try {
            $this->activityLogger->logPatientActivity($action, $patientId, $description);
        } catch (\Throwable $e) {
            log_message('error', 'App API patient activity logging failed: ' . $e->getMessage());
        }
    }

    private function csrfPayload(): array
    {
        $security = config('Security');

        return [
            'header' => $security->headerName,
            'name' => csrf_token(),
            'hash' => csrf_hash(),
        ];
    }

    private function validationFailed(array $fields): ResponseInterface
    {
        return $this->error('validation_failed', 'Please review the highlighted fields.', 422, [
            'fields' => $fields,
        ]);
    }

    private function ok(array $data, array $meta = [], int $status = 200): ResponseInterface
    {
        return $this->response
            ->setStatusCode($status)
            ->setJSON([
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
