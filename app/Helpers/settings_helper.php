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
        $position = settings()->get('currency_position', 'before');
        
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
        return settings()->get('clinic_name', 'DentalCare Clinic');
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
