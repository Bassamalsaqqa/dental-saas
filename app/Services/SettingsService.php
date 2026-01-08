<?php

namespace App\Services;

use App\Models\SettingsModel;

class SettingsService
{
    protected $settingsModel;
    protected $settings;
    protected $currencySymbols = [
        'USD' => '$',
        'EUR' => '€',
        'GBP' => '£',
        'JPY' => '¥',
        'CAD' => 'C$',
        'AUD' => 'A$',
        'CHF' => 'CHF',
        'CNY' => '¥',
        'SEK' => 'kr',
        'NOK' => 'kr',
        'DKK' => 'kr',
        'PLN' => 'zł',
        'CZK' => 'Kč',
        'HUF' => 'Ft',
        'RUB' => '₽',
        'INR' => '₹',
        'BRL' => 'R$',
        'MXN' => '$',
        'KRW' => '₩',
        'SGD' => 'S$',
        'HKD' => 'HK$',
        'NZD' => 'NZ$',
        'TRY' => '₺',
        'ZAR' => 'R',
        'AED' => 'د.إ',
        'SAR' => 'ر.س',
        'QAR' => 'ر.ق',
        'KWD' => 'د.ك',
        'BHD' => 'د.ب',
        'OMR' => 'ر.ع.',
        'JOD' => 'د.ا',
        'LBP' => 'ل.ل',
        'EGP' => 'ج.م',
        'ILS' => '₪',
        'THB' => '฿',
        'MYR' => 'RM',
        'IDR' => 'Rp',
        'PHP' => '₱',
        'VND' => '₫',
        'PKR' => '₨',
        'BDT' => '৳',
        'LKR' => '₨',
        'NPR' => '₨',
        'MMK' => 'K',
        'KHR' => '៛',
        'LAK' => '₭',
        'BND' => 'B$',
        'FJD' => 'FJ$',
        'TOP' => 'T$',
        'WST' => 'WS$',
        'VUV' => 'Vt',
        'SBD' => 'SI$',
        'PGK' => 'K',
        'ARS' => '$',
        'CLP' => '$',
        'COP' => '$',
        'PEN' => 'S/',
        'UYU' => '$U',
        'BOB' => 'Bs',
        'PYG' => '₲',
        'VES' => 'Bs.S',
        'GYD' => 'G$',
        'SRD' => 'Sr$',
        'TTD' => 'TT$',
        'JMD' => 'J$',
        'BBD' => 'Bds$',
        'BZD' => 'BZ$',
        'XCD' => 'EC$',
        'DOP' => 'RD$',
        'HTG' => 'G',
        'CUP' => '$',
        'CRC' => '₡',
        'GTQ' => 'Q',
        'HNL' => 'L',
        'NIO' => 'C$',
        'PAB' => 'B/.',
        'SVC' => '₡',
        'BMD' => 'BD$',
        'KYD' => 'CI$',
        'AWG' => 'ƒ',
        'ANG' => 'ƒ',
        'BSD' => 'B$',
        'RON' => 'lei',
        'BGN' => 'лв',
        'HRK' => 'kn',
        'RSD' => 'дин',
        'MKD' => 'ден',
        'BAM' => 'КМ',
        'ALL' => 'L',
        'ISK' => 'kr',
        'MDL' => 'L',
        'UAH' => '₴',
        'BYN' => 'Br',
        'GEL' => '₾',
        'AMD' => '֏',
        'AZN' => '₼',
        'KZT' => '₸',
        'KGS' => 'с',
        'TJS' => 'SM',
        'TMT' => 'T',
        'UZS' => 'сўм',
        'MNT' => '₮',
        'AFN' => '؋',
        'IRR' => '﷼',
        'IQD' => 'د.ع',
        'SYP' => 'ل.س',
        'YER' => '﷼',
        'LYD' => 'ل.د',
        'TND' => 'د.ت',
        'DZD' => 'د.ج',
        'MAD' => 'د.م.',
        'ETB' => 'Br',
        'KES' => 'KSh',
        'UGX' => 'USh',
        'TZS' => 'TSh',
        'RWF' => 'RF',
        'MWK' => 'MK',
        'ZMW' => 'ZK',
        'BWP' => 'P',
        'SZL' => 'L',
        'LSL' => 'L',
        'NAD' => 'N$',
        'AOA' => 'Kz',
        'MZN' => 'MT',
        'MGA' => 'Ar',
        'MUR' => '₨',
        'SCR' => '₨',
        'KMF' => 'CF',
        'DJF' => 'Fdj',
        'SOS' => 'S',
        'ERN' => 'Nfk',
        'STN' => 'Db',
        'CVE' => '$',
        'GMD' => 'D',
        'GHS' => '₵',
        'NGN' => '₦',
        'XOF' => 'CFA',
        'XAF' => 'FCFA'
    ];

    public function __construct()
    {
        $this->settingsModel = new SettingsModel();
        $this->loadSettings();
    }

    /**
     * Load all settings from database
     */
    private function loadSettings()
    {
        try {
            $this->settings = $this->settingsModel->getAllSettings();
        } catch (\Exception $e) {
            log_message('error', 'Error loading settings: ' . $e->getMessage());
            $this->settings = [];
        }
    }

    /**
     * Get a specific setting value
     */
    public function get($key, $default = null)
    {
        return $this->settings[$key] ?? $default;
    }

    /**
     * Get currency symbol
     */
    public function getCurrencySymbol()
    {
        $currency = $this->get('currency', 'USD');
        return $this->currencySymbols[$currency] ?? '$';
    }

