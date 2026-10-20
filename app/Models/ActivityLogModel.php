<?php

namespace App\Models;

use CodeIgniter\Model;

class ActivityLogModel extends TenantAwareModel
{
    protected $table = 'activity_logs';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = [
        'clinic_id',
        'user_id',
        'action',
        'entity_type',
        'entity_id',
        'description',
        'ip_address',
        'user_agent',
        'metadata',
        'created_at'
    ];

    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $deletedField = 'deleted_at';

    protected $validationRules = [
        'user_id' => 'required|integer',
        'action' => 'required|string|max_length[50]',
        'entity_type' => 'required|string|max_length[50]',
        'description' => 'required|string|max_length[255]'
    ];

    protected $validationMessages = [];
    protected $skipValidation = false;
    protected $cleanValidationRules = true;

    protected $beforeInsert = ['setClinicId'];
    protected $afterInsert = [];
    protected $beforeUpdate = [];
    protected $afterUpdate = [];
    protected $beforeDelete = [];
    protected $afterDelete = [];
    protected $beforeFind = [];
    protected $afterFind = [];

    /**
     * Log an activity
     */
    public function logActivity($userId, $action, $entityType, $entityId = null, $description = '', $metadata = null)
    {
        $data = [
            'user_id' => $userId,
            'action' => $action,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'description' => $description,
            'ip_address' => $this->getClientIP(),
            'user_agent' => $this->getUserAgent(),
            'metadata' => $metadata ? json_encode($metadata) : null,
            'created_at' => date('Y-m-d H:i:s')
        ];

        return $this->insert($data);
    }

    /**
     * Get activities by clinic
     */
    public function getActivitiesByClinic($clinicId, $limit = 50, $offset = 0, $entityType = null, $action = null, $userId = null)
    {
        $limit = (int) $limit;
        $offset = (int) $offset;
        if ($limit <= 0) {
            $limit = 50;
        }
        if ($offset < 0) {
            $offset = 0;
        }

        $builder = $this->db->table('activity_logs al')
            ->select('al.*, u.first_name, u.last_name, u.email')
            ->join('users u', 'u.id = al.user_id', 'left')
            ->where('al.clinic_id', $clinicId)
            ->orderBy('al.created_at', 'DESC')
            ->limit($limit, $offset);

        if ($entityType) {
            $builder->where('al.entity_type', $entityType);
        }
        if ($action) {
            $builder->where('al.action', $action);
        }
        if ($userId) {
            $builder->where('al.user_id', $userId);
        }

        return $builder->get()->getResultArray();
    }

    /**
     * Count activities by clinic
     */
    public function countActivitiesByClinic($clinicId, $entityType = null, $action = null, $userId = null)
    {
        $builder = $this->db->table('activity_logs al')
            ->where('al.clinic_id', $clinicId);

        if ($entityType) {
            $builder->where('al.entity_type', $entityType);
        }
        if ($action) {
            $builder->where('al.action', $action);
        }
        if ($userId) {
            $builder->where('al.user_id', $userId);
        }

        return $builder->countAllResults();
    }

    /**
     * Get filters by clinic
     */
    public function getFiltersByClinic($clinicId)
    {
        $entityTypes = $this->db->table('activity_logs')
            ->select('entity_type')
            ->where('clinic_id', $clinicId)
            ->distinct()
            ->get()
            ->getResultArray();

        $actions = $this->db->table('activity_logs')
            ->select('action')
            ->where('clinic_id', $clinicId)
            ->distinct()
            ->get()
            ->getResultArray();

        return [
            'entity_types' => array_column($entityTypes, 'entity_type'),
            'actions' => array_column($actions, 'action')
        ];
    }

    /**
     * Get recent activities for notifications
     */
    public function getRecentActivities($limit = 20, $userId = null, $clinicId = null)
    {
        if (!$clinicId) {
            return [];
        }

        $builder = $this->db->table('activity_logs al')
            ->select('al.*, u.first_name, u.last_name, u.email')
            ->join('users u', 'u.id = al.user_id', 'left')
            ->where('al.clinic_id', $clinicId)
            ->orderBy('al.created_at', 'DESC')
            ->limit($limit);

        if ($userId) {
            $builder->where('al.user_id', $userId);
        }

        $activities = $builder->get()->getResultArray();

        // Format activities for notifications
        return array_map([$this, 'formatActivityForNotification'], $activities);
    }

