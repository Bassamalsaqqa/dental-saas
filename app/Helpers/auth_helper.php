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
            log_message('error', 'RBAC permission check failed: ' . $e->getMessage());
            return false;
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