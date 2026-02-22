<?php

if (!function_exists('settings')) {
    /**
     * Get the SettingsService instance
     */
    function settings()
    {
        return \Config\Services::settings();
    }
}

if (!function_exists('formatCurrency')) {
    /**
     * Format currency amount
     */
    function formatCurrency($amount, $showSymbol = true)
    {
        return settings()->formatCurrency($amount, $showSymbol);
    }
}

if (!function_exists('formatCurrencyAbbreviated')) {
    /**
     * Format currency amount with abbreviated notation (100k, 2k, etc.)
     */
    function formatCurrencyAbbreviated($amount, $showSymbol = true)
    {
        $symbol = $showSymbol ? settings()->getCurrencySymbol() : '';
        $position = settings()->get('currency_position', 'after');
        
        if ($amount >= 1000000) {
            $formattedAmount = number_format($amount / 1000000, 1) . 'M';
        } elseif ($amount >= 1000) {
            $formattedAmount = number_format($amount / 1000, 1) . 'k';
        } else {
            $formattedAmount = number_format($amount, 0);
        }
        
        // Apply user's currency position preference
        if ($position === 'after') {
            return $formattedAmount . ' ' . $symbol;
        } else {
            return $symbol . $formattedAmount;
        }
    }
}

if (!function_exists('formatDate')) {
    /**
     * Format date according to user preference
     */
    function formatDate($date, $format = null)
    {
        return settings()->formatDate($date, $format);
    }
}

if (!function_exists('formatTime')) {
    /**
     * Format time according to user preference
     */
    function formatTime($time, $format = null)
    {
        return settings()->formatTime($time, $format);
    }
}

if (!function_exists('formatDateTime')) {
    /**
     * Format datetime according to user preferences
     */
    function formatDateTime($datetime, $dateFormat = null, $timeFormat = null)
    {
        return settings()->formatDateTime($datetime, $dateFormat, $timeFormat);
    }
}

if (!function_exists('getCurrencySymbol')) {
    /**
     * Get currency symbol
     */
    function getCurrencySymbol()
    {
        return settings()->getCurrencySymbol();
    }
}

if (!function_exists('getClinicName')) {
    /**
     * Get clinic name
     */
    function getClinicName()
    {
        return settings()->get('clinic_name', 'Denta+ Clinic');
    }
}

if (!function_exists('getWorkingHours')) {
    /**
     * Get working hours
     */
    function getWorkingHours()
    {
        return settings()->getWorkingHours();
    }
}

if (!function_exists('getAppointmentDuration')) {
    /**
     * Get appointment duration
     */
    function getAppointmentDuration()
    {
        return settings()->getAppointmentDuration();
    }
}

if (!function_exists('ui_mb_substr')) {
    /**
     * UTF-8-safe substring with fallback when mbstring is unavailable.
     */
    function ui_mb_substr($value, int $start, int $length)
    {
        $value = (string) $value;

        if (function_exists('mb_substr')) {
            return mb_substr($value, $start, $length, 'UTF-8');
        }

        if (preg_match_all('/./us', $value, $matches)) {
            return implode('', array_slice($matches[0], $start, $length));
        }

        return substr($value, $start, $length);
    }
}

if (!function_exists('ui_mb_upper')) {
    /**
     * Unicode-safe uppercase (Arabic letters remain unchanged).
     */
    function ui_mb_upper($value)
    {
        $value = (string) $value;

        if (function_exists('mb_strtoupper')) {
            return mb_strtoupper($value, 'UTF-8');
        }

        return strtoupper($value);
    }
}

if (!function_exists('ui_initials')) {
    /**
     * Build safe initials from first/last names for avatar badges.
     */
    function ui_initials($firstName = '', $lastName = '', string $fallback = '?')
    {
        $first = trim((string) $firstName);
        $last = trim((string) $lastName);

        $initials = '';
        if ($first !== '') {
            $initials .= ui_mb_substr($first, 0, 1);
        }
        if ($last !== '') {
            $initials .= ui_mb_substr($last, 0, 1);
        }

        if ($initials === '' && $first !== '') {
            $initials = ui_mb_substr($first, 0, 2);
        }

        return ui_mb_upper($initials !== '' ? $initials : $fallback);
    }
}

if (!function_exists('ui_badge_text')) {
    /**
     * Build short badge text (e.g., clinic/item avatars) safely for UTF-8 names.
     */
    function ui_badge_text($text = '', int $length = 2, string $fallback = '?')
    {
        $value = trim((string) $text);
        if ($value === '') {
            return ui_mb_upper($fallback);
        }

        return ui_mb_upper(ui_mb_substr($value, 0, max(1, $length)));
    }
}
