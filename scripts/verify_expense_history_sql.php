<?php
declare(strict_types=1);

/**
 * Explicit-bootstrap, synthetic-only focused expense history SQL regression runner.
 * No automatic connection, migration, DDL, transport, or production access.
 * Retains newly owned synthetic rows and audit evidence.
 * Use the independent preservation
 * checkpoint before/after execution to verify all pre-existing rows remain unchanged.
 * Usage: php scripts/verify_expense_history_sql.php --bootstrap=/absolute/private/bootstrap.php
 */
if (PHP_SAPI !== 'cli') { exit(2); }
$options = getopt('', ['bootstrap:', 'verify-isolation']);
$bootstrap = $options['bootstrap'] ?? null;
$privateOps = '/home/u556956644/.denta-stage-20261017';
$expectedRoot = $privateOps . '/remediation-20261018-base97';
if (!is_string($bootstrap) || $bootstrap === '' || !is_file($bootstrap)
    || realpath($bootstrap) !== $bootstrap || is_link($bootstrap)
    || !str_starts_with($bootstrap, $privateOps . '/')
    || realpath($privateOps) !== $privateOps || is_link($privateOps)) {
    fwrite(STDERR, "Explicit canonical private synthetic bootstrap required.\n");
    exit(2);
}
require $bootstrap;
if (!isset($db) || !$db instanceof \CodeIgniter\Database\BaseConnection
    || !defined('HOMEPATH') || realpath(HOMEPATH) !== $expectedRoot
    || realpath($expectedRoot) !== $expectedRoot || is_link($expectedRoot)
    || $db->database !== 'u556956644_dentstage1017'
    || $db->username !== 'u556956644_dstage1017'
    || $db->hostname !== '127.0.0.1' || (int) $db->port !== 3306
    || $db->getPrefix() !== '' || (int) $db->transDepth !== 0
    || $db->transStatus() === false) {
    throw new RuntimeException('Unverified private synthetic staging connection or root.');
}
$query = static function (string $sql, array $params = []) use ($db) {
    $result = $db->query($sql, $params);
    if ($result === false) { throw new RuntimeException('Synthetic preflight query failed.'); }
    return $result;
};
$identity = $query('SELECT guid, domain, synthetic_only FROM _denta_stage_identity WHERE id = 1')->getRowArray();
$connection = $query('SELECT DATABASE() AS db, CURRENT_USER() AS actor')->getRowArray();
if (($identity['guid'] ?? '') !== 'b60809432ec1d915b0b975aef8484e43'
    || ($identity['domain'] ?? '') !== 'dentastaging.palsync.net'
    || (int) ($identity['synthetic_only'] ?? 0) !== 1
    || ($connection['db'] ?? '') !== $db->database
    || !str_starts_with((string) ($connection['actor'] ?? ''), 'u556956644_dstage1017@')) {
    throw new RuntimeException('Synthetic marker or authenticated identity mismatch.');
}
$grants = $query('SHOW GRANTS')->getResultArray();
if ($grants === []) { throw new RuntimeException('Missing staging grants evidence.'); }
foreach ($grants as $row) {
    $grant = str_replace('\\_', '_', (string) reset($row));
    if (str_contains($grant, 'WITH GRANT OPTION')
        || !preg_match('/ ON (`[^`]+`|\*)\.(`[^`]+`|\*) TO /', $grant, $match)
        || ($match[1] === '*' && !str_starts_with($grant, 'GRANT USAGE ON *.* '))
        || ($match[1] !== '*' && trim($match[1], '`') !== $db->database)) {
        throw new RuntimeException('Excessive or unrecognized staging grants.');
    }
}
foreach ($query("SELECT setting_value FROM settings WHERE setting_key = 'dynamic_doctor_scheduling_enabled'")->getResultArray() as $setting) {
    if (!in_array(strtolower(trim((string) $setting['setting_value'])), ['0', 'false', ''], true)) {
        throw new RuntimeException('Dynamic scheduling must remain disabled.');
    }
}
$patient = $query("SELECT id FROM patients WHERE clinic_id = 1 AND patient_id LIKE 'STG\\_%' ORDER BY id LIMIT 1")->getRowArray();
if (!$patient) { throw new RuntimeException('Required synthetic clinic patient missing.'); }
if (array_key_exists('verify-isolation', $options)) {
    echo json_encode(['status' => 'PASS', 'synthetic_only' => true, 'isolation_verified' => true,
        'fixtures_executed' => false], JSON_THROW_ON_ERROR) . PHP_EOL;
    exit(0);
}
helper('settings');
session()->set(['active_clinic_id' => 1, 'user_id' => 1, 'global_mode' => false]);
require_once __DIR__ . '/fixtures/ExpenseHistorySqlFixture.php';
$result = ExpenseHistorySqlFixture::run($db, 1, 1, (int) $patient['id'], 2);
if ((int) $db->transDepth !== 0 || !$db->transStatus()) { throw new RuntimeException('Expense fixture left an unsafe connection.'); }
echo json_encode(['status'=>'PASS', 'synthetic_only'=>true, 'expense_history'=>$result], JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR) . PHP_EOL;
