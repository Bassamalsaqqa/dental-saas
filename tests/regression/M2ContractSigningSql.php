<?php
declare(strict_types=1);
/** Explicit private MariaDB/storage acceptance harness. Retains all owned fixtures and evidence.
 * php tests/regression/M2ContractSigningSql.php --bootstrap=/home/u556956644/.denta-stage-20261017/m2-private-bootstrap.php
 * No HTTP authorization claim. Workers run this same file and connect through the same guarded bootstrap.
 */
if (PHP_SAPI !== 'cli') { exit(2); }
$options = getopt('', ['bootstrap:', 'worker:', 'run-dir:', 'contract:', 'patient:', 'locale:']);
$bootstrap = $options['bootstrap'] ?? '';
$privateExpected = '/home/u556956644/.denta-stage-20261017';
if ($bootstrap !== $privateExpected . '/m2-private-bootstrap.php' || realpath($bootstrap) !== $bootstrap || is_link($bootstrap)) {
    throw new RuntimeException('Explicit canonical M2 private bootstrap required');
}
require $bootstrap;
if (!isset($db) || $private !== $privateExpected || realpath(HOMEPATH) !== $privateExpected . '/m2-20261020-base6908df7'
    || $db->database !== 'u556956644_dentstage1017' || $db->username !== 'u556956644_dstage1017'
    || $db->hostname !== '127.0.0.1' || (int) $db->port !== 3306 || $db->getPrefix() !== ''
    || (int) $db->transDepth !== 0 || !$db->transStatus()) { throw new RuntimeException('Private synthetic connection/root rejected'); }