    /**
     * Format activity for notification display
     */
    private function formatActivityForNotification($activity)
    {
        $actionConfig = $this->getActionConfig($activity['action'], $activity['entity_type']);
        
        $isRead = $this->isActivityRead($activity['id']);
        $userName = trim(((string) ($activity['first_name'] ?? '')) . ' ' . ((string) ($activity['last_name'] ?? '')));
        
        return [
            'id' => $activity['id'],
            'title' => $actionConfig['title'],
            'message' => $this->buildMessage($activity, $actionConfig),
            'type' => $activity['entity_type'],
            'type_label' => $this->getEntityTypeLabel((string) ($activity['entity_type'] ?? '')),
            'entity_type' => $activity['entity_type'],
            'entity_id' => $activity['entity_id'],
            'entity_url' => $this->resolveNotificationUrl((string) ($activity['entity_type'] ?? ''), $activity['entity_id'] ?? null),
            'is_read' => $isRead,
            'created_at' => $activity['created_at'],
            'icon' => $actionConfig['icon'],
            'color' => $actionConfig['color'],
            'user_name' => $userName,
        ];
    }

    /**
     * Check if an activity has been read by the current user
     */
    private function isActivityRead($activityId)
    {
        $userId = session()->get('user_id');
        if (!$userId) {
            return false;
        }

        $readNotifications = session()->get('read_notifications') ?? [];
        if (in_array((int) $activityId, $readNotifications, true)) {
            return true;
        }

        $lastReadTime = session()->get('last_notification_read_time');
        if (!$lastReadTime) {
            return false;
        }

        $activity = $this->find($activityId);
        if (!$activity) {
            return false;
        }

        $activityTime = strtotime($activity['created_at']);
        return $activityTime <= (int) $lastReadTime;
    }

    /**
     * Get action configuration
     */
    private function getActionConfig($action, $entityType)
    {
        $configs = [
            'patient' => [
                'create' => [
                    'title' => lang('UI.notificationTitlePatientCreate'),
                    'icon' => 'fas fa-user-plus',
                    'color' => 'green'
                ],
                'update' => [
                    'title' => lang('UI.notificationTitlePatientUpdate'),
                    'icon' => 'fas fa-user-edit',
                    'color' => 'blue'
                ],
                'delete' => [
                    'title' => lang('UI.notificationTitlePatientDelete'),
                    'icon' => 'fas fa-user-times',
                    'color' => 'red'
                ]
            ],
            'appointment' => [
                'create' => [
                    'title' => lang('UI.notificationTitleAppointmentCreate'),
                    'icon' => 'fas fa-calendar-plus',
                    'color' => 'blue'
                ],
                'update' => [
                    'title' => lang('UI.notificationTitleAppointmentUpdate'),
                    'icon' => 'fas fa-calendar-check',
                    'color' => 'purple'
                ],
                'delete' => [
                    'title' => lang('UI.notificationTitleAppointmentDelete'),
                    'icon' => 'fas fa-calendar-times',
                    'color' => 'red'
                ],
                'cancel' => [
                    'title' => lang('UI.notificationTitleAppointmentCancel'),
                    'icon' => 'fas fa-calendar-times',
                    'color' => 'red'
                ]
            ],
            'examination' => [
                'create' => [
                    'title' => lang('UI.notificationTitleExaminationCreate'),
                    'icon' => 'fas fa-stethoscope',
                    'color' => 'green'
                ],
                'update' => [
                    'title' => lang('UI.notificationTitleExaminationUpdate'),
                    'icon' => 'fas fa-clipboard-check',
                    'color' => 'blue'
                ]
            ],
            'treatment' => [
                'create' => [
                    'title' => lang('UI.notificationTitleTreatmentCreate'),
                    'icon' => 'fas fa-tooth',
                    'color' => 'green'
                ],
                'update' => [
                    'title' => lang('UI.notificationTitleTreatmentUpdate'),
                    'icon' => 'fas fa-edit',
                    'color' => 'blue'
                ],
                'complete' => [
                    'title' => lang('UI.notificationTitleTreatmentComplete'),
                    'icon' => 'fas fa-check-circle',
                    'color' => 'green'
                ]
            ],
            'inventory' => [
                'create' => [
                    'title' => lang('UI.notificationTitleInventoryCreate'),
                    'icon' => 'fas fa-box',
                    'color' => 'green'
                ],
                'update' => [
                    'title' => lang('UI.notificationTitleInventoryUpdate'),
                    'icon' => 'fas fa-edit',
                    'color' => 'blue'
                ],
                'low_stock' => [
                    'title' => lang('UI.notificationTitleInventoryLowStock'),
                    'icon' => 'fas fa-exclamation-triangle',
                    'color' => 'yellow'
                ]
            ],
            'finance' => [
                'create' => [
                    'title' => lang('UI.notificationTitleFinanceCreate'),
                    'icon' => 'fas fa-dollar-sign',
                    'color' => 'green'
                ],
                'update' => [
                    'title' => lang('UI.notificationTitleFinanceUpdate'),
                    'icon' => 'fas fa-edit',
                    'color' => 'blue'
                ],
                'delete' => [
                    'title' => lang('UI.notificationTitleFinanceDelete'),
                    'icon' => 'fas fa-trash-alt',
                    'color' => 'red'
                ]
            ],
            'auth' => [
                'password_reset_failed' => [
                    'title' => lang('UI.notificationTitleAuthPasswordResetFailed'),
                    'icon' => 'fas fa-unlock-alt',
                    'color' => 'red'
                ]
            ]
        ];

        return $configs[$entityType][$action] ?? [
            'title' => lang('UI.notificationTitleSystemActivity'),
            'icon' => 'fas fa-cog',
            'color' => 'gray'
        ];
    }

