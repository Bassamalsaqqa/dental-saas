<?php
if (php_sapi_name() !== 'cli') {
    header('HTTP/1.1 403 Forbidden');
    echo "Direct HTTP access to scripts is forbidden.";
    exit;
}

/**
 * Database Initialization Script
 * Run this script to set up the database tables
 * Access via: http://democa.store/dev/dental/public/init_database.php
 */

// Load CodeIgniter environment
require_once '../app/Config/Paths.php';
$paths = new Config\Paths();
require_once $paths->systemDirectory . '/bootstrap.php';

// Get database configuration
$dbConfig = new \Config\Database();
$db = \Config\Database::connect();

echo "<h1>Dental Management System - Database Setup</h1>";
echo "<style>body{font-family:Arial,sans-serif;margin:20px;} .success{color:green;} .error{color:red;} .info{color:blue;}</style>";

try {
    echo "<p class='info'>Connecting to database...</p>";
    
    // Test database connection
    $db->query("SELECT 1");
    echo "<p class='success'>✓ Database connection successful!</p>";
    
    // Read the SQL schema file
    $sqlFile = file_get_contents('../database_schema_fixed.sql');
    
    // Split the SQL into individual statements
    $statements = array_filter(array_map('trim', explode(';', $sqlFile)));
    
    $successCount = 0;
    $errorCount = 0;
    $errors = [];
    
    echo "<p class='info'>Creating database tables...</p>";
    
    foreach ($statements as $statement) {
        if (empty($statement) || strpos($statement, '--') === 0) {
            continue;
        }
        
        try {
            $db->query($statement);
            $successCount++;
            echo "<p class='success'>✓ " . substr($statement, 0, 50) . "...</p>";
        } catch (\Exception $e) {
            $errorCount++;
            $errors[] = $e->getMessage();
            echo "<p class='error'>✗ Error: " . $e->getMessage() . "</p>";
            echo "<p class='error'>Statement: " . substr($statement, 0, 100) . "...</p>";
        }
    }
    
    echo "<hr>";
    echo "<h2>Setup Summary</h2>";
    echo "<p class='success'>Successful statements: $successCount</p>";
    echo "<p class='error'>Failed statements: $errorCount</p>";
    
    if ($errorCount === 0) {
        echo "<p class='success'><strong>✅ All tables created successfully!</strong></p>";
        echo "<p class='info'>You can now access your dental management system at: <a href='index.php'>Dashboard</a></p>";
    } else {
        echo "<p class='error'><strong>⚠️ Some statements failed. Please check the errors above.</strong></p>";
        if (!empty($errors)) {
            echo "<h3>Error Details:</h3>";
            foreach ($errors as $error) {
                echo "<p class='error'>• $error</p>";
            }
        }
    }
    
} catch (\Exception $e) {
    echo "<p class='error'><strong>Database connection failed:</strong> " . $e->getMessage() . "</p>";
    echo "<p class='info'>Please check your database configuration in app/Config/Database.php</p>";
    echo "<p class='info'>Make sure the database 'democa_dental' exists and the user has proper permissions.</p>";
}

echo "<hr>";
echo "<p class='info'><small>This script can be safely deleted after successful database setup.</small></p>";
?>