$identity = $db->query('SELECT guid,domain,synthetic_only FROM _denta_stage_identity WHERE id=1')->getRowArray();
$who = $db->query('SELECT DATABASE() db,CURRENT_USER() actor,VERSION() version')->getRowArray();
if (($identity['guid'] ?? '') !== 'b60809432ec1d915b0b975aef8484e43' || ($identity['domain'] ?? '') !== 'dentastaging.palsync.net'
    || (int) ($identity['synthetic_only'] ?? 0) !== 1 || $who['db'] !== $db->database
    || !str_starts_with($who['actor'], 'u556956644_dstage1017@') || stripos($who['version'], 'MariaDB') === false) {
    throw new RuntimeException('Synthetic MariaDB identity rejected');
}
$db->query('SET SESSION innodb_lock_wait_timeout=1');
helper(['url', 'settings']);
App\Libraries\ApiRuntimeContext::clear();
App\Libraries\ApiRuntimeContext::setClinicId(1);
App\Libraries\ApiRuntimeContext::setUserId(1);
session()->set(['user_id' => 1, 'active_clinic_id' => 1, 'global_mode' => false]);
const M2PNG = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+aG5kAAAAASUVORK5CYII=';
const M2NAME = 'Synthetic Consent موافقة تجريبية';
function m2require(bool $ok, string $message): void { if (!$ok) { throw new RuntimeException($message); } }
function m2record(string $dir, array $record): void {
    $handle = fopen($dir . '/manifest.jsonl', 'ab');
    m2require($handle !== false && flock($handle, LOCK_EX), 'Manifest lock failed');
    $line = json_encode($record, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES) . "\n";
    m2require(fwrite($handle, $line) === strlen($line) && fflush($handle), 'Manifest write failed');
    if (function_exists('fsync')) { m2require(fsync($handle), 'Manifest fsync failed'); }
    flock($handle, LOCK_UN); fclose($handle);
}
function m2wait(string $path): void {
    $until = microtime(true) + 25;
    while (!is_file($path)) { if (microtime(true) > $until) { throw new RuntimeException('Barrier timeout: ' . basename($path)); } usleep(20000); }
}
class M2Journal extends App\Services\ContractDocumentJournal {
    public string $fault = '';
    public array $published = [];
    public function __construct(public string $manifestDir, private $connection) {}
    public function publish(int $clinicId, int $contractId, string $html, array $metadata): array {
        m2require((int) $this->connection->transDepth === 0, 'Storage publication inside SQL transaction');
        if ($this->fault === 'storage') { throw new RuntimeException('M2_INJECT_STORAGE'); }
        $artifact = parent::publish($clinicId, $contractId, $html, $metadata);
        $this->published[] = $artifact;
        m2record($this->manifestDir, ['kind' => 'published', 'artifact' => $artifact]);
        return $artifact;
    }
    public function markCommitted(array $artifact, int $signatureId, int $attachmentId): void {
        m2require((int) $this->connection->transDepth === 0, 'Journal finalization inside SQL transaction');
        if ($this->fault === 'postcommit') { throw new RuntimeException('M2_INJECT_POSTCOMMIT'); }
        parent::markCommitted($artifact, $signatureId, $attachmentId);
    }
}
function m2sign($service, int $contract, int $patient, string $name = M2NAME): array {
    return $service->sign(1, $contract, $patient, $name, M2PNG, 'staff', 1, '127.0.0.1', 'M2 synthetic SQL acceptance',
        static function (array $data): string {
            $html = view('contracts/print', $data);
            m2require(str_contains($html, '<!DOCTYPE html>') && str_contains($html, M2NAME), 'Actual print view missing HTML/signature');
            return $html;
        });
}
function m2state($db, int $id): array {
    return ['contract' => $db->table('treatment_contracts')->where('id', $id)->get()->getRowArray(),
        'signatures' => $db->table('treatment_contract_signatures')->where('clinic_id', 1)->where('contract_id', $id)->orderBy('id')->get()->getResultArray(),
        'attachments' => $db->table('file_attachments')->where('clinic_id', 1)->where('entity_type', 'contract')->where('entity_id', $id)->where('purpose', 'contract_pdf')->orderBy('id')->get()->getResultArray(),
        'audits' => $db->table('activity_logs')->where('clinic_id', 1)->like('description', 'Treatment contract #' . $id . ' ', 'after')->orderBy('id')->get()->getResultArray()];
}
function m2reject(callable $fn, string $expected): void {
    try { $fn(); } catch (Throwable $e) { m2require(str_contains($e->getMessage(), $expected), 'Unexpected rejection: ' . $e->getMessage()); return; }
    throw new RuntimeException('Expected rejection: ' . $expected);
}
// Child processes never create fixtures. Their manifest must already name the committed fixture.
if (isset($options['worker'])) {
    $dir = $options['run-dir'] ?? '';
    m2require(preg_match('#^' . preg_quote($privateExpected, '#') . '/m2-sql-[0-9a-f]{16}$#D', $dir) === 1
        && realpath($dir) === $dir && !is_link($dir), 'Worker run directory rejected');
    $id = (int) ($options['contract'] ?? 0); $patient = (int) ($options['patient'] ?? 0); $name = $options['worker'];
    m2require(preg_match('/^[a-z0-9_]+$/D', $name) === 1, 'Worker name rejected');
    $owned = json_decode(file_get_contents($dir . '/fixtures.json'), true, 512, JSON_THROW_ON_ERROR);
    m2require(in_array($id, $owned['contracts'], true) && $patient === $owned['patient'], 'Worker unowned fixture rejected');
    $journal = new M2Journal($dir, $db); $service = new App\Services\ContractSigningService($db, $journal);
    service('language')->setLocale($options['locale'] ?? 'en');
    $service->phaseFn = static function (string $phase) use ($db, $dir, $name): void {
        if (in_array($phase, ['render', 'published'], true)) { m2require((int) $db->transDepth === 0, $phase . ' holds transaction'); }
        if ($phase === 'render') { file_put_contents($dir . '/' . $name . '.ready', 'ready', LOCK_EX); m2wait($dir . '/' . $name . '.go'); }
    };
    try { $result = m2sign($service, $id, $patient); echo json_encode(['ok' => true, 'signature' => $result['signature']['id'], 'attachment' => $result['attachment']['id']], JSON_THROW_ON_ERROR); }
    catch (Throwable $e) { echo json_encode(['ok' => false, 'error' => $e->getMessage()], JSON_THROW_ON_ERROR); exit(1); }
    exit;
}
$runDir = $privateExpected . '/m2-sql-' . bin2hex(random_bytes(8));
m2require(mkdir($runDir, 0700), 'Private run directory creation failed');
$originalFiles=[];
foreach ([WRITEPATH.'uploads',WRITEPATH.'contract-signing'] as $storageRoot) {
    if(!is_dir($storageRoot))continue;
    $iterator=new RecursiveIteratorIterator(new RecursiveDirectoryIterator($storageRoot,FilesystemIterator::SKIP_DOTS));
    foreach($iterator as $file){m2require(!$file->isLink(),'Preexisting storage symlink rejected');if($file->isFile())$originalFiles[$file->getPathname()]=hash_file('sha256',$file->getPathname());}
}
$checks = []; $fixtures = ['patient' => 0, 'contracts' => [], 'items' => [], 'snapshots' => []]; $children = [];
$check = static function (bool $ok, string $label) use (&$checks): void { m2require($ok, $label); $checks[] = $label; };
$tables = $db->listTables(); sort($tables); $baseline = []; $schema = [];
$sourceHashes=[]; foreach(['ContractSigningService','ContractDocumentJournal','OdontogramSvgSnapshotService'] as $sourceName){$sourceHashes[$sourceName]=hash_file('sha256',APPPATH.'Services/'.$sourceName.'.php');}
m2record($runDir,['kind'=>'actual_source_hashes','sha256'=>$sourceHashes]);
foreach ($tables as $table) {
    m2require(preg_match('/^[A-Za-z0-9_]+$/D', $table) === 1, 'Unsafe table identifier');
    $baseline[$table] = $db->table($table)->get()->getResultArray();
    $create = $db->query('SHOW CREATE TABLE `' . $table . '`')->getRowArray();
    // AUTO_INCREMENT advances for retained fixture inserts; column/index schema must remain identical.
    $schema[$table] = preg_replace('/ AUTO_INCREMENT=\d+/', '', (string) array_values($create)[1]);
}
m2record($runDir, ['kind' => 'baseline', 'table_count' => count($tables), 'row_count' => array_sum(array_map('count', $baseline)), 'schema_sha256' => hash('sha256', json_encode($schema, JSON_THROW_ON_ERROR))]);
$startChild = static function (string $name, int $id, string $locale = 'en') use ($bootstrap, $runDir, &$children, &$fixtures): void {
    $command = [PHP_BINARY, __FILE__, '--bootstrap=' . $bootstrap, '--worker=' . $name, '--run-dir=' . $runDir,
        '--contract=' . $id, '--patient=' . $fixtures['patient'], '--locale=' . $locale];
    $pipes = []; $process = proc_open($command, [0 => ['pipe','r'], 1 => ['file', $runDir . '/' . $name . '.out', 'w'], 2 => ['file', $runDir . '/' . $name . '.err', 'w']], $pipes);
    m2require(is_resource($process), 'Worker spawn failed'); fclose($pipes[0]); $children[$name] = $process;
};
$finishChild = static function (string $name) use (&$children, $runDir): array {
    $until = microtime(true) + 30;
    do { $status = proc_get_status($children[$name]); if (!$status['running']) { break; } usleep(20000); } while (microtime(true) < $until);
    m2require(!$status['running'], 'Worker completion timeout'); proc_close($children[$name]); unset($children[$name]);
    $output = file_get_contents($runDir . '/' . $name . '.out');
    m2require(is_string($output) && $output !== '', 'Worker failed: ' . file_get_contents($runDir . '/' . $name . '.err'));
    return json_decode($output, true, 512, JSON_THROW_ON_ERROR);
};
try {
    m2require($db->table('clinics')->where('id', 1)->countAllResults() === 1 && $db->table('users')->where('id', 1)->countAllResults() === 1, 'Read-only synthetic clinic/actor required');
    m2require($db->transBegin(), 'Fixture transaction begin');
    $insert = static function (string $table, array $data) use ($db): int {
        m2require($db->table($table)->insert($data), 'Fixture insert failed: ' . $table); return (int) $db->insertID();
    };
    $label = basename($runDir);
    $fixtures['patient'] = $insert('patients', ['clinic_id'=>1,'patient_id'=>$label,'first_name'=>'Synthetic','last_name'=>'Consent تجريبي','phone'=>'0000000000','gender'=>'other','date_of_birth'=>'1990-01-01']);
    $svgService = new App\Services\OdontogramSvgSnapshotService();
    $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="40" height="40"><text x="1" y="20">M2</text></svg>';
    $encoded = $svgService->encode($svg); $check($svgService->decode($encoded) === $svg, 'Existing SVG snapshot encode/decode API round trip');
    for ($i=0; $i<9; $i++) {
        $id = $insert('treatment_contracts', ['clinic_id'=>1,'patient_id'=>$fixtures['patient'],'status'=>'draft','currency'=>'USD','total_cost'=>'12.00','notes'=>$label.' '.$i,'terms_conditions'=>'Synthetic terms شروط','created_by'=>1]);
        $fixtures['contracts'][] = $id;
        $fixtures['items'][] = $insert('treatment_contract_items', ['clinic_id'=>1,'contract_id'=>$id,'description'=>'Synthetic procedure إجراء تجريبي','qty'=>1,'price'=>'12.00']);
        $fixtures['snapshots'][] = $insert('treatment_contract_odontogram_snapshots', ['clinic_id'=>1,'contract_id'=>$id,'svg_snapshot'=>$encoded,'data_snapshot_json'=>'[]']);
    }
    m2require(file_put_contents($runDir.'/fixtures.json', json_encode($fixtures, JSON_THROW_ON_ERROR), LOCK_EX) !== false, 'Fixture ownership manifest failed');
    m2record($runDir, ['kind'=>'fixture_ids_before_commit','ids'=>$fixtures]);
    m2require($db->transCommit(), 'Fixture commit failed');
    $journal = new M2Journal($runDir, $db); $service = new App\Services\ContractSigningService($db, $journal);
    $service->phaseFn = static function (string $phase) use ($db): void {
        if (in_array($phase,['render','published'],true)) { m2require((int)$db->transDepth===0, 'Slow phase holds SQL locks'); }
    };
    [$race,$arabic,$renderFail,$storageFail,$linkFail,$auditFail,$postFail,$editRace,$deleteRace] = $fixtures['contracts'];
    // Both processes capture the same draft then render concurrently, without a clinic lock.
    $startChild('sign_a',$race); $startChild('sign_b',$race,'ar');
    m2wait($runDir.'/sign_a.ready'); m2wait($runDir.'/sign_b.ready');
    m2require($db->transBegin(), 'Independent lock probe begin');
    m2require((bool)$db->query('SELECT id FROM clinics WHERE id=1 FOR UPDATE'), 'Render blocked independent clinic lock');
    m2require($db->transRollback(), 'Independent lock probe rollback');
    $check(true,'Two real PHP signers simultaneously render; independent connection locks clinic within 1s timeout');
    file_put_contents($runDir.'/sign_a.go','go'); file_put_contents($runDir.'/sign_b.go','go');
    $a=$finishChild('sign_a'); $b=$finishChild('sign_b');
    $state=m2state($db,$race);
    $check($a['ok'] && $b['ok'] && $a['signature']===$b['signature'] && $a['attachment']===$b['attachment']
        && count($state['signatures'])===1 && count($state['attachments'])===1 && count($state['audits'])===1,'Concurrent identical consent has one signature, attachment and audit winner');
    $raceEntries=array_values(array_filter($journal->entries(),static fn($e)=>(int)$e['contract_id']===$race));
    $raceStates=array_column($raceEntries,'state');sort($raceStates);
    $check($raceStates===['committed','unreferenced'],'Both real publications remain durable: one committed and one unreferenced concurrent loser');
    $retry=m2sign($service,$race,$fixtures['patient']);
    $check(m2state($db,$race)===$state && (int)$retry['signature']['id']===(int)$a['signature'] && (int)$retry['attachment']['id']===(int)$a['attachment'],'Identical retry returns same IDs without history changes');
    m2reject(static fn()=>m2sign($service,$race,$fixtures['patient'],'Conflicting consent'), 'SIGN_ERR_CONFLICT');
    $check(m2state($db,$race)===$state,'Conflicting retry preserves complete consent');
    service('language')->setLocale('ar'); $ar=m2sign($service,$arabic,$fixtures['patient']); service('language')->setLocale('en');
    foreach ([$retry,$ar] as $result) {
        $attachment=$result['attachment']; $path=WRITEPATH.'uploads/'.$attachment['file_path']; $html=file_get_contents($path);
        $check($attachment['mime_type']==='text/html' && filesize($path)===(int)$attachment['file_size']
            && hash_file('sha256',$path)===$attachment['file_hash'] && $service->verifyAttachment($attachment)
            && str_contains($html,M2NAME) && str_contains($html,'<svg'), 'Actual EN/AR print evidence has HTML MIME, exact file identity, SHA-256, size and frozen SVG');
    }
    // Deliberately corrupt only this run's signed fixture, prove rejection, restore exact owned row.
    foreach ([['treatment_contracts',$arabic,'terms_conditions','Tampered terms'],
        ['treatment_contract_items',$fixtures['items'][1],'description','Tampered procedure'],
        ['treatment_contract_odontogram_snapshots',$fixtures['snapshots'][1],'data_snapshot_json','[{"condition_type":"decay"}]']] as [$table,$ownedId,$field,$value]) {
        $original=$db->table($table)->where('id',$ownedId)->get()->getRowArray();
        m2require($db->table($table)->where('id',$ownedId)->update([$field=>$value]),'Owned tamper fixture update failed');
        $corrupt=m2state($db,$arabic);
        m2reject(static fn()=>m2sign($service,$arabic,$fixtures['patient']),'SIGN_ERR_PARTIAL_STATE');
        $check(m2state($db,$arabic)===$corrupt,'Tampered immutable '.$field.' retry fails closed without autorepair');
        m2require($db->table($table)->where('id',$ownedId)->update([$field=>$original[$field]]),'Owned fixture exact restoration failed');
    }
    $check(m2state($db,$arabic)['contract']['status']==='signed','Owned tamper probes retain signed evidence');
    $before=m2state($db,$renderFail); $service->renderHtmlFn=static function():string {throw new RuntimeException('M2_INJECT_RENDER');};
    m2reject(static fn()=>m2sign($service,$renderFail,$fixtures['patient']),'M2_INJECT_RENDER'); $service->renderHtmlFn=null;
    $check(m2state($db,$renderFail)===$before,'Render failure leaves draft and all SQL history unchanged');
    $before=m2state($db,$storageFail); $journal->fault='storage';
    m2reject(static fn()=>m2sign($service,$storageFail,$fixtures['patient']),'M2_INJECT_STORAGE'); $journal->fault='';
    $check(m2state($db,$storageFail)===$before,'Real-service publication fault leaves draft and no evidence rows');
    foreach ([$linkFail=>'link',$auditFail=>'audit'] as $id=>$phase) {
        $before=m2state($db,$id); $service->phaseFn=static function(string $p)use($phase):void {if($p===$phase)throw new RuntimeException('M2_INJECT_'.$phase);};
        m2reject(static fn()=>m2sign($service,$id,$fixtures['patient']),'M2_INJECT_'.$phase);
        $artifact=end($journal->published); $entry=array_values(array_filter($journal->entries(),static fn($e)=>$e['operation']===$artifact['operation']))[0]??null;
        $check(m2state($db,$id)===$before && $journal->verify($artifact) && ($entry['state']??'')==='published','Injected '.$phase.' failure rolls back actual SQL and retains durable published recovery evidence');
    }
    $service->phaseFn=null; $journal->fault='postcommit';
    m2reject(static fn()=>m2sign($service,$postFail,$fixtures['patient']),'SIGN_ERR_RECONCILIATION'); $journal->fault='';
    $state=m2state($db,$postFail); $artifact=end($journal->published);
    $check($state['contract']['status']==='signed' && count($state['signatures'])===1 && count($state['attachments'])===1 && count($state['audits'])===1,'Postcommit journal failure retains complete committed SQL consent');
    $journal->markCommitted($artifact,(int)$state['signatures'][0]['id'],(int)$state['attachments'][0]['id']);
    $entries=array_values(array_filter($journal->entries(),static fn($e)=>$e['operation']===$artifact['operation']));
    $check(($entries[0]['state']??'')==='committed' && m2state($db,$postFail)===$state,'Explicit journal reconciliation commits owned entry without altering SQL evidence');
    foreach ([$editRace=>'edit',$deleteRace=>'delete'] as $id=>$mode) {
        $worker='race_'.$mode; $startChild($worker,$id); m2wait($runDir.'/'.$worker.'.ready');
        $mutate=static function($connection,array $contract)use($mode,$label):bool {
            return $connection->table('treatment_contracts')->where('clinic_id',1)->where('id',$contract['id'])->update($mode==='edit'?['notes'=>$label.' changed']:['deleted_at'=>date('Y-m-d H:i:s')]);
        };
        if($mode==='edit')$service->updateDraftWithLock(1,$id,$mutate); else $service->deleteDraftWithLock(1,$id,$mutate);
        $before=m2state($db,$id); file_put_contents($runDir.'/'.$worker.'.go','go'); $result=$finishChild($worker);
        $check(!$result['ok'] && str_contains($result['error'],$mode==='edit'?'SIGN_ERR_CHANGED':'SIGN_ERR_NOT_FOUND')
            && m2state($db,$id)===$before && count($before['signatures'])===0 && count($before['attachments'])===0,'Actual two-process sign/'.$mode.' rejects stale capture without consent rows');
    }
    $before=m2state($db,$renderFail);
    m2reject(static fn()=>m2sign($service,$renderFail,$fixtures['patient']+1000000),'SIGN_ERR_NOT_FOUND');
    m2reject(static fn()=>$service->sign(2147483647,$renderFail,$fixtures['patient'],M2NAME,M2PNG,'staff',1,'127.0.0.1','M2',static fn()=>''),'SIGN_ERR_NOT_FOUND');
    $check(m2state($db,$renderFail)===$before,'Tenant and patient mismatch reject before rendering or evidence writes');
    $allowed=['patients','treatment_contracts','treatment_contract_items','treatment_contract_odontogram_snapshots','treatment_contract_signatures','file_attachments','activity_logs'];
    $afterTables=$db->listTables(); sort($afterTables); $check($afterTables===$tables,'All original tables retained; no schema tables created');
    foreach($tables as $table) {
        $create=$db->query('SHOW CREATE TABLE `'.$table.'`')->getRowArray();
        m2require(preg_replace('/ AUTO_INCREMENT=\d+/','',(string)array_values($create)[1])===$schema[$table],'Schema changed: '.$table);
        $after=$db->table($table)->get()->getResultArray(); $counts=[];
        foreach($after as $row){$hash=hash('sha256',json_encode($row,JSON_THROW_ON_ERROR));$counts[$hash]=($counts[$hash]??0)+1;}
        foreach($baseline[$table] as $row){$hash=hash('sha256',json_encode($row,JSON_THROW_ON_ERROR));m2require(($counts[$hash]??0)>0,'Preexisting row changed/deleted: '.$table);$counts[$hash]--;}
        if(!in_array($table,$allowed,true))m2require(count($after)===count($baseline[$table]),'Unexpected new history: '.$table);
        else {
            $oldHashes=[];foreach($baseline[$table] as $row)$oldHashes[hash('sha256',json_encode($row,JSON_THROW_ON_ERROR))]=true;
            foreach($after as $row){
                if(isset($oldHashes[hash('sha256',json_encode($row,JSON_THROW_ON_ERROR))]))continue;
                $owned=match($table){
                    'patients'=>(int)$row['id']===$fixtures['patient'],
                    'treatment_contracts'=>in_array((int)$row['id'],$fixtures['contracts'],true),
                    'treatment_contract_items'=>in_array((int)$row['id'],$fixtures['items'],true),
                    'treatment_contract_odontogram_snapshots'=>in_array((int)$row['id'],$fixtures['snapshots'],true),
                    'treatment_contract_signatures'=>in_array((int)$row['contract_id'],$fixtures['contracts'],true),
                    'file_attachments'=>$row['entity_type']==='contract'&&in_array((int)$row['entity_id'],$fixtures['contracts'],true),
                    'activity_logs'=>$row['entity_type']==='patient'&&(int)$row['entity_id']===$fixtures['patient'],
                    default=>false};
                m2require($owned&&(int)$row['clinic_id']===1,'New row outside manifest ownership: '.$table);
            }
        }
    }
    foreach($originalFiles as $path=>$hash)m2require(is_file($path)&&!is_link($path)&&hash_file('sha256',$path)===$hash,'Preexisting storage file changed: '.$path);
    $check(true,'Every preexisting uploads/journal file preserved byte for byte');
    $check(true,'Every preexisting row and all column/index schemas preserved; finance and all unrelated table counts unchanged');
    $check((int)$db->transDepth===0 && $db->transStatus(),'Final MariaDB connection healthy with no open transaction');
    m2record($runDir,['kind'=>'completed','checks'=>$checks]);
    echo json_encode(['status'=>'PASS','manifest_dir'=>$runDir,'retained_owned_ids'=>$fixtures,'checks'=>$checks,
        'limits'=>'Actual private MariaDB and storage service acceptance; HTTP route/filter authorization and browser visual layout are separate checks. All fixture rows/files are retained.'],JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_THROW_ON_ERROR),"\n";
} catch (Throwable $e) {
    foreach($children as $process){if(is_resource($process)){proc_terminate($process);proc_close($process);}}
    if((int)$db->transDepth>0)$db->transRollback();
    m2record($runDir,['kind'=>'failed','error'=>$e->getMessage(),'retained_owned_ids'=>$fixtures]);
    echo json_encode(['status'=>'FAIL','error'=>$e->getMessage(),'manifest_dir'=>$runDir,'retained_owned_ids'=>$fixtures,'checks'=>$checks],JSON_PRETTY_PRINT|JSON_THROW_ON_ERROR),"\n"; exit(1);
}
