<?php
/**
 * Custom redirect handler to fix the full server path issue
 */

// Get the protocol
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';

// Get the host
$host = $_SERVER['HTTP_HOST'];

// Get the current request URI
$requestUri = $_SERVER['REQUEST_URI'];

// Check if we're being redirected to the full server path and fix it
if (strpos($requestUri, '/home/democa/public_html/dev/dental/public/') !== false) {
    $cleanUrl = $protocol . '://' . $host . '/dev/dental/public/';
    header('Location: ' . $cleanUrl, true, 301);
    exit();
}

// Also check for any other full server path patterns
if (strpos($requestUri, '/home/democa/public_html/') !== false) {
    $cleanUrl = $protocol . '://' . $host . '/dev/dental/public/';
    header('Location: ' . $cleanUrl, true, 301);
    exit();
}

// If we reach here, redirect to the public directory
$cleanUrl = $protocol . '://' . $host . '/dev/dental/public/';
header('Location: ' . $cleanUrl, true, 302);
exit();
?>
