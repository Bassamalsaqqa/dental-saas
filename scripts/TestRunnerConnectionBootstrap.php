<?php
declare(strict_types=1);

// CLI-only bridge for safe_test_runner's already-validated child connection.
// No framework bootstrap, connection, mutation, output or credential files.
if (PHP_SAPI !== 'cli') exit(2);
$runtimeWritepath = getenv('WRITEPATH');
if ($runtimeWritepath && is_dir($runtimeWritepath)) {
    defined('WRITEPATH') || define('WRITEPATH', rtrim($runtimeWritepath, '\\/ ') . DIRECTORY_SEPARATOR);
}
require_once __DIR__.'/TestDatabasePreflight.php';
try {
    $connection=json_decode((string)getenv('DENTA_VERIFIED_TEST_CONNECTION'),true,512,JSON_THROW_ON_ERROR);
} catch (JsonException $error) {
    $connection=null;
}
$allowed=['hostname','port','database','username','password','DBDriver','DBPrefix'];
if (!is_array($connection) || array_diff(array_keys($connection),$allowed) || array_diff($allowed,array_keys($connection))
    || array_filter($connection,static fn($value)=>!is_string($value)&&!is_int($value))
    || \Scripts\TestDatabasePreflight::validateConfigBeforeConnection($connection,[])) {
    throw new RuntimeException('Verified isolated child connection required.');
}
foreach ($connection as $key=>$value) {
    $name='database.tests.'.$key;
    // Match CI env()'s empty sentinel; integer zero survives DotEnv's strict
    // empty-string/'0' fallback while still becoming "0" in string settings.
    $value=(string)$value;
    $encoded=$key==='DBPrefix'?$value:($value===''?'empty':($value==='0'?0:$value));
    putenv($name.'='.$encoded);
    $_ENV[$name]=$encoded;
    $_SERVER[$name]=$encoded;
}
// Do not retain a second copy of credentials in a general environment payload.
putenv('DENTA_VERIFIED_TEST_CONNECTION');
unset($_ENV['DENTA_VERIFIED_TEST_CONNECTION'],$_SERVER['DENTA_VERIFIED_TEST_CONNECTION']);