    /**
     * Build notification message
     */
    private function buildMessage($activity, $actionConfig)
    {
        $userName = trim(((string) ($activity['first_name'] ?? '')) . ' ' . ((string) ($activity['last_name'] ?? '')));
        $displayName = $userName !== '' ? $userName : lang('UI.notificationUnknownUser');

        $messageKeys = [
            'patient.create' => 'notificationMessagePatientCreate',
            'patient.update' => 'notificationMessagePatientUpdate',
            'patient.delete' => 'notificationMessagePatientDelete',
            'appointment.create' => 'notificationMessageAppointmentCreate',
            'appointment.update' => 'notificationMessageAppointmentUpdate',
            'appointment.delete' => 'notificationMessageAppointmentDelete',
            'appointment.cancel' => 'notificationMessageAppointmentCancel',
            'examination.create' => 'notificationMessageExaminationCreate',
            'examination.update' => 'notificationMessageExaminationUpdate',
            'treatment.create' => 'notificationMessageTreatmentCreate',
            'treatment.update' => 'notificationMessageTreatmentUpdate',
            'treatment.complete' => 'notificationMessageTreatmentComplete',
            'inventory.create' => 'notificationMessageInventoryCreate',
            'inventory.update' => 'notificationMessageInventoryUpdate',
            'inventory.low_stock' => 'notificationMessageInventoryLowStock',
            'finance.create' => 'notificationMessageFinanceCreate',
            'finance.update' => 'notificationMessageFinanceUpdate',
            'finance.delete' => 'notificationMessageFinanceDelete',
            'auth.password_reset_failed' => 'notificationMessageAuthPasswordResetFailed',
        ];

        $messageKey = $messageKeys[$activity['entity_type'] . '.' . $activity['action']] ?? 'notificationMessageSystemActivity';
        $fallbackMessage = lang('UI.' . $messageKey, ['name' => $displayName]);
        $description = trim((string) ($activity['description'] ?? ''));
        $structuredMessage = $this->buildStructuredMessage($activity, $displayName);

        if ($structuredMessage !== null) {
            return $structuredMessage;
        }

        if ($description !== '' && $this->shouldUseActivityDescriptionForLocale($description)) {
            return $description;
        }

        return $fallbackMessage;
    }

    private function buildStructuredMessage(array $activity, string $displayName): ?string
    {
        $entityType = (string) ($activity['entity_type'] ?? '');
        $action = (string) ($activity['action'] ?? '');

        if ($entityType === 'appointment') {
            return $this->buildAppointmentNotificationMessage($activity, $displayName, $action);
        }

        if ($entityType === 'patient') {
            return $this->buildPatientNotificationMessage($activity, $displayName, $action);
        }

        if ($entityType === 'examination') {
            return $this->buildExaminationNotificationMessage($activity, $displayName, $action);
        }

        if ($entityType === 'treatment') {
            return $this->buildTreatmentNotificationMessage($activity, $displayName, $action);
        }

        if ($entityType === 'finance') {
            return $this->buildFinanceNotificationMessage($activity, $displayName, $action);
        }

        return null;
    }

