<?php
if (php_sapi_name() !== 'cli') {
    http_response_code(404);
    exit;
}

$host = getenv('database.default.hostname');
$username = getenv('database.default.username');
$password = getenv('database.default.password');
$database = getenv('database.default.database');

if (!$host || !$username || !$password || !$database) {
    die("Error: Database credentials (hostname, username, password, database) must be set in environment variables.\n");
}

$conn = new mysqli($host, $username, $password, $database);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$result = $conn->query("DESCRIBE users");
if ($result) {
    echo "Users table structure:\n";
    while ($row = $result->fetch_assoc()) {
        echo $row['Field'] . " - " . $row['Type'] . "\n";
    }
} else {
    echo "Error describing users table: " . $conn->error . "\n";
}

$conn->close();