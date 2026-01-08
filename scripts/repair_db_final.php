<?php
if (php_sapi_name() !== 'cli') {
    http_response_code(404);
    exit;
}

// Standalone repair script - Environment Configured Only
$host = getenv('database.default.hostname');
$username = getenv('database.default.username');
$password = getenv('database.default.password');
$database = getenv('database.default.database');
$port = getenv('database.default.port');

if (!$host || !$username || !$password || !$database || !$port) {
    die("Error: Database credentials (hostname, username, password, database, port) must be set in environment variables.\n");
}

echo "Attempting to connect to $host:$port...\n";
$conn = new mysqli($host, $username, $password, $database, $port);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error . "\n");
}

echo "Connected successfully!\n";

echo "Checking finances table...\n";
$result = $conn->query("SHOW COLUMNS FROM finances LIKE 'total_amount'");
if ($result && $result->num_rows == 0) {
    echo "Adding total_amount column...\n";
    if ($conn->query("ALTER TABLE finances ADD COLUMN total_amount DECIMAL(10,2) DEFAULT 0.00 AFTER tax_amount")) {
        echo "total_amount added successfully!\n";
    } else {
        echo "Error adding total_amount: " . $conn->error . "\n";
    }
} else {
    echo "total_amount already exists.\n";
}

echo "Updating currency enum...\n";
if ($conn->query("ALTER TABLE finances MODIFY COLUMN currency ENUM('USD','EUR','GBP','BDT','ILS') DEFAULT 'USD'")) {
    echo "Currency enum updated successfully!\n";
} else {
    echo "Error updating currency enum: " . $conn->error . "\n";
}

$conn->close();
echo "Repairs completed.\n";