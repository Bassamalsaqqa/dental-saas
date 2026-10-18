<?php
declare(strict_types=1);
/** Reviewer-owned actual-controller parity. Private synthetic DB only; no routed-auth claim. */
if (PHP_SAPI !== 'cli') exit(2);
$bootstrap = $argv[1] ?? '';
$privateExpected = '/home/u556956644/.denta-stage-20261017';
if (!is_string($bootstrap) || !str_starts_with($bootstrap, $privateExpected . '/')
    || !is_file($bootstrap) || is_link($bootstrap) || realpath($bootstrap) !== $bootstrap) {
    throw new RuntimeException('Canonical private guarded bootstrap required.');
}
require $bootstrap;
if ($private !== $privateExpected || realpath($private) !== $private || !defined('HOMEPATH')
    || !str_starts_with((string) realpath(HOMEPATH), $private . '/remediation-20261018-')
    || $db->database !== 'u556956644_dentstage1017' || $db->username !== 'u556956644_dstage1017'
    || $db->hostname !== '127.0.0.1' || (int) $db->port !== 3306 || $db->getPrefix() !== ''
    || (int) $db->transDepth !== 0 || !$db->transStatus()) {
    throw new RuntimeException('Private synthetic connection rejected.');
}
$identity = $db->query('SELECT guid,domain,synthetic_only FROM _denta_stage_identity WHERE id=1')->getRowArray();
if (($identity['guid'] ?? '') !== 'b60809432ec1d915b0b975aef8484e43'
    || ($identity['domain'] ?? '') !== 'dentastaging.palsync.net' || (int) ($identity['synthetic_only'] ?? 0) !== 1) {
    throw new RuntimeException('Synthetic identity rejected.');
}
foreach ($db->query('SHOW GRANTS')->getResultArray() as $row) {
    $grant = str_replace('\\_', '_', (string) reset($row));
    if (str_contains($grant, 'WITH GRANT OPTION')
        || (str_contains($grant, ' ON *.* ') && !str_starts_with($grant, 'GRANT USAGE ON *.* '))
        || (preg_match('/ ON `([^`]+)`\./', $grant, $match) && $match[1] !== $db->database)) {
        throw new RuntimeException('Excessive synthetic grants.');
    }
}
helper(['url', 'form']);
$db->query('SET SESSION innodb_lock_wait_timeout=5');
session()->set(['active_clinic_id'=>1,'user_id'=>1,'global_mode'=>false]);
$run='S0CV_'.bin2hex(random_bytes(5));$checks=[];$created=[];
function verifyS0(bool $condition,string $label):void {global $checks;if(!$condition)throw new RuntimeException($label);$checks[]=$label;}
function apiS0(array $payload,$db):App\Controllers\Api\Finance {
    App\Libraries\ApiRuntimeContext::setClinicId(1);App\Libraries\ApiRuntimeContext::setUserId(1);
    $request=new CodeIgniter\HTTP\IncomingRequest(config('App'),Config\Services::uri(null,false),json_encode($payload,JSON_THROW_ON_ERROR),new CodeIgniter\HTTP\UserAgent());
    $request->setHeader('Content-Type','application/json');Config\Services::injectMock('request',$request);
    $reflection=new ReflectionClass(App\Controllers\Api\Finance::class);$controller=$reflection->newInstanceWithoutConstructor();
    foreach(['model'=>new App\Models\FinanceModel($db),'request'=>$request,'response'=>Config\Services::response(null,false)] as $key=>$value) $reflection->getProperty($key)->setValue($controller,$value);
    return $controller;
}
function browserS0(array $payload,$db):App\Controllers\Finance {
    App\Libraries\ApiRuntimeContext::clear();
    $request=new CodeIgniter\HTTP\IncomingRequest(config('App'),Config\Services::uri(null,false),null,new CodeIgniter\HTTP\UserAgent());
    $request->setMethod('POST');$request->setGlobal('post',$payload);$request->setHeader('Referer','https://dentastaging.palsync.net/finance');
    Config\Services::injectMock('request',$request);
    $reflection=new ReflectionClass(App\Controllers\Finance::class);$controller=$reflection->newInstanceWithoutConstructor();
    foreach(['financeModel'=>new App\Models\FinanceModel($db),'db'=>$db,'request'=>$request,'response'=>Config\Services::response(null,false)] as $key=>$value) $reflection->getProperty($key)->setValue($controller,$value);
    return $controller;
}
function financeS0($db,int $id):array {return $db->table('finances')->where('clinic_id',1)->where('id',$id)->get()->getRowArray() ?? [];}
function auditsS0($db,int $id):array {return $db->table('activity_logs')->where('clinic_id',1)->where('entity_type','finance')->where('entity_id',$id)->orderBy('id')->get()->getResultArray();}
try {
    $patient=$db->table('patients')->select('id')->where('clinic_id',1)->like('patient_id','STG_','after')->get()->getRowArray();
    if(!$patient)throw new RuntimeException('Synthetic patient required.');
    $attemptsBefore=(int)$db->table('notification_attempts')->countAllResults();
    $notificationsBefore=(int)$db->table('notifications')->countAllResults();
    $payload=['patient_id'=>(int)$patient['id'],'transaction_type'=>'invoice','amount'=>50,'amount_original'=>50,'currency'=>'ILS','exchange_rate'=>1,'payment_method'=>'cash','payment_status'=>'pending','description'=>$run,'created_by'=>2147483000,'balance_due'=>999,'total_amount'=>999];
    $response=apiS0($payload,$db)->create();$body=json_decode($response->getBody(),true,512,JSON_THROW_ON_ERROR);
    if(isset($body['id']))$created[]=(int)$body['id'];
    verifyS0($response->getStatusCode()===201,'Actual API creates a valid invoice in its owned audited transaction');
    $id=(int)($body['id']??0);$before=financeS0($db,$id);$audit=auditsS0($db,$id);
    verifyS0($id>0&&(int)$before['clinic_id']===1&&(int)$before['patient_id']===(int)$patient['id'],'Created invoice uses active tenant and validated patient identity');
    verifyS0((int)$before['created_by']===1&&count($audit)===1&&(int)$audit[0]['user_id']===1&&$audit[0]['action']==='create','Creation uses server actor and commits exactly one creation audit');
    verifyS0((float)$before['total_amount']===50.0&&(float)$before['balance_due']===50.0&&(float)$before['amount_paid']===0.0,'Server-calculated total and unpaid balance ignore forged computed fields');
    verifyS0((int)$db->transDepth===0&&$db->transStatus(),'Successful controller creation leaves no open transaction');
    $count=(int)$db->table('finances')->countAllResults();
    $response=apiS0(array_replace($payload,['payment_status'=>'paid','amount_paid'=>50]),$db)->create();
    verifyS0($response->getStatusCode()===400&&(int)$db->table('finances')->countAllResults()===$count,'Actual API rejects forged paid-invoice creation without insertion');
    $foreign=$db->table('patients')->select('id')->where('clinic_id !=',1)->like('patient_id','STG_','after')->get()->getRowArray();
    if(!$foreign)throw new RuntimeException('Foreign synthetic patient required for tenant parity check.');
    $response=apiS0(array_replace($payload,['patient_id'=>(int)$foreign['id']]),$db)->create();
    verifyS0($response->getStatusCode()===400&&(int)$db->table('finances')->countAllResults()===$count,'Actual API rejects a foreign-clinic patient without insertion');
    foreach(['en','ar'] as $locale) {
        Config\Services::language()->setLocale($locale);session()->remove(['success','error']);
        $note=$locale==='ar'?'ملاحظة داخلية '.$run:'Internal note '.$run;
        $auditBefore=count(auditsS0($db,$id));
        $response=browserS0(['internal_note'=>$note],$db)->update($id);
        $afterAudit=auditsS0($db,$id);$last=end($afterAudit);$metadata=json_decode($last['metadata']??'null',true,512,JSON_THROW_ON_ERROR);
        verifyS0($response->getStatusCode()===302&&financeS0($db,$id)===$before,$locale.': browser internal note redirects while preserving every invoice column');
        verifyS0(count($afterAudit)===$auditBefore+1&&($metadata['internal_note']??null)===$note&&(int)$last['user_id']===1,$locale.': browser note is audit-only and server-actor attributed');
        verifyS0(session()->getFlashdata('success')===lang('Finance.internalNoteSaved'),$locale.': browser success message resolves through actual language service');
        $auditBefore=count($afterAudit);session()->remove(['success','error']);
        $response=browserS0(['amount_original'=>1,'patient_id'=>(int)$foreign['id'],'internal_note'=>'must not be recorded '.$run],$db)->update($id);
        verifyS0($response->getStatusCode()===302&&financeS0($db,$id)===$before&&count(auditsS0($db,$id))===$auditBefore,$locale.': browser protected-basis/identity tampering rejected atomically');
        verifyS0(session()->getFlashdata('error')===lang('Finance.invoiceChangeRejected'),$locale.': browser rejection message resolves through actual language service');
        $response=apiS0(['amount_original'=>1,'patient_id'=>(int)$foreign['id']],$db)->update($id);
        verifyS0($response->getStatusCode()===409&&financeS0($db,$id)===$before,$locale.': API and browser enforce identical invoice basis protection');
    }
    $source=file_get_contents(APPPATH.'Views/finance/edit.php');$open=strpos($source,'<fieldset disabled');$close=strpos($source,'</fieldset>',$open===false?0:$open);$note=strpos($source,'name="internal_note"');
    verifyS0($open!==false&&$close!==false&&$note!==false&&$open<$close&&$close<$note,'Edit source places invoice document controls in disabled fieldset and internal note outside it');
    foreach(['en','ar'] as $locale) {
        $lang=require APPPATH.'Language/'.$locale.'/Finance.php';
        foreach(['invoiceDocumentProtected','internalNote','internalNoteHelp','internalNoteSaved','invoiceChangeRejected','internalNoteFailed'] as $key)verifyS0(isset($lang[$key])&&is_string($lang[$key])&&trim($lang[$key])!==''&&preg_match('//u',$lang[$key])===1,$locale.': valid translated lifecycle text '.$key);
    }
    verifyS0((int)$db->table('notification_attempts')->countAllResults()===$attemptsBefore&&(int)$db->table('notifications')->countAllResults()===$notificationsBefore,'Controller verification produces no queued notifications or outbound attempts');
    verifyS0((int)$db->transDepth===0&&$db->transStatus(),'Controller parity leaves no open or failed transaction');
    echo json_encode(['status'=>'PASS','scope'=>'actual controller calls, not routed authentication or real browser transport','synthetic_only'=>true,'retained_new_fixture_ids'=>$created,'checks'=>$checks,'check_count'=>count($checks)],JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_THROW_ON_ERROR),"\n";
} catch(Throwable $error) {
    if((int)$db->transDepth>0)$db->transRollback();
    echo json_encode(['status'=>'FAIL','error'=>$error->getMessage(),'retained_new_fixture_ids'=>$created,'completed_checks'=>$checks],JSON_PRETTY_PRINT|JSON_THROW_ON_ERROR),"\n";exit(1);
} finally {App\Libraries\ApiRuntimeContext::clear();}
