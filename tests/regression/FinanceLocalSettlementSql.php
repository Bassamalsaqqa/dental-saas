<?php
declare(strict_types=1);
/** CLI-only real SQL regression. Requires an explicitly supplied bootstrap and an owned local test DB.
 * Adds only synthetic fixtures; never imports or reads business records. HTTP/session/RBAC are separate.
 */
if (PHP_SAPI !== 'cli') exit(2);
try {
$bootstrap = getenv('DENTA_REPLAY_TEST_BOOTSTRAP');
if (!$bootstrap || !is_file($bootstrap)) throw new RuntimeException('Explicit isolated bootstrap required.');
require $bootstrap;
require_once dirname(__DIR__,2).'/scripts/TestDatabasePreflight.php';
$config = new Config\Database();
$errors = Scripts\TestDatabasePreflight::validateConfigBeforeConnection($config->tests, $config->default);
if ($errors) throw new RuntimeException(implode('; ', $errors));
$db = Config\Database::connect('tests', false);
$errors = Scripts\TestDatabasePreflight::validateLiveConnection($db);
$owned = Scripts\TestDatabasePreflight::verifyOwnershipForReset($db);
if ($errors || !$owned['allowed'] || !$db->tableExists('_test_db_marker')) throw new RuntimeException('Isolated ownership validation failed.');
$checks = [];
$assert = static function (bool $ok, string $name) use (&$checks): void { if (!$ok) throw new RuntimeException($name); $checks[] = $name; };
require_once dirname(__DIR__,2).'/tests/_support/Database/LegacyBaseSchema.php';
$appointmentDdl = array_values(array_filter(Tests\Support\Database\LegacyBaseSchema::DDL_STATEMENTS, static fn($ddl)=>str_contains($ddl,'CREATE TABLE IF NOT EXISTS `appointments`')))[0];
$db->query(str_replace('CREATE TABLE IF NOT EXISTS `appointments`','CREATE TEMPORARY TABLE `local_foundation_appointments`',$appointmentDdl));
$column = $db->query("SHOW COLUMNS FROM local_foundation_appointments LIKE 'clinic_id'")->getRowArray();
$assert(str_contains($column['Type'],'unsigned') && $column['Null']==='NO','Fresh synthetic appointment foundation meets strict tenant-column precondition');
$db->query('DROP TEMPORARY TABLE local_foundation_appointments');
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
$fixture = ['clinic_id'=>1,'transaction_id'=>'LOCAL-'.bin2hex(random_bytes(5)),'transaction_type'=>'expense','amount'=>1000,'amount_original'=>1000,'total_amount'=>1000,'currency'=>'USD','exchange_rate'=>1,'payment_method'=>'cash','liquidity_account'=>'safe_cash','service_type'=>'other','expense_category'=>'salaries','counterparty_name'=>'Synthetic salary payee','transaction_date'=>'2026-11-01','amount_paid'=>0,'balance_due'=>1000,'payment_status'=>'pending'];
if (!$db->table('finances')->insert($fixture)) throw new RuntimeException('Synthetic fixture failed');
$seed = $db->table('finances')->where('id', $db->insertID())->get()->getRowArray();
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

$db->table('finances')->insert(array_merge($fixture,['transaction_id'=>'PYP-'.bin2hex(random_bytes(5)),'transaction_type'=>'invoice','patient_id'=>1,'expense_category'=>null,'counterparty_name'=>null]));
$payoutFinanceId=(int)$db->insertID();
$payoutFixture = ['clinic_id'=>$clinic,'finance_id'=>$payoutFinanceId,'patient_id'=>1,'provider_type'=>'guest','provider_name_snapshot'=>'Synthetic provider','split_mode'=>'percent','split_value'=>30,'gross_amount'=>1000,'provider_share_amount'=>300,'clinic_share_amount'=>700,'provider_paid_amount'=>0,'provider_balance_due'=>300,'status'=>'unpaid','currency'=>'ILS','created_by'=>$actor];
$db->table('provider_payouts')->insert($payoutFixture);
$payoutId = (int)$db->insertID();
$context = $guard->issue($clinic,$actor,'provider_payment',[$payoutId]);
$assert($context['currency']==='ILS','SQL context uses targeted payout currency despite USD parent');
$mutation = new App\Services\FinanceMutationService($db);
$pay = static fn($wire,$amount,$notes='Synthetic partial') => $mutation->recordPayout($clinic,$payoutId,$actor,$amount,'cash','safe_cash','2026-11-02',$notes,$wire);
$first = $pay($context,100);
$rows = static fn() => [$db->table('provider_payouts')->where('id',$payoutId)->get()->getRowArray(),$db->table('provider_payout_payments')->where('provider_payout_id',$payoutId)->get()->getResultArray()];
$partial = $rows();
$assert($partial[0]['status']==='partial' && (float)$partial[0]['provider_balance_due']===200.0 && count($partial[1])===1 && $partial[1][0]['paid_date']==='2026-11-02','SQL partial provider payout stores balance and dated receipt');
$retry = $pay($context,100);
$assert($retry['request_replayed']===true && $rows()===$partial,'Provider exact retry creates no duplicate payment');
try {$pay($context,101);throw new LogicException('Changed replay accepted');}catch(App\Services\FinanceSettlementConflict $e){$assert($e->reason==='SETTLEMENT_IDENTITY_CONFLICT','Changed provider replay rejected');}
$assert($rows()===$partial,'Changed replay preserves provider history');
$next = $guard->issue($clinic,$actor,'provider_payment',[$payoutId]);
try {$pay($next,201);throw new LogicException('Overpayment accepted');}catch(DomainException $e){$assert(str_contains($e->getMessage(),'exceeds'),'Provider overpayment rejected');}
$assert($rows()===$partial,'Overpayment rolls back receipt and balance');
$pay($next,200,'Synthetic final');
$full = $rows();
$assert($full[0]['status']==='paid' && (float)$full[0]['provider_balance_due']===0.0 && count($full[1])===2 && array_sum(array_column($full[1],'amount'))===300.0,'SQL final provider payment reconciles two dated receipts');
try {$guard->issue($clinic+999,$actor,'provider_payment',[$payoutId]);throw new LogicException('Cross-tenant target accepted');}catch(DomainException $e){$assert(true,'Cross-tenant provider context rejected');}
$assert($rows()===$full,'Cross-tenant rejection preserves durable history');
helper('settings');
$controller=(new ReflectionClass(App\Controllers\Finance::class))->newInstanceWithoutConstructor();
(new ReflectionProperty(App\Controllers\Finance::class,'db'))->setValue($controller,$db);
$ledger=(new ReflectionMethod(App\Controllers\Finance::class,'buildFinancialLedger'))->invoke($controller,$clinic,'2026-11-01','2026-11-30');
$providerRows=array_values(array_filter($ledger['rows'],static fn($row)=>(int)($row['provider_payout_id']??0)===$payoutId));
$assert(count($providerRows)===2 && array_sum(array_column($providerRows,'amount_original'))===300.0,'Active report SQL reads both provider receipts exactly once');
$assert(array_unique(array_column($providerRows,'finance_id'))===[$payoutFinanceId],'Active report retains canonical provider transaction history link');
$expenseRows=array_values(array_filter($ledger['rows'],static fn($row)=>(int)$row['finance_id']===$id && $row['event_type']==='expense'));
$assert(count($expenseRows)===1 && (float)$expenseRows[0]['amount_base']===20.0 && $expenseRows[0]['event_source']==='expense_settlement','Salary report SQL uses receipt once and excludes cached parent duplication');
$assert((new ReflectionMethod(App\Controllers\Finance::class,'buildFinancialLedger'))->invoke($controller,$clinic+999,'2026-11-01','2026-11-30')['rows']===[],'Active report SQL excludes other tenant records');
echo json_encode(['passed'=>count($checks),'checks'=>$checks,'scope'=>'Local MariaDB SQL services only; HTTP CSRF/session/RBAC and production remain separate'],JSON_PRETTY_PRINT),PHP_EOL;

} catch (Throwable $error) {
    fwrite(STDERR,get_class($error).": ".$error->getMessage().PHP_EOL);
    exit(1);
}
