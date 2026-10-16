<?php

declare(strict_types=1);

// Actual mutators, instrumented only at the SQL transport boundary; disposable DB only.
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
$root = dirname(__DIR__, 2);
require $root . '/scripts/TestDatabasePreflight.php';
\Scripts\TestDatabasePreflight::enforceEnvironment();
require $root . '/vendor/autoload.php';
require $root . '/system/Test/bootstrap.php';

final class Pr3LockOrderProbeConnection extends \CodeIgniter\Database\MySQLi\Connection
{
    public string $probePath = '';
    protected function execute(string $sql)
    {
        if ($this->probePath !== '' && stripos($sql, 'FOR UPDATE') !== false) {
            file_put_contents($this->probePath, json_encode(['sql' => $sql], JSON_THROW_ON_ERROR) . "\n", FILE_APPEND | LOCK_EX);
        }
        try {
            $result = parent::execute($sql);
            if ($result === false && $this->probePath !== '') {
                file_put_contents($this->probePath, json_encode(['failure' => [
                    'errno' => $this->connID instanceof \mysqli ? $this->connID->errno : null,
                    'error' => $this->connID instanceof \mysqli ? $this->connID->error : null,
                    'sql' => $sql,
                ]], JSON_THROW_ON_ERROR) . "\n", FILE_APPEND | LOCK_EX);
            }
            return $result;
        } catch (\Throwable $e) {
            if ($this->probePath !== '') {
                file_put_contents($this->probePath, json_encode(['failure' => [
                    'class' => get_class($e), 'code' => $e->getCode(), 'message' => $e->getMessage(), 'sql' => $sql,
                ]], JSON_THROW_ON_ERROR) . "\n", FILE_APPEND | LOCK_EX);
            }
            throw $e;
        }
    }
}

// CI resolves the builder/result sibling classes from static::class, even for a
// transport-only subclass. Keep the real driver classes for every other operation.
class_alias(\CodeIgniter\Database\MySQLi\Builder::class, 'Pr3LockOrderProbeBuilder');
class_alias(\CodeIgniter\Database\MySQLi\Result::class, 'Pr3LockOrderProbeResult');

$config = new \Config\Database();
$errors = \Scripts\TestDatabasePreflight::validateConfigBeforeConnection($config->tests, $config->default);
if ($errors || (int) $config->tests['port'] !== 3307) { fwrite(STDERR, 'Unsafe test target'); exit(2); }
$db = new Pr3LockOrderProbeConnection($config->tests);
$db->initialize();
$ownership = \Scripts\TestDatabasePreflight::verifyOwnershipForReset($db);
$liveErrors = \Scripts\TestDatabasePreflight::validateLiveConnection($db);
if ($liveErrors || !$ownership['allowed']) { fwrite(STDERR, 'Disposable database identity/ownership validation failed.'); exit(2); }
$db->query('SET SESSION innodb_lock_wait_timeout = 8');
$input = json_decode(file_get_contents($argv[1]), true, 512, JSON_THROW_ON_ERROR);
\CodeIgniter\I18n\Time::setTestNow(\CodeIgniter\I18n\Time::parse('2030-01-07 06:00:00', 'UTC'));
\App\Libraries\ApiRuntimeContext::clear();
session()->set(['user_id' => $input['actor'], 'identity' => $input['email'], 'active_clinic_id' => $input['clinic'],
    'global_mode' => false, 'is_impersonating' => false, 'impersonated_clinic_id' => null]);
$db->probePath = $argv[2];
file_put_contents($argv[3], 'ready');
try {
    if ($input['operation'] === 'hours') {
        $result = (new \App\Services\ClinicSchedulingConfigurationService($db))->configureHours($input['clinic'], $input['payload'], $input['actor']);
    } else {
        $result = (new \App\Services\AppointmentSchedulingService($db))->createForClinic($input['clinic'], $input['payload'], $input['actor']);
    }
    echo json_encode($result, JSON_THROW_ON_ERROR);
} catch (\Throwable $e) {
    fwrite(STDERR, get_class($e) . ': ' . $e->getMessage());
    exit(3);
} finally {
    while ($db->transDepth > 0) { $db->transRollback(); }
    \CodeIgniter\I18n\Time::setTestNow();
    $db->close();
}