    private function buildAppointmentNotificationMessage(array $activity, string $displayName, string $action): ?string
    {
        $appointmentId = (int) ($activity['entity_id'] ?? 0);
        $clinicId = (int) ($activity['clinic_id'] ?? 0);
        if ($appointmentId <= 0 || $clinicId <= 0) {
            return null;
        }

        $appointment = $this->db->table('appointments a')
            ->select('a.appointment_type, p.first_name, p.last_name')
            ->join('patients p', 'p.id = a.patient_id', 'left')
            ->where('a.clinic_id', $clinicId)
            ->where('a.id', $appointmentId)
            ->get()
            ->getRowArray();

        if (!$appointment) {
            return null;
        }

        $appointmentType = $this->getAppointmentTypeLabel((string) ($appointment['appointment_type'] ?? ''));
        $patientName = trim(((string) ($appointment['first_name'] ?? '')) . ' ' . ((string) ($appointment['last_name'] ?? '')));
        if ($patientName === '') {
            $patientName = lang('UI.notificationUnknownPatient');
        }

        $messageKeys = [
            'create' => 'notificationMessageAppointmentCreateDetailed',
            'update' => 'notificationMessageAppointmentUpdateDetailed',
            'delete' => 'notificationMessageAppointmentDeleteDetailed',
            'cancel' => 'notificationMessageAppointmentCancelDetailed',
            'reschedule' => 'notificationMessageAppointmentUpdateDetailed',
        ];

        $messageKey = $messageKeys[$action] ?? null;
        if ($messageKey === null) {
            return null;
        }

        return lang('UI.' . $messageKey, [
            'name' => $displayName,
            'type' => $appointmentType,
            'patient' => $patientName,
        ]);
    }

    private function buildPatientNotificationMessage(array $activity, string $displayName, string $action): ?string
    {
        $patientId = (int) ($activity['entity_id'] ?? 0);
        $clinicId = (int) ($activity['clinic_id'] ?? 0);
        if ($patientId <= 0 || $clinicId <= 0) {
            return null;
        }

        $patient = $this->db->table('patients')
            ->select('first_name, last_name')
            ->where('clinic_id', $clinicId)
            ->where('id', $patientId)
            ->get()
            ->getRowArray();

        if (!$patient) {
            return null;
        }

        $patientName = trim(((string) ($patient['first_name'] ?? '')) . ' ' . ((string) ($patient['last_name'] ?? '')));
        if ($patientName === '') {
            $patientName = lang('UI.notificationUnknownPatient');
        }

        $messageKeys = [
            'create' => 'notificationMessagePatientCreateDetailed',
            'update' => 'notificationMessagePatientUpdateDetailed',
            'delete' => 'notificationMessagePatientDeleteDetailed',
        ];

        $messageKey = $messageKeys[$action] ?? null;
        if ($messageKey === null) {
            return null;
        }

        return lang('UI.' . $messageKey, [
            'name' => $displayName,
            'patient' => $patientName,
        ]);
    }

    private function buildExaminationNotificationMessage(array $activity, string $displayName, string $action): ?string
    {
        $examinationId = (int) ($activity['entity_id'] ?? 0);
        $clinicId = (int) ($activity['clinic_id'] ?? 0);
        if ($examinationId <= 0 || $clinicId <= 0) {
            return null;
        }

        $examination = $this->db->table('examinations e')
            ->select('e.examination_type, p.first_name, p.last_name')
            ->join('patients p', 'p.id = e.patient_id', 'left')
            ->where('e.clinic_id', $clinicId)
            ->where('e.id', $examinationId)
            ->get()
            ->getRowArray();

        if (!$examination) {
            return null;
        }

        $examinationType = $this->getExaminationTypeLabel((string) ($examination['examination_type'] ?? ''));
        $patientName = trim(((string) ($examination['first_name'] ?? '')) . ' ' . ((string) ($examination['last_name'] ?? '')));
        if ($patientName === '') {
            $patientName = lang('UI.notificationUnknownPatient');
        }

        $messageKeys = [
            'create' => 'notificationMessageExaminationCreateDetailed',
            'update' => 'notificationMessageExaminationUpdateDetailed',
        ];

        $messageKey = $messageKeys[$action] ?? null;
        if ($messageKey === null) {
            return null;
        }

        return lang('UI.' . $messageKey, [
            'name' => $displayName,
            'type' => $examinationType,
            'patient' => $patientName,
        ]);
    }

