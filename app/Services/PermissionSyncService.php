<?php

namespace App\Services;

use App\Models\PermissionModel;
use App\Models\RoleModel;
use App\Config\Permissions;

class PermissionSyncService
{
    protected $permissionModel;
    protected $roleModel;
    
    public function __construct()
    {
        $this->permissionModel = new PermissionModel();
        $this->roleModel = new RoleModel();
    }
    
    /**
     * Sync permissions from config to database
     */
    public function syncPermissions()
    {
        $configPermissions = Permissions::getDefaultPermissions();
        $syncedCount = 0;
        
        foreach ($configPermissions as $module => $actions) {
            foreach ($actions as $action => $name) {
                // Check if permission exists
                if (!$this->permissionModel->exists($module, $action)) {
                    // Create new permission
                    $this->permissionModel->createIfNotExists(
                        $module, 
                        $action, 
                        $name, 
                        $name, 
                        $module, 
                        1 // System permission
                    );
                    $syncedCount++;
                }
            }
        }
        
        return $syncedCount;
    }
    
    /**
     * Sync roles from config to database
     */
    public function syncRoles()
    {
        $configRoles = Permissions::getDefaultRoles();
        $syncedCount = 0;
        
        foreach ($configRoles as $slug => $roleData) {
            // Check if role exists
            $existingRole = $this->roleModel->getBySlug($slug);
            
            if (!$existingRole) {
                // Create new role
                $roleId = $this->roleModel->insert([
                    'name' => $roleData['name'],
                    'slug' => $slug,
                    'description' => $roleData['description'],
                    'is_system' => 1,
                    'created_by' => 1 // Super admin
                ]);
                
                if ($roleId) {
                    // Assign permissions to role
                    $this->assignPermissionsToRole($roleId, $roleData['permissions']);
                    $syncedCount++;
                }
            } else {
                // Role exists, but ensure permissions are assigned
                $this->assignPermissionsToRole($existingRole['id'], $roleData['permissions']);
            }
        }
        
        return $syncedCount;
    }
    
    /**
     * Assign permissions to a role
     */
    protected function assignPermissionsToRole($roleId, $permissions)
    {
        log_message('debug', "Assigning permissions to role ID: {$roleId}");
        
        if ($permissions === '*') {
            // Super admin gets all permissions
            $allPermissions = $this->permissionModel->findAll();
            log_message('debug', "Super admin role - found " . count($allPermissions) . " permissions");
            
            foreach ($allPermissions as $permission) {
                $result = $this->roleModel->assignPermission($roleId, $permission['id']);
                log_message('debug', "Assigned permission {$permission['id']} ({$permission['module']}.{$permission['action']}) to role {$roleId}: " . ($result ? 'SUCCESS' : 'FAILED'));
            }
        } else {
            // Assign specific permissions
            log_message('debug', "Specific permissions for role {$roleId}: " . json_encode($permissions));
            
            foreach ($permissions as $module => $actions) {
                foreach ($actions as $action) {
                    $permission = $this->permissionModel->getByModuleAction($module, $action);
                    
                    if ($permission) {
                        $result = $this->roleModel->assignPermission($roleId, $permission['id']);
                        log_message('debug', "Assigned permission {$permission['id']} ({$module}.{$action}) to role {$roleId}: " . ($result ? 'SUCCESS' : 'FAILED'));
                    } else {
                        log_message('error', "Permission not found: {$module}.{$action}");
                    }
                }
            }
        }
    }
    
    /**
     * Run full sync (permissions + roles)
     */
    public function fullSync()
    {
        $permissionCount = $this->syncPermissions();
        $roleCount = $this->syncRoles();
        $adminAssigned = $this->assignAdminToSuperAdmin();
        
        return [
            'permissions_synced' => $permissionCount,
            'roles_synced' => $roleCount,
            'admin_assigned' => $adminAssigned
        ];
    }

    /**
     * Assign admin user to Super Admin role
     */
    protected function assignAdminToSuperAdmin()
    {
        try {
            // Get Super Admin role
            $superAdminRole = $this->roleModel->getBySlug('super_admin');
            if (!$superAdminRole) {
                return false;
            }
            
            // Get admin users (users in admin group)
            $ionAuth = new \App\Libraries\IonAuth();
            $adminUsers = $ionAuth->users(1)->result(); // Group ID 1 is typically admin
            
            $assignedCount = 0;
            foreach ($adminUsers as $adminUser) {
                // Check if user already has Super Admin role
                $hasRole = $this->userRoleModel->hasRole($adminUser->id, 'super_admin');
                
                if (!$hasRole) {
                    // Assign Super Admin role to admin user
                    $this->userRoleModel->assignRole($adminUser->id, $superAdminRole['id'], $adminUser->id, null);
                    $assignedCount++;
                }
            }
            
            return $assignedCount;
        } catch (\Exception $e) {
            log_message('error', 'Failed to assign admin to Super Admin role: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Sync permissions for a specific module
     */
    public function syncModulePermissions($module, $permissions)
    {
        $syncedCount = 0;
        
        foreach ($permissions as $action => $name) {
            if (!$this->permissionModel->exists($module, $action)) {
                $this->permissionModel->createIfNotExists(
                    $module, 
                    $action, 
                    $name, 
                    $name, 
                    $module, 
                    0 // Custom permission
                );
                $syncedCount++;
            }
        }
        
        return $syncedCount;
    }

    /**
     * Update role permissions
     */
    public function updateRolePermissions($roleSlug, $permissions)
    {
        $role = $this->roleModel->getBySlug($roleSlug);
        
        if (!$role) {
            return false;
        }

        // Convert permissions to permission IDs
        $permissionIds = [];
        
        if ($permissions === '*') {
            // All permissions
            $allPermissions = $this->permissionModel->findAll();
            $permissionIds = array_column($allPermissions, 'id');
        } else {
            // Specific permissions
            foreach ($permissions as $module => $actions) {
                foreach ($actions as $action) {
                    $permission = $this->permissionModel->getByModuleAction($module, $action);
                    if ($permission) {
                        $permissionIds[] = $permission['id'];
                    }
                }
            }
        }

        // Update role permissions
        return $this->roleModel->assignPermissions($role['id'], $permissionIds);
    }

    /**
     * Get sync status
     */
    public function getSyncStatus()
    {
        $configPermissions = Permissions::getDefaultPermissions();
        $configRoles = Permissions::getDefaultRoles();
        
        $dbPermissions = $this->permissionModel->getSystemPermissions();
        $dbRoles = $this->roleModel->getSystemRoles();
        
        $permissionCount = 0;
        foreach ($configPermissions as $module => $actions) {
            $permissionCount += count($actions);
        }
        
        return [
            'config_permissions' => $permissionCount,
            'db_permissions' => count($dbPermissions),
            'config_roles' => count($configRoles),
            'db_roles' => count($dbRoles),
            'permissions_synced' => $permissionCount === count($dbPermissions),
            'roles_synced' => count($configRoles) === count($dbRoles)
        ];
    }
}
