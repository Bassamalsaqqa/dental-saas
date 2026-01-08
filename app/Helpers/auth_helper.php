<?php

if (!function_exists('is_logged_in')) {
    /**
     * Check if user is logged in
     *
     * @return bool
     */
    function is_logged_in()
    {
        $ionAuth = new \App\Libraries\IonAuth();
        return $ionAuth->loggedIn();
    }
}

if (!function_exists('is_admin')) {
    /**
     * Check if user is admin
     *
     * @return bool
     */
    function is_admin()
    {
        $ionAuth = new \App\Libraries\IonAuth();
        return $ionAuth->isAdmin();
    }
}

if (!function_exists('current_user')) {
    /**
     * Get current user
     *
     * @return object|null
     */
    function current_user()
    {
        $ionAuth = new \App\Libraries\IonAuth();
        return $ionAuth->user()->row();
    }
}

if (!function_exists('user_groups')) {
    /**
     * Get current user groups
     *
     * @return array
     */
    function user_groups()
    {
        $ionAuth = new \App\Libraries\IonAuth();
        return $ionAuth->getUsersGroups()->getResult();
    }
}

if (!function_exists('in_group')) {
    /**
     * Check if user is in specific group
     *
     * @param string|array $group
     * @param int|null $userId
     * @return bool
     */
    function in_group($group, $userId = null)
    {
        $ionAuth = new \App\Libraries\IonAuth();
        return $ionAuth->inGroup($group, $userId);
    }
}

if (!function_exists('has_permission')) {
    /**
     * Check if user has permission for specific action using RBAC system
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

        try {
            $permissionService = new \App\Services\PermissionService();
            return $permissionService->hasPermission($user->id, $module, $action);
        } catch (\Exception $e) {
            // Fallback to IonAuth groups if RBAC is not ready
            log_message('debug', 'RBAC permission check failed, falling back to IonAuth groups: ' . $e->getMessage());
            
            $userGroups = $ionAuth->getUsersGroups($user->id)->getResult();
            
            $permissions = [
                'admin' => [
                    'dashboard' => ['read'],
                    'patients' => ['view', 'create', 'edit', 'delete'],
                    'examinations' => ['view', 'create', 'edit', 'delete'],
                    'appointments' => ['view', 'create', 'edit', 'delete'],
                    'treatments' => ['view', 'create', 'edit', 'delete'],
                    'prescriptions' => ['view', 'create', 'edit', 'delete'],
                    'finance' => ['view', 'create', 'edit', 'delete'],
                    'reports' => ['view', 'export'],
                    'inventory' => ['view', 'create', 'edit', 'delete'],
                    'settings' => ['view', 'edit'],
                    'users' => ['view', 'create', 'edit', 'delete'],
                ],
                'doctor' => [
                    'dashboard' => ['read'],
                    'patients' => ['view', 'create', 'edit'],
                    'examinations' => ['view', 'create', 'edit'],
                    'appointments' => ['view', 'create', 'edit'],
                    'treatments' => ['view', 'create', 'edit'],
                    'prescriptions' => ['view', 'create', 'edit'],
                    'finance' => ['view'],
                    'reports' => ['view'],
                    'inventory' => ['view'],
                ],
                'receptionist' => [
                    'dashboard' => ['read'],
                    'patients' => ['view', 'create', 'edit'],
                    'appointments' => ['view', 'create', 'edit'],
                    'finance' => ['view', 'create', 'edit'],
                    'reports' => ['view'],
                ],
                'staff' => [
                    'dashboard' => ['read'],
                    'patients' => ['view'],
                    'appointments' => ['view'],
                    'inventory' => ['view'],
                ]
            ];

            $userPermissions = [];
            foreach ($userGroups as $group) {
                if (isset($permissions[$group->name])) {
                    foreach ($permissions[$group->name] as $mod => $actions) {
                        if (!isset($userPermissions[$mod])) {
                            $userPermissions[$mod] = [];
                        }
                        $userPermissions[$mod] = array_unique(array_merge($userPermissions[$mod], $actions));
                    }
                }
            }

            return isset($userPermissions[$module]) && in_array($action, $userPermissions[$module]);
        }
    }
}

if (!function_exists('auth_nav_item')) {
    /**
     * Generate navigation item with permission check
     *
     * @param string $url
     * @param string $text
     * @param string $module
     * @param string $action
     * @param string $icon
     * @return string
     */
    function auth_nav_item($url, $text, $module, $action = 'view', $icon = '')
    {
        if (!has_permission($module, $action)) {
            return '';
        }

        $iconHtml = $icon ? "<i class='{$icon}'></i> " : '';
        return "<li><a href='{$url}'>{$iconHtml}{$text}</a></li>";
    }
}

if (!function_exists('can_access')) {
    /**
     * Check if user can access a specific resource
     *
     * @param string $module
     * @param string $action
     * @return bool
     */
    function can_access($module, $action)
    {
        return has_permission($module, $action);
    }
}

if (!function_exists('permission_button')) {
    /**
     * Generate button with permission check
     *
     * @param string $url
     * @param string $text
     * @param string $module
     * @param string $action
     * @param string $class
     * @param string $icon
     * @return string
     */
    function permission_button($url, $text, $module, $action, $class = 'btn btn-primary', $icon = '')
    {
        if (!has_permission($module, $action)) {
            return '';
        }

        $iconHtml = $icon ? "<i class='{$icon}'></i> " : '';
        return "<a href='{$url}' class='{$class}'>{$iconHtml}{$text}</a>";
    }
}

if (!function_exists('getTimeBasedGreeting')) {
    /**
     * Get time-based greeting
     *
     * @return string
     */
    function getTimeBasedGreeting()
    {
        $hour = (int)date('H');
        
        if ($hour < 12) {
            return 'Good morning,';
        } elseif ($hour < 17) {
            return 'Good afternoon,';
        } else {
            return 'Good evening,';
        }
    }
}
