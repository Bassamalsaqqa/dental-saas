<?php
if (php_sapi_name() !== 'cli') {
    http_response_code(404);
    exit;
}


use Config\Database;

// Simple migration runner script
define('FCPATH', __DIR__ . DIRECTORY_ROOT);
chdir(__DIR__);

// Load CodeIgniter bootstrapper
require_once __DIR__ . '/../system/Test/bootstrap.php';

$db = Database::connect();

echo "Checking finances table...<br>";
$fields = $db->getFieldNames('finances');

if (!in_array('total_amount', $fields)) {
    echo "Adding total_amount column...<br>";
    $db->query("ALTER TABLE finances ADD COLUMN total_amount DECIMAL(10,2) DEFAULT 0.00 AFTER tax_amount");
    echo "Done!<br>";
} else {
    echo "total_amount column already exists.<br>";
}

// Also check the ILS currency update
echo "Checking currency enum...<br>";
$db->query("ALTER TABLE finances MODIFY COLUMN currency ENUM('USD','EUR','GBP','BDT','ILS') DEFAULT 'USD'");
echo "Currency enum updated!<br>";

echo "All repairs completed.";
