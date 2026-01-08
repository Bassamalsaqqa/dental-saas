<?php

if (!function_exists('is_menu_active')) {
    /**
     * Check if a menu item should be active based on current URI
     * 
     * @param string $menu_uri The menu URI to check against
     * @param string $current_uri Optional current URI (defaults to uri_string())
     * @return bool
     */
    function is_menu_active($menu_uri, $current_uri = null)
    {
        if ($current_uri === null) {
            $current_uri = uri_string();
        }
        
        // Remove leading and trailing slashes for comparison
        $menu_uri = trim($menu_uri, '/');
        $current_uri = trim($current_uri, '/');
        
        // Exact match
        if ($current_uri === $menu_uri) {
            return true;
        }
        
        // Check if current URI starts with menu URI (for sub-pages)
        if (strpos($current_uri, $menu_uri) === 0) {
            // Additional check to ensure it's not a partial match
            // e.g., 'exam' should not match 'examination'
            $next_char = substr($current_uri, strlen($menu_uri), 1);
            return empty($next_char) || $next_char === '/';
        }
        
        return false;
    }
}

if (!function_exists('get_menu_classes')) {
    /**
     * Get CSS classes for menu items based on active state
     * 
     * @param string $menu_uri The menu URI to check against
     * @param array $active_classes Classes to apply when active
     * @param array $inactive_classes Classes to apply when inactive
     * @param string $current_uri Optional current URI (defaults to uri_string())
     * @return string
     */
    function get_menu_classes($menu_uri, $active_classes = [], $inactive_classes = [], $current_uri = null)
    {
        $is_active = is_menu_active($menu_uri, $current_uri);
        
        if ($is_active) {
            return implode(' ', $active_classes);
        } else {
            return implode(' ', $inactive_classes);
        }
    }
}

if (!function_exists('get_icon_classes')) {
    /**
     * Get CSS classes for menu icons based on active state
     * 
     * @param string $menu_uri The menu URI to check against
     * @param string $active_class Class to apply when active
     * @param string $inactive_class Class to apply when inactive
     * @param string $current_uri Optional current URI (defaults to uri_string())
     * @return string
     */
    function get_icon_classes($menu_uri, $active_class = '', $inactive_class = '', $current_uri = null)
    {
        $is_active = is_menu_active($menu_uri, $current_uri);
        
        return $is_active ? $active_class : $inactive_class;
    }
}
