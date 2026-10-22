<?php

declare(strict_types=1);
$bootstrap = getopt('', ['bootstrap:'])['bootstrap'] ?? '';
if (PHP_SAPI !== 'cli' || !is_file($bootstrap)) {
    exit(2);
}require $bootstrap;
$identity = $db->query('SELECT DATABASE() db,@@port port,@@datadir datadir')->getRowArray();
if ($identity['db'] !== 'm2c_finance_acceptance' || (int)$identity['port'] !== 3307 || !str_contains(str_replace('\\', '/', $identity['datadir']), 'denta-m2c-20261021/disposable-mariadb/data')) {
    throw new RuntimeException('Owned disposable target required');
}
$cfg = config('Database')->default;
$cfg['DBPrefix'] = 'contact_'.bin2hex(random_bytes(5)).'_';
$test = Config\Database::connect($cfg, false);
$test->initialize();
$prefix = $cfg['DBPrefix'];
foreach (['clinics' => 'id INT UNSIGNED PRIMARY KEY,status VARCHAR(20)','users' => 'id INT UNSIGNED PRIMARY KEY','patients' => 'id INT UNSIGNED PRIMARY KEY,clinic_id INT UNSIGNED,deleted_at DATETIME NULL','appointments' => 'id INT PRIMARY KEY,clinic_id INT UNSIGNED,patient_id INT UNSIGNED,deleted_at DATETIME NULL','activity_logs' => 'id INT AUTO_INCREMENT PRIMARY KEY,clinic_id INT,user_id INT,action VARCHAR(50),entity_type VARCHAR(50),entity_id INT,description TEXT,created_at DATETIME'] as $table => $fields) {
    $test->query('CREATE TABLE `'.$prefix.$table.'`('.$fields.') ENGINE=InnoDB');
}
$test->table('clinics')->insertBatch([['id' => 1,'status' => 'active'],['id' => 2,'status' => 'active']]);
$test->table('users')->insert(['id' => 1]);
$test->table('patients')->insertBatch([['id' => 1,'clinic_id' => 1],['id' => 2,'clinic_id' => 2]]);
$test->table('appointments')->insertBatch([['id' => 1,'clinic_id' => 1,'patient_id' => 1],['id' => 2,'clinic_id' => 2,'patient_id' => 2],['id' => 3,'clinic_id' => 1,'patient_id' => 2]]);
require $root.'/app/Database/Migrations/2026-10-22-180000_CreateAppointmentContactNotes.php';
$migration = new App\Database\Migrations\CreateAppointmentContactNotes(Config\Database::forge($test));
try {
    $migration->up();
} catch (Throwable$e) {
    throw new RuntimeException($e->getMessage().' '.json_encode($test->error()).' '.(string)$test->getLastQuery());
}
$migration->up();
class ContactPermissionFixture extends App\Services\PermissionService
{
    public function hasPermission($userId, $module, $action)
    {
        return true;
    }
}
class ContactSqlService extends App\Services\AppointmentSchedulingService
{
    public bool$failAudit = false;
    public bool$deny = false;
    public function __construct($db)
    {
        $this->db = $db;
        $this->permissionService = new ContactPermissionFixture($db);
    }
    protected function validateContextAuth(int$clinicId, int$actorId, string$context): ?array
    {
        return$this->deny ? ['success' => false] : null;
    }
    protected function checkSubscriptionStanding(int$clinicId): ?array
    {
        return null;
    }
    protected function revalidateActorUnderLock(int$clinicId, int$actorId, string$action): ?array
    {
        return$this->deny ? ['success' => false] : null;
    }
    protected function writeActivityLog(int$clinicId, int$actorId, string$action, int$appointmentId, string$description, string$now): void
    {
        if ($this->failAudit) {
            throw new RuntimeException('Injected audit failure');
        }if (!$this->db->table('activity_logs')->insert(['clinic_id' => $clinicId,'user_id' => $actorId,'action' => $action,'entity_type' => 'appointment','entity_id' => $appointmentId,'description' => $description,'created_at' => $now])) {
            throw new RuntimeException('Audit failed');
        }
    }
}
$n = 0;
function ck(bool$v, string$why): void
{
    global$n;
    if (!$v) {
        throw new RuntimeException($why);
    }$n++;
}
$s = new ContactSqlService($test);
$missingCfg = $cfg; $missingCfg['DBPrefix'] = $prefix.'absent_';
$missingDb = Config\Database::connect($missingCfg, false);
config('ClinicOperations')->manualAppointmentContacts = true;
try {
    (new ContactSqlService($missingDb))->contactHistory(1, 1, 1);
    throw new RuntimeException('Missing schema was presented as empty history');
} catch (DomainException $e) {
    ck($e->getMessage() === 'SCHEMA_NOT_READY', 'Enabled missing schema fails closed');
}

