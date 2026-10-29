<?php
declare(strict_types=1);
/**
 * Run only with an explicitly owned disposable bootstrap, never .env defaults.
 * Bootstrap must set $settlementTestDb and DENTA_SETTLEMENT_DISPOSABLE=true.
 * It must apply only the reviewed request migration before this test.
 * New fixture rows are retained for evidence; original rows are never edited.
 */
if (PHP_SAPI !== 'cli') exit(2);
$bootstrap = getenv('DENTA_REPLAY_TEST_BOOTSTRAP');
if (!$bootstrap || !is_file($bootstrap)) throw new RuntimeException('Explicit disposable bootstrap required.');
require $bootstrap;
if (!defined('DENTA_SETTLEMENT_DISPOSABLE') || DENTA_SETTLEMENT_DISPOSABLE !== true || !isset($settlementTestDb)) throw new RuntimeException('Disposable ownership missing.');
$db = $settlementTestDb;
$identity = $db->query('SELECT DATABASE() db,@@port port,@@datadir datadir')->getRowArray();
if ((int)$identity['port'] !== 3307 || !str_contains($identity['db'], 'synthetic') || !str_contains(str_replace('\\', '/', $identity['datadir']), 'disposable-mariadb/data')) throw new RuntimeException('Unsafe database identity.');
$checks = [];
$assert = static function (bool $ok, string $name) use (&$checks): void { if (!$ok) throw new RuntimeException($name); $checks[] = $name; };
$guard = new App\Services\FinanceSettlementRequestService($db);
$guard->requireSchema();
require_once dirname(__DIR__,2).'/app/Database/Migrations/2026-10-29-180000_CreateFinanceSettlementRequests.php';
$db->transBegin();
$migration = new App\Database\Migrations\CreateFinanceSettlementRequests(Config\Database::forge($db));
foreach (['up','down'] as $direction) {
    try { $migration->$direction(); throw new LogicException('DDL accepted inside transaction'); }
    catch (RuntimeException $e) { if (!str_contains($e->getMessage(), 'outside a financial transaction')) throw $e; }
}
$assert((int)$db->transDepth === 1 && $db->transStatus(), 'DDL refuses an active transaction without implicit commit');
$db->transRollback();
$seed = $db->table('finances')->where('transaction_type', 'expense')->orderBy('id')->get()->getRowArray();
if (!$seed) throw new RuntimeException('Synthetic expense seed required.');
$seedBefore = $seed;
$clinic = (int)$seed['clinic_id'];
$actor = (int)$db->table('clinic_users')->where('clinic_id', $clinic)->orderBy('user_id')->get()->getRowArray()['user_id'];
if ($actor <= 0) throw new RuntimeException('Synthetic actor missing.');
unset($seed['id']);
$seed['transaction_id'] = 'REQ-' . bin2hex(random_bytes(6));
foreach (['amount', 'amount_original', 'balance_due'] as $field) if (array_key_exists($field, $seed)) $seed[$field] = '20.00';
if (array_key_exists('total_amount', $seed)) $seed['total_amount'] = '20.00';
$seed['amount_paid'] = '0.00'; $seed['payment_status'] = 'pending';
$seed['discount_amount'] = '0.00'; $seed['tax_amount'] = '0.00'; $seed['tax_percentage'] = '0.00';
$seed['description'] = 'Disposable settlement replay regression';
if (!$db->table('finances')->insert($seed)) throw new RuntimeException('Fixture insert failed.');
$id = (int)$db->insertID();
$wire = $guard->issue($clinic, $actor, 'mark_paid', [$id]);
$stale = $guard->issue($clinic, $actor, 'mark_paid', [$id]);
$state = static fn() => [
    $db->table('finances')->where('id', $id)->get()->getRowArray(),
    $db->table('finance_payments')->where('finance_id', $id)->get()->getResultArray(),
    $db->table('activity_logs')->where('entity_type', 'finance')->where('entity_id', $id)->get()->getResultArray(),
    $db->table('finance_settlement_requests')->where('request_key', $wire['request_key'])->get()->getResultArray(),
];
$before = $state();
$failing = new class($db) extends App\Services\FinanceSettlementService {
    protected function audit(int $clinicId, int $actorId, string $action, int $financeId, string $description, array $metadata): void { throw new RuntimeException('Injected audit failure after ledger insertion'); }
};
try { $failing->bulkSettle($clinic, [$id], $actor, [], $wire, 'mark_paid'); throw new LogicException('Fault not exercised'); }
catch (RuntimeException $e) { if ($e->getMessage() !== 'Injected audit failure after ledger insertion') throw $e; }
$assert($before === $state() && (int)$db->transDepth === 0, 'Real ledger insert rolls back request, parent, audit and receipt');
$service = new App\Services\FinanceSettlementService($db);
$first = $service->bulkSettle($clinic, [$id], $actor, [], $wire, 'mark_paid');
$after = $state();
$retry = $service->bulkSettle($clinic, [$id], $actor, [], $wire, 'mark_paid');
$assert($first['settled_count'] === 1 && $retry['request_replayed'] === true && $first['settlement_result'] === $retry['settlement_result'], 'Exact replay returns original durable result');
$assert($after === $state(), 'Exact replay preserves all durable financial rows');
foreach ([[$stale, [], 'SETTLEMENT_STALE'], [$wire, ['notes' => 'Changed financial request'], 'SETTLEMENT_IDENTITY_CONFLICT']] as [$input, $terms, $expected]) {
    try { $service->bulkSettle($clinic, [$id], $actor, $terms, $input, 'mark_paid'); throw new LogicException('Conflict accepted'); }
    catch (App\Services\FinanceSettlementConflict $e) { $assert($e->reason === $expected, $expected); }
}
$assert($after === $state(), 'Rejected stale/reused requests leave financial rows unchanged');
$assert($seedBefore === $db->table('finances')->where('id', $seedBefore['id'])->get()->getRowArray(), 'Original seed record preserved verbatim');
echo json_encode(['passed' => count($checks), 'checks' => $checks, 'fixture_id' => $id, 'scope' => 'SQL transaction/replay only; real HTTP concurrency, auth and browser evidence are separate'], JSON_PRETTY_PRINT), PHP_EOL;
