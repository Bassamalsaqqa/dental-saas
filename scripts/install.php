<?php
/**
 * CLI Installer for DentaCare Pro
 *
 * This script is intended for CLI use only.
 * Access via browser is strictly prohibited for security reasons.
 */

if (php_sapi_name() !== 'cli') {
    http_response_code(404);
    exit;
}

echo "DentaCare Pro Installer (CLI Mode)\n";
echo "==================================\n\n";
echo "For security reasons, the browser-based installer has been disabled.\n";
echo "Please follow the manual installation steps in docs/installation.html\n";
echo "to configure your database and environment.\n\n";