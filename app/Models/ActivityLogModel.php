<?php

namespace App\Models;

use CodeIgniter\Model;

class ActivityLogModel extends Model
{
    use \App\Traits\TenantTrait;

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
        
        // Check if this activity was read by the current user
        $isRead = $this->isActivityRead($activity['id']);
        
        return [
            'id' => $activity['id'],
            'title' => $actionConfig['title'],
            'message' => $this->buildMessage($activity, $actionConfig),
            'type' => $activity['entity_type'],
            'entity_type' => $activity['entity_type'],
            'entity_id' => $activity['entity_id'],
            'is_read' => $isRead,
            'created_at' => $activity['created_at'],
            'icon' => $actionConfig['icon'],
            'color' => $actionConfig['color'],
            'user_name' => $activity['first_name'] . ' ' . $activity['last_name']
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
        
        // Check if this activity was marked as read in session
        $readActivities = session()->get('read_activities') ?? [];
        $lastReadTime = session()->get('last_notification_read_time');
        
        // If no read time is set, all activities are unread
        if (!$lastReadTime) {
            return false;
        }
        
        // Get the activity creation time
        $activity = $this->find($activityId);
        if (!$activity) {
            return false;
        }
        
        $activityTime = strtotime($activity['created_at']);
        $readTime = $lastReadTime;
        
        // Activity is read if it was created before the last read time
        $isRead = $activityTime <= $readTime;
        
        return $isRead;
    }

    /**
     * Get action configuration
     */
    private function getActionConfig($action, $entityType)
    {
        $configs = [
            'patient' => [
                'create' => [
                    'title' => 'New Patient Registered',
                    'icon' => 'fas fa-user-plus',
                    'color' => 'green'
                ],
                'update' => [
                    'title' => 'Patient Updated',
                    'icon' => 'fas fa-user-edit',
                    'color' => 'blue'
                ],
                'delete' => [
                    'title' => 'Patient Deleted',
                    'icon' => 'fas fa-user-times',
                    'color' => 'red'
                ]
            ],
            'appointment' => [
                'create' => [
                    'title' => 'New Appointment Scheduled',
                    'icon' => 'fas fa-calendar-plus',
                    'color' => 'blue'
                ],
                'update' => [
                    'title' => 'Appointment Updated',
                    'icon' => 'fas fa-calendar-check',
                    'color' => 'purple'
                ],
                'cancel' => [
                    'title' => 'Appointment Cancelled',
                    'icon' => 'fas fa-calendar-times',
                    'color' => 'red'
                ]
            ],
            'examination' => [
                'create' => [
                    'title' => 'New Examination',
                    'icon' => 'fas fa-stethoscope',
                    'color' => 'green'
                ],
                'update' => [
                    'title' => 'Examination Updated',
                    'icon' => 'fas fa-clipboard-check',
                    'color' => 'blue'
                ]
            ],
            'treatment' => [
                'create' => [
                    'title' => 'New Treatment Started',
                    'icon' => 'fas fa-tooth',
                    'color' => 'green'
                ],
                'update' => [
                    'title' => 'Treatment Updated',
                    'icon' => 'fas fa-edit',
                    'color' => 'blue'
                ],
                'complete' => [
                    'title' => 'Treatment Completed',
                    'icon' => 'fas fa-check-circle',
                    'color' => 'green'
                ]
            ],
            'inventory' => [
                'create' => [
                    'title' => 'Inventory Item Added',
                    'icon' => 'fas fa-box',
                    'color' => 'green'
                ],
                'update' => [
                    'title' => 'Inventory Updated',
                    'icon' => 'fas fa-edit',
                    'color' => 'blue'
                ],
                'low_stock' => [
                    'title' => 'Low Stock Alert',
                    'icon' => 'fas fa-exclamation-triangle',
                    'color' => 'yellow'
                ]
            ],
            'finance' => [
                'create' => [
                    'title' => 'Payment Received',
                    'icon' => 'fas fa-dollar-sign',
                    'color' => 'green'
                ],
                'update' => [
                    'title' => 'Payment Updated',
                    'icon' => 'fas fa-edit',
                    'color' => 'blue'
                ]
            ]
        ];

        return $configs[$entityType][$action] ?? [
            'title' => 'System Activity',
            'icon' => 'fas fa-cog',
            'color' => 'gray'
        ];
    }

    /**
     * Build notification message
     */
    private function buildMessage($activity, $actionConfig)
    {
        $userName = $activity['first_name'] . ' ' . $activity['last_name'];
        
        if (!empty($activity['description'])) {
            return $activity['description'];
        }

        // Default messages based on action and entity type
        $messages = [
            'patient' => [
                'create' => "New patient registered by {$userName}",
                'update' => "Patient information updated by {$userName}",
                'delete' => "Patient record deleted by {$userName}"
            ],
            'appointment' => [
                'create' => "New appointment scheduled by {$userName}",
                'update' => "Appointment updated by {$userName}",
                'cancel' => "Appointment cancelled by {$userName}"
            ],
            'examination' => [
                'create' => "New examination conducted by {$userName}",
                'update' => "Examination updated by {$userName}"
            ],
            'treatment' => [
                'create' => "New treatment started by {$userName}",
                'update' => "Treatment updated by {$userName}",
                'complete' => "Treatment completed by {$userName}"
            ],
            'inventory' => [
                'create' => "New inventory item added by {$userName}",
                'update' => "Inventory updated by {$userName}",
                'low_stock' => "Low stock alert triggered"
            ],
            'finance' => [
                'create' => "Payment received by {$userName}",
                'update' => "Payment updated by {$userName}"
            ]
        ];

        return $messages[$activity['entity_type']][$activity['action']] ?? "System activity by {$userName}";
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
