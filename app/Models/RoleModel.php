<?php

namespace App\Models;

use CodeIgniter\Model;

class RoleModel extends Model
{
    protected $table = 'roles';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = [
        'name',
        'slug',
        'description',
        'is_active',
        'is_system',
        'is_medical',
        'created_by'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    // Validation
    protected $validationRules = [
        'name' => 'required|max_length[100]|is_unique[roles.name,id,{id}]',
        'slug' => 'required|max_length[100]|is_unique[roles.slug,id,{id}]',
        'description' => 'permit_empty',
        'is_active' => 'permit_empty|in_list[0,1]',
        'is_system' => 'permit_empty|in_list[0,1]',
        'is_medical' => 'permit_empty|in_list[0,1]',
        'created_by' => 'required|integer'
    ];

    protected $validationMessages = [
        'name' => [
            'required' => 'Role name is required',
            'max_length' => 'Role name cannot exceed 100 characters',
            'is_unique' => 'Role name already exists'
        ],
        'slug' => [
            'required' => 'Role slug is required',
            'max_length' => 'Role slug cannot exceed 100 characters',
            'is_unique' => 'Role slug already exists'
        ],
        'created_by' => [
            'required' => 'Creator is required',
            'integer' => 'Creator must be a valid user ID'
        ]
    ];

    /**
     * Get role by slug
     */
    public function getBySlug($slug)
    {
        return $this->where('slug', $slug)->first();
    }

    /**
     * Get active roles only
     */
    public function getActiveRoles()
    {
        return $this->where('is_active', 1)
                   ->orderBy('name', 'ASC')
                   ->findAll();
    }

    /**
     * Get system roles (cannot be deleted)
     */
    public function getSystemRoles()
    {
        return $this->where('is_system', 1)
                   ->orderBy('name', 'ASC')
                   ->findAll();
    }

    /**
     * Get custom roles (can be deleted)
     */
    public function getCustomRoles()
    {
        return $this->where('is_system', 0)
                   ->orderBy('name', 'ASC')
                   ->findAll();
    }

    /**
     * Get role with permissions
     */
    public function getWithPermissions($roleId)
    {
        $role = $this->find($roleId);
        if (!$role) {
            return null;
        }

        $db = \Config\Database::connect();
        $permissions = $db->table('role_permissions rp')
                         ->select('p.*, rp.granted')
                         ->join('permissions p', 'p.id = rp.permission_id')
                         ->where('rp.role_id', $roleId)
                         ->where('rp.granted', 1)
                         ->get()
                         ->getResultArray();

        $role['permissions'] = $permissions;
        return $role;
    }

    /**
     * Assign permission to role
     */
    public function assignPermission($roleId, $permissionId, $granted = 1)
    {
        $db = \Config\Database::connect();
        
        // Check if already exists
        $exists = $db->table('role_permissions')
                    ->where('role_id', $roleId)
                    ->where('permission_id', $permissionId)
                    ->countAllResults();

        if ($exists > 0) {
            // Update existing
            return $db->table('role_permissions')
                     ->where('role_id', $roleId)
                     ->where('permission_id', $permissionId)
                     ->update(['granted' => $granted]);
        } else {
            // Insert new
            return $db->table('role_permissions')
                     ->insert([
                         'role_id' => $roleId,
                         'permission_id' => $permissionId,
                         'granted' => $granted
                     ]);
        }
    }

    /**
     * Remove permission from role
     */
    public function removePermission($roleId, $permissionId)
    {
        $db = \Config\Database::connect();
        return $db->table('role_permissions')
                 ->where('role_id', $roleId)
                 ->where('permission_id', $permissionId)
                 ->delete();
    }

    /**
     * Assign multiple permissions to role
     */
    public function assignPermissions($roleId, $permissions)
    {
        $db = \Config\Database::connect();
        
        // Remove all existing permissions
        $db->table('role_permissions')
           ->where('role_id', $roleId)
           ->delete();

        // Insert new permissions
        if (!empty($permissions)) {
            $data = [];
            foreach ($permissions as $permissionId) {
                $data[] = [
                    'role_id' => $roleId,
                    'permission_id' => $permissionId,
                    'granted' => 1
                ];
            }
            
            if (!empty($data)) {
                return $db->table('role_permissions')->insertBatch($data);
            }
        }
        
        return true;
    }

    /**
     * Get role permissions grouped by module
     */
    public function getPermissionsGrouped($roleId)
    {
        $db = \Config\Database::connect();
        $permissions = $db->table('role_permissions rp')
                         ->select('p.*, rp.granted')
                         ->join('permissions p', 'p.id = rp.permission_id')
                         ->where('rp.role_id', $roleId)
                         ->where('rp.granted', 1)
                         ->orderBy('p.module', 'ASC')
                         ->orderBy('p.sort_order', 'ASC')
                         ->get()
                         ->getResultArray();

        $grouped = [];
        foreach ($permissions as $permission) {
            $grouped[$permission['module']][] = $permission;
        }

        return $grouped;
    }

    /**
     * Check if role has permission
     */
    public function hasPermission($roleId, $module, $action)
    {
        $db = \Config\Database::connect();
        $result = $db->table('role_permissions rp')
                    ->select('rp.granted')
                    ->join('permissions p', 'p.id = rp.permission_id')
                    ->where('rp.role_id', $roleId)
                    ->where('p.module', $module)
                    ->where('p.action', $action)
                    ->where('rp.granted', 1)
                    ->get()
                    ->getRow();

        return $result ? true : false;
    }

    /**
     * Get users assigned to role (scoped to clinic if provided)
     */
    public function getUsers($roleId, $clinicId = null)
    {
        $db = \Config\Database::connect();
        $builder = $db->table('user_roles ur')
                 ->select('u.*, ur.assigned_at, ur.expires_at, ur.is_active')
                 ->join('users u', 'u.id = ur.user_id')
                 ->where('ur.role_id', $roleId)
                 ->where('ur.is_active', 1);

        if ($clinicId) {
            $builder->join('clinic_users cu', 'cu.user_id = u.id')
                    ->where('cu.clinic_id', $clinicId);
        }

        return $builder->get()->getResultArray();
    }

    /**
     * Count users assigned to role (scoped to clinic if provided)
     */
    public function getUserCount($roleId, $clinicId = null)
    {
        $db = \Config\Database::connect();
        $builder = $db->table('user_roles ur')
                 ->where('ur.role_id', $roleId)
                 ->where('ur.is_active', 1);

        if ($clinicId) {
            $builder->join('clinic_users cu', 'cu.user_id = ur.user_id')
                    ->where('cu.clinic_id', $clinicId);
        }

        return $builder->countAllResults();
    }

    /**
     * Delete role (only if not system role and no users assigned)
     */
    public function deleteRole($id)
    {
        $role = $this->find($id);
        
        if (!$role) {
            return false;
        }

        // Cannot delete Super Admin role
        if ($role['slug'] === 'super_admin') {
            return false;
        }

        // Cannot delete system roles
        if ($role['is_system'] == 1) {
            return false;
        }

        // Cannot delete if users are assigned
        if ($this->getUserCount($id) > 0) {
            return false;
        }

        return $this->delete($id);
    }

    /**
     * Create role with permissions
     */
    public function createWithPermissions($roleData, $permissions = [])
    {
        $this->db->transStart();

        // Create role
        $roleId = $this->insert($roleData);

        if ($roleId && !empty($permissions)) {
            // Assign permissions
            $this->assignPermissions($roleId, $permissions);
        }

        $this->db->transComplete();

        return $this->db->transStatus() ? $roleId : false;
    }

    /**
     * Update role with permissions
     */
    public function updateWithPermissions($id, $roleData, $permissions = [])
    {
        $role = $this->find($id);
        
        if (!$role) {
            return false;
        }

        // Cannot update Super Admin role
        if ($role['slug'] === 'super_admin') {
            return false;
        }

        $this->db->transStart();

        // Update role (disable validation for updates)
        $this->skipValidation(true);
        $this->update($id, $roleData);
        $this->skipValidation(false);

        // Update permissions
        if (!empty($permissions)) {
            $this->assignPermissions($id, $permissions);
        }

        $this->db->transComplete();

        return $this->db->transStatus();
    }
}