    private function buildTreatmentNotificationMessage(array $activity, string $displayName, string $action): ?string
    {
        $treatmentId = (int) ($activity['entity_id'] ?? 0);
        $clinicId = (int) ($activity['clinic_id'] ?? 0);
        if ($treatmentId <= 0 || $clinicId <= 0) {
            return null;
        }

        $treatment = $this->db->table('treatments t')
            ->select('t.treatment_name, t.treatment_type, p.first_name, p.last_name')
            ->join('patients p', 'p.id = t.patient_id AND p.clinic_id = t.clinic_id', 'left')
            ->where('t.clinic_id', $clinicId)
            ->where('t.id', $treatmentId)
            ->get()
            ->getRowArray();

        if (!$treatment) {
            return null;
        }

        $treatmentLabel = trim((string) ($treatment['treatment_name'] ?? ''));
        if ($treatmentLabel === '') {
            $treatmentLabel = ucwords(str_replace('_', ' ', (string) ($treatment['treatment_type'] ?? 'treatment')));
        }

        $patientName = trim(((string) ($treatment['first_name'] ?? '')) . ' ' . ((string) ($treatment['last_name'] ?? '')));
        if ($patientName === '') {
            $patientName = lang('UI.notificationUnknownPatient');
        }

        $messageKeys = [
            'create' => 'notificationMessageTreatmentCreateDetailed',
            'update' => 'notificationMessageTreatmentUpdateDetailed',
            'delete' => 'notificationMessageTreatmentDeleteDetailed',
            'complete' => 'notificationMessageTreatmentCompleteDetailed',
        ];

        $messageKey = $messageKeys[$action] ?? null;
        if ($messageKey === null) {
            return null;
        }

        return lang('UI.' . $messageKey, [
            'name' => $displayName,
            'type' => $treatmentLabel,
            'patient' => $patientName,
        ]);
    }

    private function buildFinanceNotificationMessage(array $activity, string $displayName, string $action): ?string
    {
        $financeId = (int) ($activity['entity_id'] ?? 0);
        $clinicId = (int) ($activity['clinic_id'] ?? 0);
        if ($financeId <= 0 || $clinicId <= 0) {
            return null;
        }

        $finance = $this->db->table('finances f')
            ->select('f.transaction_type, f.counterparty_name, p.first_name, p.last_name')
            ->join('patients p', 'p.id = f.patient_id AND p.clinic_id = f.clinic_id', 'left')
            ->where('f.clinic_id', $clinicId)
            ->where('f.id', $financeId)
            ->get()
            ->getRowArray();

        if (!$finance) {
            return null;
        }

        $transactionLabel = $this->getFinanceTransactionLabel((string) ($finance['transaction_type'] ?? ''));
        $targetName = trim(((string) ($finance['first_name'] ?? '')) . ' ' . ((string) ($finance['last_name'] ?? '')));
        if ($targetName === '') {
            $targetName = trim((string) ($finance['counterparty_name'] ?? ''));
        }
        if ($targetName === '') {
            $targetName = lang('UI.notificationUnknownRecordTarget');
        }

        $messageKeys = [
            'create' => 'notificationMessageFinanceCreateDetailed',
            'update' => 'notificationMessageFinanceUpdateDetailed',
            'delete' => 'notificationMessageFinanceDeleteDetailed',
            'refund' => 'notificationMessageFinanceUpdateDetailed',
        ];

        $messageKey = $messageKeys[$action] ?? null;
        if ($messageKey === null) {
            return null;
        }

        return lang('UI.' . $messageKey, [
            'name' => $displayName,
            'transaction' => $transactionLabel,
            'target' => $targetName,
        ]);
    }

