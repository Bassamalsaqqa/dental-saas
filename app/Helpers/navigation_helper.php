<?php

if (!function_exists('nav_back')) {
    function nav_back(string $fallback = '/'): string
    {
        try {
            $prev = service('navigation')->previous();
            if ($prev && !empty($prev['url'])) {
                return $prev['url'];
            }
        } catch (\Throwable $e) {
        }

        return base_url($fallback);
    }
}
