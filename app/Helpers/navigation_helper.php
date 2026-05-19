<?php

if (!function_exists('nav_back')) {
    function nav_back(string $fallback = '/'): string
    {
        try {
            return service('navigation')->previousSafe($fallback);
        } catch (\Throwable $e) {
        }

        return base_url($fallback);
    }
}

if (!function_exists('workflow_back')) {
    function workflow_back(string $fallback = '/', ?string $returnTo = null): string
    {
        try {
            return service('navigation')->previousSafe($fallback, $returnTo);
        } catch (\Throwable $e) {
        }

        return base_url($fallback);
    }
}

if (!function_exists('workflow_cancel')) {
    function workflow_cancel(string $fallback = '/', ?string $returnTo = null): string
    {
        return workflow_back($fallback, $returnTo);
    }
}

if (!function_exists('workflow_context_url')) {
    function workflow_context_url(string $target, ?string $returnTo = null): string
    {
        try {
            if ($returnTo === null) {
                $returnTo = service('navigation')->currentTarget();
            }
            return service('navigation')->withReturnTo($target, $returnTo);
        } catch (\Throwable $e) {
        }

        return base_url(ltrim($target, '/'));
    }
}

if (!function_exists('workflow_current_target')) {
    function workflow_current_target(): ?string
    {
        try {
            return service('navigation')->currentTarget();
        } catch (\Throwable $e) {
        }

        return null;
    }
}
