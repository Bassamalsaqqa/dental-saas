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

    // ... (formatActivityForNotification and others) ...

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
