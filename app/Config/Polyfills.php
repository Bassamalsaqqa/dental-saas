<?php
/**
 * Polyfill for missing intl extension
 * This file provides basic Locale class functionality when intl extension is not available
 */

if (!class_exists('Locale')) {
    /**
     * Basic Locale class polyfill
     * Provides minimal functionality to prevent crashes when intl extension is missing
     */
    class Locale
    {
        /**
         * Get the default locale
         *
         * @return string
         */
        public static function getDefault()
        {
            return 'en';
        }

        /**
         * Set the default locale
         *
         * @param string $locale
         * @return bool
         */
        public static function setDefault($locale)
        {
            return true;
        }

        /**
         * Get display name for locale
         *
         * @param string $locale
         * @param string $in_locale
         * @return string
         */
        public static function getDisplayName($locale, $in_locale = null)
        {
            return $locale;
        }
    }
}