    /**
     * Format currency amount
     */
    public function formatCurrency($amount, $showSymbol = true)
    {
        $symbol = $showSymbol ? $this->getCurrencySymbol() : '';
        $formattedAmount = number_format($amount, 2);
        $position = $this->get('currency_position', 'before');
        
        // Apply user's currency position preference
        if ($position === 'after') {
            return $formattedAmount . ' ' . $symbol;
        } else {
            return $symbol . $formattedAmount;
        }
    }

    /**
     * Format date according to user preference
     */
    public function formatDate($date, $format = null)
    {
        if (empty($date)) {
            return '';
        }

        $format = $format ?? $this->get('date_format', 'Y-m-d');
        
        try {
            // Handle Unix timestamp (integer)
            if (is_numeric($date)) {
                $dateObj = new \DateTime();
                $dateObj->setTimestamp($date);
                $date = $dateObj;
            }
            // Convert to DateTime object if it's a string
            elseif (is_string($date)) {
                $date = new \DateTime($date);
            }

            return $date->format($format);
        } catch (\Exception $e) {
            log_message('error', 'Error formatting date: ' . $e->getMessage() . ' for date: ' . $date);
            return $date; // Return original value if formatting fails
        }
    }

    /**
     * Format time according to user preference
     */
    public function formatTime($time, $format = null)
    {
        if (empty($time)) {
            return '';
        }

        $timeFormat = $this->get('time_format', '12');
        
        if ($format) {
            $formatToUse = $format;
        } else {
            $formatToUse = ($timeFormat === '24') ? 'H:i' : 'g:i A';
        }

        try {
            // Handle Unix timestamp (integer)
            if (is_numeric($time)) {
                $timeObj = new \DateTime();
                $timeObj->setTimestamp($time);
                $time = $timeObj;
            }
            // Convert to DateTime object if it's a string
            elseif (is_string($time)) {
                $time = new \DateTime($time);
            }

            return $time->format($formatToUse);
        } catch (\Exception $e) {
            log_message('error', 'Error formatting time: ' . $e->getMessage() . ' for time: ' . $time);
            return $time; // Return original value if formatting fails
        }
    }

    /**
     * Format datetime according to user preferences
     */
    public function formatDateTime($datetime, $dateFormat = null, $timeFormat = null)
    {
        if (empty($datetime)) {
            return '';
        }

        try {
            $dateFormatted = $this->formatDate($datetime, $dateFormat);
            $timeFormatted = $this->formatTime($datetime, $timeFormat);

            return $dateFormatted . ' ' . $timeFormatted;
        } catch (\Exception $e) {
            log_message('error', 'Error formatting datetime: ' . $e->getMessage() . ' for datetime: ' . $datetime);
            return $datetime; // Return original value if formatting fails
        }
    }

    /**
     * Get timezone
     */
    public function getTimezone()
    {
        return $this->get('timezone', 'UTC');
    }

    /**
     * Convert datetime to user's timezone
     */
    public function convertToUserTimezone($datetime, $fromTimezone = 'UTC')
    {
        if (empty($datetime)) {
            return null;
        }

        try {
            $userTimezone = $this->getTimezone();
            
            if (is_string($datetime)) {
                $datetime = new \DateTime($datetime, new \DateTimeZone($fromTimezone));
            }
            
            $datetime->setTimezone(new \DateTimeZone($userTimezone));
            
            return $datetime;
        } catch (\Exception $e) {
            log_message('error', 'Timezone conversion error: ' . $e->getMessage());
            return $datetime;
        }
    }

    /**
     * Get working hours
     */
    public function getWorkingHours()
    {
        return [
            'start' => $this->get('working_hours_start', '09:00'),
            'end' => $this->get('working_hours_end', '17:00'),
            'days' => $this->get('working_days', ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'])
        ];
    }

    /**
     * Get appointment duration
     */
    public function getAppointmentDuration()
    {
        return (int) $this->get('appointment_duration', 30);
    }

    /**
     * Check if a day is a working day
     */
    public function isWorkingDay($day)
    {
        $workingDays = $this->getWorkingHours()['days'];
        return in_array(strtolower($day), $workingDays);
    }

    /**
     * Get clinic information
     */
    public function getClinicInfo()
    {
        return [
            'name' => $this->get('clinic_name', 'DentalCare Clinic'),
            'address' => $this->get('clinic_address', '123 Dental Street, Medical District, City 12345'),
            'phone' => $this->get('clinic_phone', '+1 (555) 123-4567'),
            'email' => $this->get('clinic_email', 'info@dentalclinic.com'),
            'website' => $this->get('clinic_website', 'https://dentalclinic.com')
        ];
    }

    /**
     * Generate time slots based on working hours and appointment duration
     */
    public function generateTimeSlots($date = null, $excludeTimes = [])
    {
        $workingHours = $this->getWorkingHours();
        $duration = $this->getAppointmentDuration();
        
        $timeSlots = [];
        $startTime = strtotime($workingHours['start']);
        $endTime = strtotime($workingHours['end']);
        $slotDuration = $duration * 60; // Convert to seconds

        for ($time = $startTime; $time < $endTime; $time += $slotDuration) {
            $timeSlot = date('H:i', $time);
            
            if (!in_array($timeSlot, $excludeTimes)) {
                $timeSlots[] = [
                    'value' => $timeSlot,
                    'display' => $this->formatTime($timeSlot)
                ];
            }
        }

        return $timeSlots;
    }

    /**
     * Reload settings (useful after updates)
     */
    public function reloadSettings()
    {
        $this->loadSettings();
    }
}
