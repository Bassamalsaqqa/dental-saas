<?php
/**
 * Root entry point - serves the application directly
 * This file includes the public/index.php to handle all requests
 */

// Change to the public directory
chdir(__DIR__ . '/public');

// Include the public/index.php
require __DIR__ . '/public/index.php';
?>