<?php

namespace App\Models;

use CodeIgniter\Model;

class UserRoleModel extends Model
{
    protected $table = 'user_roles';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = [
        'user_id',
        'role_id',
        'assigned_by',
        'assigned_at',
        'expires_at',
        'is_active'
    ];

    // Dates
    protected $useTimestamps = false;
    protected $dateFormat = 'datetime';
    protected $createdField = 'assigned_at';
    protected $updatedField = null;

    // Validation
    protected $validationRules = [
        'user_id' => 'required|integer',
        'role_id' => 'required|integer',
        'assigned_by' => 'required|integer',
        'expires_at' => 'permit_empty|valid_date',
        'is_active' => 'permit_empty|in_list[0,1]'
    ];

    protected $validationMessages = [
        'user_id' => [
            'required' => 'User ID is required',
            'integer' => 'User ID must be a valid integer'
        ],
        'role_id' => [
            'required' => 'Role ID is required',
            'integer' => 'Role ID must be a valid integer'
        ],
        'assigned_by' => [
            'required' => 'Assigned by is required',
            'integer' => 'Assigned by must be a valid user ID'
        ]
    ];

    /**
     * Get user roles
     */
    public function getUserRoles($userId, $activeOnly = true)
    {
        $builder = $this->where('user_id', $userId);
        
        if ($activeOnly) {
            $builder->where('is_active', 1);
        }

        return $builder->findAll();
    }

    /**
     * Get user roles with role details
     */
    public function getUserRolesWithDetails($userId, $activeOnly = true)
    {
        $db = \Config\Database::connect();
        
        $builder = $db->table('user_roles ur')
                     ->select('r.*, ur.assigned_at, ur.expires_at, ur.is_active')
                     ->join('roles r', 'r.id = ur.role_id')
                     ->where('ur.user_id', $userId);

        if ($activeOnly) {
            $builder->where('ur.is_active', 1);
        }

        return $builder->orderBy('r.name', 'ASC')
                      ->get()
                      ->getResultArray();
    }

    /**
     * Assign role to user
     */
    public function assignRole($userId, $roleId, $assignedBy, $expiresAt = null)
    {
        // Check if already assigned
        $existing = $this->where('user_id', $userId)
                        ->where('role_id', $roleId)
                        ->first();

        if ($existing) {
            // Update existing assignment
            return $this->update($existing['id'], [
                'assigned_by' => $assignedBy,
                'expires_at' => $expiresAt,
                'is_active' => 1
            ]);
        } else {
            // Create new assignment
            return $this->insert([
                'user_id' => $userId,
                'role_id' => $roleId,
                'assigned_by' => $assignedBy,
                'assigned_at' => date('Y-m-d H:i:s'),
                'expires_at' => $expiresAt,
                'is_active' => 1
            ]);
        }
    }

    /**
     * Remove role from user
     */
    public function removeRole($userId, $roleId)
    {
        return $this->where('user_id', $userId)
                   ->where('role_id', $roleId)
                   ->delete();
    }

    /**
     * Deactivate role for user
     */
    public function deactivateRole($userId, $roleId)
    {
        return $this->where('user_id', $userId)
                   ->where('role_id', $roleId)
                   ->update(['is_active' => 0]);
    }

    /**
     * Assign multiple roles to user
     */
    public function assignRoles($userId, $roleIds, $assignedBy, $expiresAt = null)
    {
        $this->db->transStart();

        // Remove all existing roles
        $this->where('user_id', $userId)->delete();

        // Insert new roles
        if (!empty($roleIds)) {
            $data = [];
            foreach ($roleIds as $roleId) {
                $data[] = [
                    'user_id' => $userId,
                    'role_id' => $roleId,
                    'assigned_by' => $assignedBy,
                    'assigned_at' => date('Y-m-d H:i:s'),
                    'expires_at' => $expiresAt,
                    'is_active' => 1
                ];
            }
            
            if (!empty($data)) {
                $this->insertBatch($data);
            }
        }

        $this->db->transComplete();
        return $this->db->transStatus();
    }

    /**
     * Check if user has role
     */
    public function hasRole($userId, $roleSlug)
    {
        $db = \Config\Database::connect();
        $result = $db->table('user_roles ur')
                    ->join('roles r', 'r.id = ur.role_id')
                    ->where('ur.user_id', $userId)
                    ->where('r.slug', $roleSlug)
                    ->where('ur.is_active', 1)
                    ->countAllResults();

        return $result > 0;
    }

    /**
     * Check if user has any of the given roles
     */
    public function hasAnyRole($userId, $roleSlugs)
    {
        if (empty($roleSlugs)) {
            return false;
        }

        $db = \Config\Database::connect();
        $result = $db->table('user_roles ur')
                    ->join('roles r', 'r.id = ur.role_id')
                    ->where('ur.user_id', $userId)
                    ->whereIn('r.slug', $roleSlugs)
                    ->where('ur.is_active', 1)
                    ->countAllResults();

        return $result > 0;
    }

    /**
     * Get users with specific role
     */
    public function getUsersByRole($roleId, $activeOnly = true)
    {
        $db = \Config\Database::connect();
        
        $builder = $db->table('user_roles ur')
                     ->select('u.*, ur.assigned_at, ur.expires_at, ur.is_active')
                     ->join('users u', 'u.id = ur.user_id')
                     ->where('ur.role_id', $roleId);

        if ($activeOnly) {
            $builder->where('ur.is_active', 1);
        }

        return $builder->orderBy('u.first_name', 'ASC')
                      ->get()
                      ->getResultArray();
    }

    /**
     * Get expired role assignments
     */
    public function getExpiredAssignments()
    {
        return $this->where('expires_at <', date('Y-m-d H:i:s'))
                   ->where('is_active', 1)
                   ->findAll();
    }

    /**
     * Deactivate expired assignments
     */
    public function deactivateExpiredAssignments()
    {
        return $this->where('expires_at <', date('Y-m-d H:i:s'))
                   ->where('is_active', 1)
                   ->update(['is_active' => 0]);
    }

    /**
     * Get role assignment history for user
     */
    public function getAssignmentHistory($userId)
    {
        $db = \Config\Database::connect();
        return $db->table('user_roles ur')
                 ->select('r.name as role_name, r.slug as role_slug, ur.*, u.first_name, u.last_name')
                 ->join('roles r', 'r.id = ur.role_id')
                 ->join('users u', 'u.id = ur.assigned_by')
                 ->where('ur.user_id', $userId)
                 ->orderBy('ur.assigned_at', 'DESC')
                 ->get()
                 ->getResultArray();
    }

    /**
     * Get role assignment statistics
     */
    public function getAssignmentStats()
    {
        $db = \Config\Database::connect();
        
        $stats = $db->table('user_roles ur')
                   ->select('r.name as role_name, COUNT(*) as user_count')
                   ->join('roles r', 'r.id = ur.role_id')
                   ->where('ur.is_active', 1)
                   ->groupBy('ur.role_id')
                   ->get()
                   ->getResultArray();

        return $stats;
    }


}
