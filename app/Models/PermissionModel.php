<?php

namespace App\Models;

use CodeIgniter\Model;

class PermissionModel extends Model
{
    protected $table = 'permissions';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = [
        'module',
        'action',
        'name',
        'description',
        'category',
        'is_system',
        'sort_order'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    // Validation
    protected $validationRules = [
        'module' => 'required|max_length[50]',
        'action' => 'required|max_length[50]',
        'name' => 'required|max_length[100]',
        'description' => 'permit_empty',
        'category' => 'permit_empty|max_length[50]',
        'is_system' => 'permit_empty|in_list[0,1]',
        'sort_order' => 'permit_empty|integer'
    ];

    protected $validationMessages = [
        'module' => [
            'required' => 'Module is required',
            'max_length' => 'Module name cannot exceed 50 characters'
        ],
        'action' => [
            'required' => 'Action is required',
            'max_length' => 'Action name cannot exceed 50 characters'
        ],
        'name' => [
            'required' => 'Permission name is required',
            'max_length' => 'Permission name cannot exceed 100 characters'
        ]
    ];

    /**
     * Get permissions by module
     */
    public function getByModule($module)
    {
        return $this->where('module', $module)
                   ->orderBy('sort_order', 'ASC')
                   ->findAll();
    }

    /**
     * Get permissions by category
     */
    public function getByCategory($category)
    {
        return $this->where('category', $category)
                   ->orderBy('sort_order', 'ASC')
                   ->findAll();
    }

    /**
     * Get all permissions grouped by module
     */
    public function getGroupedByModule()
    {
        $permissions = $this->orderBy('module', 'ASC')
                          ->orderBy('sort_order', 'ASC')
                          ->findAll();

        $grouped = [];
        foreach ($permissions as $permission) {
            $grouped[$permission['module']][] = $permission;
        }

        return $grouped;
    }

    /**
     * Get all permissions grouped by category
     */
    public function getGroupedByCategory()
    {
        $permissions = $this->orderBy('category', 'ASC')
                          ->orderBy('sort_order', 'ASC')
                          ->findAll();

        $grouped = [];
        foreach ($permissions as $permission) {
            $grouped[$permission['category']][] = $permission;
        }

        return $grouped;
    }

    /**
     * Check if permission exists
     */
    public function exists($module, $action)
    {
        return $this->where('module', $module)
                   ->where('action', $action)
                   ->countAllResults() > 0;
    }

    /**
     * Get permission by module and action
     */
    public function getByModuleAction($module, $action)
    {
        return $this->where('module', $module)
                   ->where('action', $action)
                   ->first();
    }

    /**
     * Get system permissions (cannot be deleted)
     */
    public function getSystemPermissions()
    {
        return $this->where('is_system', 1)
                   ->orderBy('module', 'ASC')
                   ->orderBy('sort_order', 'ASC')
                   ->findAll();
    }

    /**
     * Get custom permissions (can be deleted)
     */
    public function getCustomPermissions()
    {
        return $this->where('is_system', 0)
                   ->orderBy('module', 'ASC')
                   ->orderBy('sort_order', 'ASC')
                   ->findAll();
    }

    /**
     * Create permission if not exists
     */
    public function createIfNotExists($module, $action, $name, $description = '', $category = 'general', $isSystem = 0)
    {
        if (!$this->exists($module, $action)) {
            $sortOrder = $this->getSortOrder($module, $action);
            
            return $this->insert([
                'module' => $module,
                'action' => $action,
                'name' => $name,
                'description' => $description,
                'category' => $category,
                'is_system' => $isSystem,
                'sort_order' => $sortOrder
            ]);
        }
        
        return false;
    }

    /**
     * Get sort order for permission
     */
    protected function getSortOrder($module, $action)
    {
        $moduleOrder = [
            'dashboard' => 1,
            'patients' => 10,
            'appointments' => 20,
            'examinations' => 30,
            'treatments' => 40,
            'prescriptions' => 50,
            'finance' => 60,
            'reports' => 70,
            'inventory' => 80,
            'users' => 90,
            'settings' => 100
        ];

        $actionOrder = [
            'view' => 0,
            'create' => 1,
            'edit' => 2,
            'delete' => 3,
            'export' => 4,
            'print' => 5,
            'calendar' => 6,
            'reports' => 7,
            'roles' => 8
        ];

        return ($moduleOrder[$module] ?? 999) + ($actionOrder[$action] ?? 0);
    }

    /**
     * Delete permission (only if not system permission)
     */
    public function deletePermission($id)
    {
        $permission = $this->find($id);
        
        if ($permission && $permission['is_system'] == 0) {
            return $this->delete($id);
        }
        
        return false;
    }
}