    private function getEntityTypeLabel(string $entityType): string
    {
        $keyMap = [
            'patient' => 'notificationTypePatient',
            'appointment' => 'notificationTypeAppointment',
            'examination' => 'notificationTypeExamination',
            'treatment' => 'notificationTypeTreatment',
            'inventory' => 'notificationTypeInventory',
            'finance' => 'notificationTypeFinance',
            'auth' => 'notificationTypeSystem',
        ];

        return lang('UI.' . ($keyMap[$entityType] ?? 'notificationTypeSystem'));
    }

    private function getAppointmentTypeLabel(string $appointmentType): string
    {
        $typeKeyMap = [
            'consultation' => 'appointmentTypeConsultation',
            'treatment' => 'appointmentTypeTreatment',
            'follow_up' => 'appointmentTypeFollowUp',
            'emergency' => 'appointmentTypeEmergency',
            'cleaning' => 'appointmentTypeCleaning',
            'checkup' => 'appointmentTypeCheckup',
        ];

        $key = $typeKeyMap[$appointmentType] ?? null;
        if ($key === null) {
            return ucwords(str_replace('_', ' ', $appointmentType));
        }

        return lang('UI.' . $key);
    }

    private function getFinanceTransactionLabel(string $transactionType): string
    {
        $candidate = lang('Finance.' . $transactionType);
        if (is_string($candidate) && $candidate !== '' && $candidate !== 'Finance.' . $transactionType) {
            return $candidate;
        }

        return ucwords(str_replace('_', ' ', $transactionType));
    }

    private function getExaminationTypeLabel(string $examinationType): string
    {
        $candidate = lang('Examination.type_' . $examinationType);
        if (is_string($candidate) && $candidate !== '' && $candidate !== 'Examination.type_' . $examinationType) {
            return $candidate;
        }

        return ucwords(str_replace('_', ' ', $examinationType));
    }

    private function resolveNotificationUrl(string $entityType, $entityId): string
    {
        $id = (int) $entityId;
        if ($id <= 0) {
            return base_url('notifications');
        }

        return match ($entityType) {
            'patient' => base_url('patient/' . $id),
            'appointment' => base_url('appointment/' . $id),
            'treatment' => base_url('treatment/' . $id),
            'examination' => base_url('examination/' . $id),
            'prescription' => base_url('prescription/' . $id),
            'finance' => base_url('finance/' . $id),
            'inventory' => base_url('inventory/' . $id),
            default => base_url('notifications'),
        };
    }

    private function shouldUseActivityDescriptionForLocale(string $description): bool
    {
        $locale = service('request')->getLocale();
        $isArabic = preg_match('/[\x{0600}-\x{06FF}]/u', $description) === 1;

        if ($locale === 'ar') {
            return $isArabic;
        }

        return !$isArabic;
    }

    /**
     * Get client IP address
     */
    private function getClientIP()
    {
        $ipKeys = ['HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR'];
        foreach ($ipKeys as $key) {
            if (array_key_exists($key, $_SERVER) === true) {
                foreach (explode(',', $_SERVER[$key]) as $ip) {
                    $ip = trim($ip);
                    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false) {
                        return $ip;
                    }
                }
            }
        }
        return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }

    /**
     * Get user agent
     */
    private function getUserAgent()
    {
        return $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
    }

    /**
     * Get activities for specific entity
     */
    public function getEntityActivities($entityType, $entityId, $limit = 20, $clinicId = null)
    {
        if (!$clinicId) {
            return [];
        }

        return $this->db->table('activity_logs al')
            ->select('al.*, u.first_name, u.last_name')
            ->join('users u', 'u.id = al.user_id', 'left')
            ->where('al.clinic_id', $clinicId)
            ->where('al.entity_type', $entityType)
            ->where('al.entity_id', $entityId)
            ->orderBy('al.created_at', 'DESC')
            ->limit($limit)
            ->get()
            ->getResultArray();
    }

    /**
     * Get user activities
     */
    public function getUserActivities($userId, $limit = 50, $clinicId = null)
    {
        if (!$clinicId) {
            return [];
        }

        return $this->db->table('activity_logs al')
            ->select('al.*, u.first_name, u.last_name')
            ->join('users u', 'u.id = al.user_id', 'left')
            ->where('al.clinic_id', $clinicId)
            ->where('al.user_id', $userId)
            ->orderBy('al.created_at', 'DESC')
            ->limit($limit)
            ->get()
            ->getResultArray();
    }
}
