<?php
declare(strict_types=1);

namespace Tests\Support;

/** Explicit, shared configuration for this loopback-only harness; never guess credentials. */
final class PatientNavigationConnection
{
    public static function isolateWritable(string $runId): void
    {
        if (!preg_match('/^[a-f0-9]{16}$/', $runId)) throw new \RuntimeException('Invalid owned run identifier.');
        $expected = dirname(__DIR__, 2) . '/writable/tmp/patient_http_' . $runId . '/runtime';
        $path = getenv('DENTA_TEST_WRITEPATH');
        if (!is_string($path) || str_replace('\\', '/', $path) !== str_replace('\\', '/', $expected)
            || is_link($path) || strcasecmp(str_replace('\\', '/', (string) realpath($path)), str_replace('\\', '/', $expected)) !== 0) {
            throw new \RuntimeException('Owned isolated runtime directory required.');
        }
        if (defined('WRITEPATH')) throw new \RuntimeException('Runtime isolation must precede application bootstrap.');
        define('WRITEPATH', realpath($path) . DIRECTORY_SEPARATOR);
    }

    public static function load(): array
    {
        $keys = ['hostname', 'port', 'database', 'username', 'password', 'DBDriver', 'DBPrefix'];
        $json = getenv('DENTA_VERIFIED_TEST_CONNECTION');
        if ($json !== false && $json !== '') {
            try { $connection = json_decode($json, true, 32, JSON_THROW_ON_ERROR); }
            catch (\JsonException $error) { throw new \RuntimeException('Invalid test connection JSON.'); }
            if (!is_array($connection)) throw new \RuntimeException('Test connection must be an object.');
        } else {
            $connection = [];
            foreach ($keys as $key) {
                $value = getenv('database.tests.' . $key);
                if ($value !== false) $connection[$key] = $value === 'empty' ? '' : $value;
            }
        }
        if (array_diff($keys, array_keys($connection)) || array_diff(array_keys($connection), $keys)) {
            throw new \RuntimeException('Explicit complete test connection required; no credential defaults.');
        }
        foreach ($keys as $key) {
            if ($key !== 'port' && !is_string($connection[$key])) throw new \RuntimeException('Invalid test connection field type.');
        }
        if (!is_scalar($connection['port']) || !ctype_digit((string) $connection['port'])) throw new \RuntimeException('Invalid test port.');
        $connection['port'] = (int) $connection['port'];
        if ($connection['port'] !== 3307 || $connection['hostname'] !== '127.0.0.1' || $connection['username'] === '') {
            throw new \RuntimeException('Explicit loopback 3307 test connection required.');
        }
        $errors = \Scripts\TestDatabasePreflight::validateConfigBeforeConnection($connection, []);
        if ($errors) throw new \RuntimeException('Test connection rejected by repository preflight.');
        return $connection;
    }
}
