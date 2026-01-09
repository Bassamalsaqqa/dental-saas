<?php
if (php_sapi_name() !== 'cli') {
    header('HTTP/1.1 403 Forbidden');
    echo "Direct HTTP access to scripts is forbidden.";
    exit;
}

// Simple login test that bypasses CodeIgniter
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Simple Login Test</h1>";
echo "<p>Current time: " . date('Y-m-d H:i:s') . "</p>";
echo "<p>Session ID: " . session_id() . "</p>";

// Show any existing session data
if (!empty($_SESSION)) {
    echo "<h2>Session Data:</h2>";
    echo "<pre>";
    print_r($_SESSION);
    echo "</pre>";
}

// Handle POST request
if ($_POST) {
    echo "<h2>POST Data Received:</h2>";
    echo "<pre>";
    print_r($_POST);
    echo "</pre>";
    
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    
    // Set session message
    $_SESSION['error'] = 'POST RECEIVED: Email=' . $email . ', Password length=' . strlen($password);
    
    echo "<p style='color: green; font-weight: bold;'>POST data received and session set!</p>";
    echo "<p><a href='simple_login_test.php'>Refresh to see session data</a></p>";
} else {
    // Show any error message
    if (isset($_SESSION['error'])) {
        echo "<div style='background: #fee; border: 1px solid #fcc; padding: 10px; margin: 10px 0; color: #c00;'>";
        echo "<strong>Error:</strong> " . $_SESSION['error'];
        echo "</div>";
    }
    
    // Show any test message
    if (isset($_SESSION['test'])) {
        echo "<div style='background: #eef; border: 1px solid #ccf; padding: 10px; margin: 10px 0; color: #00c;'>";
        echo "<strong>Test:</strong> " . $_SESSION['test'];
        echo "</div>";
    }
    
    // Set test message
    $_SESSION['test'] = 'Session test at ' . date('H:i:s');
    
    // Show form
    echo "<form method='POST'>";
    echo "<h3>Test Login Form</h3>";
    echo "<p><label>Email:</label><br><input type='email' name='email' value='test@example.com' required></p>";
    echo "<p><label>Password:</label><br><input type='password' name='password' value='test123' required></p>";
    echo "<p><button type='submit'>Test Submit</button></p>";
    echo "</form>";
    
    echo "<p><a href='?clear=1'>Clear Session</a></p>";
}

if (isset($_GET['clear'])) {
    session_destroy();
    echo "<p>Session cleared. <a href='simple_login_test.php'>Refresh</a></p>";
}
?>
