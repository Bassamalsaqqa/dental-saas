<?php
if (php_sapi_name() !== 'cli') {
    http_response_code(404);
    exit;
}

// Debug script to test environment variable loading

// Set the current directory
chdir(__DIR__);

// Load CodeIgniter paths
require_once 'app/Config/Paths.php';
$paths = new \Config\Paths();

// Load DotEnv manually
require_once $paths->systemDirectory . '/Config/DotEnv.php';
$dotEnv = new \CodeIgniter\Config\DotEnv($paths->appDirectory . '/../');
$result = $dotEnv->load();

echo "DotEnv load result: " . ($result ? 'SUCCESS' : 'FAILED') . "\n\n";

// Define environment
if (!defined('ENVIRONMENT')) {
    $env = $_ENV['CI_ENVIRONMENT'] ?? $_SERVER['CI_ENVIRONMENT'] ?? getenv('CI_ENVIRONMENT') ?: 'production';
    define('ENVIRONMENT', $env);
}

echo "ENVIRONMENT constant: " . ENVIRONMENT . "\n\n";

// Test environment variables
echo "Testing database environment variables:\n";
echo "database.default.hostname: " . ($_ENV['database.default.hostname'] ?? 'NOT FOUND') . "\n";
echo "database.default.username: " . ($_ENV['database.default.username'] ?? 'NOT FOUND') . "\n";
echo "database.default.password: " . ($_ENV['database.default.password'] ?? 'NOT FOUND') . "\n";
echo "database.default.database: " . ($_ENV['database.default.database'] ?? 'NOT FOUND') . "\n";

echo "\nTesting with env() function:\n";
echo "env('database.default.hostname'): " . (env('database.default.hostname') ?? 'NOT FOUND') . "\n";

// Test the env function implementation
function env(string $key, $default = null)
{
    $value = $_ENV[$key] ?? $_SERVER[$key] ?? getenv($key);
    if ($value === false) {
        return $default;
    }
    switch (strtolower($value)) {
        case 'true':
        case '(true)':
            return true;
        case 'false':
        case '(false)':
            return false;
        case 'empty':
        case '(empty)':
            return '';
        case 'null':
        case '(null)':
            return null;
    }
    return $value;
}

echo "\nAfter defining env() function:\n";
echo "env('database.default.hostname'): " . (env('database.default.hostname') ?? 'NOT FOUND') . "\n";
echo "env('database.default.username'): " . (env('database.default.username') ?? 'NOT FOUND') . "\n";
echo "env('database.default.password'): " . (env('database.default.password') ?? 'NOT FOUND') . "\n";
echo "env('database.default.database'): " . (env('database.default.d database') ?? 'NOT FOUND') . "\n";
