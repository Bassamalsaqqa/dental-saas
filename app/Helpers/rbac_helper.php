<?php

if (!function_exists('has_permission')) {
    /**
     * Check if current user has permission
     *
     * @param string $module
     * @param string $action
     * @return bool
     */
    function has_permission($module, $action)
    {
        $ionAuth = new \App\Libraries\IonAuth();
        $user = $ionAuth->user()->row();
        
        if (!$user) {
            return false;
        }

        $permissionService = new \App\Services\PermissionService();
        return $permissionService->hasPermission($user->id, $module, $action);
    }
}

if (!function_exists('has_role')) {
    /**
     * Check if current user has role
     *
     * @param string $roleSlug
     * @return bool
     */
    function has_role($roleSlug)
    {
        $ionAuth = new \App\Libraries\IonAuth();
        $user = $ionAuth->user()->row();
        
        if (!$user) {
            return false;
        }

        $permissionService = new \App\Services\PermissionService();
        return $permissionService->hasRole($user->id, $roleSlug);
    }
}

if (!function_exists('has_any_role')) {
    /**
     * Check if current user has any of the given roles
     *
     * @param array $roleSlugs
     * @return bool
     */
    function has_any_role($roleSlugs)
    {
        $ionAuth = new \App\Libraries\IonAuth();
        $user = $ionAuth->user()->row();
        
        if (!$user) {
            return false;
        }

        $permissionService = new \App\Services\PermissionService();
        return $permissionService->hasAnyRole($user->id, $roleSlugs);
    }
}

if (!function_exists('is_admin')) {
    /**
     * Check if current user is admin
     *
     * @return bool
     */
    function is_admin()
    {
        $ionAuth = new \App\Libraries\IonAuth();
        $user = $ionAuth->user()->row();
        
        if (!$user) {
            return false;
        }

        $permissionService = new \App\Services\PermissionService();
        return $permissionService->isAdmin($user->id);
    }
}

if (!function_exists('get_user_permissions')) {
    /**
     * Get current user permissions
     *
     * @return array
     */
    function get_user_permissions()
    {
        $ionAuth = new \App\Libraries\IonAuth();
        $user = $ionAuth->user()->row();
        
        if (!$user) {
            return [];
        }

        $permissionService = new \App\Services\PermissionService();
        return $permissionService->getUserPermissions($user->id);
    }
}

if (!function_exists('get_module_icon')) {
    /**
     * Get icon for module
     *
     * @param string $module
     * @return string
     */
    function get_module_icon($module)
    {
        $icons = [
            'dashboard' => 'tachometer-alt',
            'patients' => 'user-injured',
            'appointments' => 'calendar-alt',
            'examinations' => 'stethoscope',
            'treatments' => 'procedures',
            'prescriptions' => 'prescription-bottle-alt',
            'finance' => 'dollar-sign',
            'reports' => 'chart-bar',
            'inventory' => 'boxes',
            'users' => 'users',
            'settings' => 'cog'
        ];
        
        return $icons[$module] ?? 'folder';
    }
}

if (!function_exists('can_access_module')) {
    /**
     * Check if current user can access module
     *
     * @param string $module
     * @return bool
     */
    function can_access_module($module)
    {
        $permissions = get_user_permissions();
        return isset($permissions[$module]);
    }
}

if (!function_exists('can_perform_action')) {
    /**
     * Check if current user can perform action on module
     *
     * @param string $module
     * @param string $action
     * @return bool
     */
    function can_perform_action($module, $action)
    {
        return has_permission($module, $action);
    }
}

if (!function_exists('get_accessible_modules')) {
    /**
     * Get modules accessible to current user
     *
     * @return array
     */
    function get_accessible_modules()
    {
        $ionAuth = new \App\Libraries\IonAuth();
        $user = $ionAuth->user()->row();
        
        if (!$user) {
            return [];
        }

        $permissionService = new \App\Services\PermissionService();
        return $permissionService->getAccessibleModules($user->id);
    }
}

if (!function_exists('get_accessible_actions')) {
    /**
     * Get actions accessible to current user for module
     *
     * @param string $module
     * @return array
     */
    function get_accessible_actions($module)
    {
        $ionAuth = new \App\Libraries\IonAuth();
        $user = $ionAuth->user()->row();
        
        if (!$user) {
            return [];
        }

        $permissionService = new \App\Services\PermissionService();
        return $permissionService->getAccessibleActions($user->id, $module);
    }
}