$input = ['request_key' => str_repeat('a', 32),'note' => "Factual synthetic note <script>no execution</script>\nLine two"];
config('ClinicOperations')->manualAppointmentContacts = false;
ck($s->appendContactNote(1, 1, 1, $input)['code'] === 'DISABLED', 'Default OFF denies');
config('ClinicOperations')->manualAppointmentContacts = true;
$r = $s->appendContactNote(1, 1, 1, $input);
ck($r['success'] && !$r['replayed'], 'Append success');
$id = $r['id'];
$before = $test->table('appointment_contact_notes')->get()->getResultArray();
$r = $s->appendContactNote(1, 1, 1, $input);
ck($r['success'] && $r['replayed'] && $r['id'] === $id, 'Exact retry unchanged ID');
ck($before === $test->table('appointment_contact_notes')->get()->getResultArray(), 'History verbatim unchanged');
$r = $s->appendContactNote(1, 1, 1, ['request_key' => $input['request_key'],'note' => 'Different']);
ck($r['code'] === 'REPLAY_CONFLICT', 'Conflicting retry denied');
foreach ([[1,2],[1,3],[2,1]] as [$clinic,$appointment]) {
    ck(!$s->appendContactNote($clinic, $appointment, 1, ['request_key' => bin2hex(random_bytes(16)),'note' => 'Foreign'])['success'], 'Cross-clinic parent isolation');
}
foreach ([['note' => []],['note' => str_repeat('x', 2001)],['note' => ' '],['request_key' => str_repeat('A', 32)],['request_key' => []]] as $bad) {
    ck($s->appendContactNote(1, 1, 1, array_replace($input, $bad))['code'] === 'INVALID_INPUT', 'Malformed note or case-mismatched key denied');
}
$s->deny = true;
ck($s->appendContactNote(1, 1, 1, $input)['code'] === 'FORBIDDEN', 'Authority denial propagated');
$s->deny = false;
$s->failAudit = true;
$r = $s->appendContactNote(1, 1, 1, ['request_key' => str_repeat('b', 32),'note' => 'Rollback']);
ck(!$r['success'] && $test->table('appointment_contact_notes')->countAllResults() === 1, 'Required audit failure rolls ledger back');
$s->failAudit = false;
$other = Config\Database::connect($cfg, false);
$other->initialize();
$other->query('SET SESSION innodb_lock_wait_timeout=1');
$otherService = new ContactSqlService($other);
$test->transBegin();
$test->query('SELECT id FROM `'.$prefix.'patients` WHERE id=1 FOR UPDATE');
$started = microtime(true);
$r = $otherService->appendContactNote(1, 1, 1, ['request_key' => str_repeat('c', 32),'note' => 'Concurrent']);
ck(!$r['success'] && microtime(true) - $started < 1.0, 'Patient NOWAIT contention fails without long locks');
$test->transRollback();
$other = Config\Database::connect($cfg, false);
$other->initialize();
$otherService = new ContactSqlService($other);
$r = $otherService->appendContactNote(1, 1, 1, ['request_key' => str_repeat('c', 32),'note' => 'Concurrent']);
ck($r['success'], 'Retry after contention succeeds');
$repeat = $s->appendContactNote(1, 1, 1, ['request_key' => str_repeat('c', 32),'note' => 'Concurrent']);
ck($repeat['success'] && $repeat['id'] === $r['id'] && $repeat['replayed'], 'Independent connection retry deduplicates');
foreach (['patients','appointments','users','clinics'] as $table) {
    ck($test->table($table)->where('id', 1)->delete() === false, 'RESTRICT preserves referenced '.$table);
}
try {
    $migration->down();
    throw new RuntimeException('Populated down accepted');
} catch (RuntimeException$e) {
    ck(str_contains($e->getMessage(), 'retained evidence'), 'Populated migration rollback refused');
}
for ($i = 0;$i < 27;$i++) {
    ck($s->appendContactNote(1, 1, 1, ['request_key' => bin2hex(random_bytes(16)),'note' => 'Page '.$i])['success'], 'History pagination fixture');
}
$first = $s->contactHistory(1, 1, 1);
$second = $s->contactHistory(1, 1, 1, $first['next']);
ck(count($first['items']) === 25 && count($second['items']) === 4 && $second['next'] === null, 'All records traversable bounded cursor');
ck($s->contactHistory(2, 1, 1)['items'] === [], 'History tenant isolation');
foreach ($test->table('activity_logs')->get()->getResultArray() as $a) {
    ck(!str_contains($a['description'], 'Factual') && !str_contains($a['description'], '<script>'), 'No factual content in notifications audit');
}
$en = require $root.'/app/Language/en/AppointmentContact.php';
$ar = require $root.'/app/Language/ar/AppointmentContact.php';
ck(array_keys($en) === array_keys($ar) && !str_contains(implode('',$ar),'??') && preg_match('/\p{Arabic}/u',$ar['title']) === 1,'Arabic source valid and paired');
echo json_encode(['status' => 'PASS','checks' => $n,'prefix' => $prefix,'scope' => 'Real MariaDB migration/ledger/transactions/FKs/two-connection NOWAIT; inherited auth boundaries overridden in fixture, routed acceptance separate'],JSON_PRETTY_PRINT),PHP_EOL;
