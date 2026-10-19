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
helper(['url','form']);
$db->query('SET SESSION innodb_lock_wait_timeout=5');
App\Libraries\ApiRuntimeContext::clear();
session()->set(['active_clinic_id'=>1,'user_id'=>1,'global_mode'=>false]);
$run='FXBR_'.bin2hex(random_bytes(5));$checks=[];$id=0;
$check=static function(bool $ok,string $label)use(&$checks):void{if(!$ok)throw new RuntimeException($label);$checks[]=$label;};
$row=static fn():array=>$db->table('finances')->where('id',$GLOBALS['id'])->where('clinic_id',1)->get()->getRowArray()??[];
$payments=static fn():array=>$db->table('finance_payments')->where('clinic_id',1)->where('finance_id',$GLOBALS['id'])->orderBy('id')->get()->getResultArray();
$audits=static fn():array=>$db->table('activity_logs')->where('clinic_id',1)->where('entity_type','finance')->where('entity_id',$GLOBALS['id'])->orderBy('id')->get()->getResultArray();
$browser=static function(array $payload)use($db):App\Controllers\Finance{
    $request=new CodeIgniter\HTTP\IncomingRequest(config('App'),Config\Services::uri(null,false),null,new CodeIgniter\HTTP\UserAgent());
    $request->setMethod('POST');$request->setGlobal('post',$payload);$request->setGlobal('request',$payload);$request->setHeader('Content-Type','application/x-www-form-urlencoded');
    Config\Services::validation()->reset();$request->setHeader('Referer','https://dentastaging.palsync.net/finance');
    Config\Services::injectMock('request',$request);
    $treasury=new App\Services\FinanceTreasuryService();
    $logger=new App\Services\ActivityLogger();
    $lr=new ReflectionClass($logger);$lr->getProperty('activityLogModel')->setValue($logger,new App\Models\ActivityLogModel($db));
    $r=new ReflectionClass(App\Controllers\Finance::class);$controller=$r->newInstanceWithoutConstructor();
    foreach(['financeModel'=>new App\Models\FinanceModel($db),'financePaymentModel'=>new App\Models\FinancePaymentModel($db),
        'financeSettlementService'=>new App\Services\FinanceSettlementService($db,$treasury),'financeTreasuryService'=>$treasury,
        'activityLogger'=>$logger,'db'=>$db,'request'=>$request,'response'=>Config\Services::response(null,false)]as$key=>$value)$r->getProperty($key)->setValue($controller,$value);
    return $controller;
};
try{
    $patient=$db->table('patients')->select('id')->where('clinic_id',1)->like('patient_id','STG_','after')->get()->getRowArray();
    if(!$patient)throw new RuntimeException('Existing synthetic clinic patient required.');
    $notifications=(int)$db->table('notifications')->countAllResults();$attempts=(int)$db->table('notification_attempts')->countAllResults();
    $fixture=['clinic_id'=>1,'patient_id'=>(int)$patient['id'],'examination_id'=>null,'transaction_id'=>$run,'transaction_type'=>'expense',
        'amount'=>116.66,'amount_original'=>33.33,'currency'=>'USD','exchange_rate'=>3.5,'payment_method'=>'bank_transfer','liquidity_account'=>'bank',
        'payment_status'=>'paid','description'=>$run.' description','service_type'=>'procedure','expense_category'=>'supplies','counterparty_name'=>$run.' supplier',
        'supplier_id'=>null,'service_details'=>$run.' details','discount_amount'=>0,'tax_percentage'=>0,'tax_amount'=>0,'total_amount'=>116.66,
        'amount_paid'=>116.66,'balance_due'=>0,'due_date'=>null,'paid_date'=>'2097-06-02','transaction_date'=>'2097-06-02','notes'=>$run.' notes',
        'created_by'=>1,'created_at'=>'2097-06-02 12:00:00','updated_at'=>'2097-06-02 12:00:00'];
    if(!$db->table('finances')->insert($fixture))throw new RuntimeException('New owned expense fixture insert failed.');$id=(int)$db->insertID();
    $receipt=['clinic_id'=>1,'finance_id'=>$id,'type'=>'payment','amount'=>116.66,'payment_method'=>'bank_transfer','liquidity_account'=>'bank',
        'payment_date'=>'2097-06-02','created_by'=>1,'notes'=>$run.' preserved payment','created_at'=>'2097-06-02 12:00:00'];
    foreach(['currency'=>'USD','amount_original'=>33.33,'exchange_rate'=>3.5]as$key=>$value)if($db->fieldExists($key,'finance_payments'))$receipt[$key]=$value;
    if(!$db->table('finance_payments')->insert($receipt))throw new RuntimeException('New owned payment fixture insert failed.');
    $before=$row();$beforePayments=$payments();
    $payload=['counterparty_name'=>$fixture['counterparty_name'],'supplier_id'=>'','expense_category'=>'supplies','amount_original'=>'33.33','currency'=>'USD',
        'exchange_rate'=>'3.5','payment_method'=>'bank_transfer','payment_status'=>'paid','amount_paid'=>'116.66','discount_amount'=>'0','tax_percentage'=>'0',
        'transaction_date'=>'2097-06-02','paid_date'=>'2097-06-02','due_date'=>'','description'=>$fixture['description'],'service_details'=>$fixture['service_details'],'notes'=>$fixture['notes']];
    foreach(['en','ar']as$locale){
        Config\Services::language()->setLocale($locale);session()->remove(['success','error']);$auditBefore=count($audits());
        $response=$browser($payload)->update($id);$success=session()->getFlashdata('success');$error=session()->getFlashdata('error');
        $check($response->getStatusCode()===302&&is_string($success)&&$success!==''&&empty($error),$locale.': actual browser accepts unchanged paid FX expense form');
        $after=$row();$expected=$before;unset($after['updated_at'],$expected['updated_at']);
        $check($after===$expected,$locale.': unchanged FX form preserves complete financial basis, patient association and treasury account');
        $check($payments()===$beforePayments,$locale.': existing payment history remains byte-for-byte unchanged');
        $afterAudits=$audits();$last=end($afterAudits);
        $check(count($afterAudits)===$auditBefore+1&&(int)$last['user_id']===1&&$last['action']==='update',$locale.': unchanged browser form records one server-attributed audit');
        $stable=$row();$auditBefore=count($afterAudits);session()->remove(['success','error']);
        $response=$browser(array_replace($payload,['counterparty_name'=>$run.' unauthorized payee']))->update($id);
        $check($response->getStatusCode()===302&&!empty(session()->getFlashdata('error'))&&empty(session()->getFlashdata('success')),$locale.': actual browser rejects paid-expense payee rewrite');
        $check($row()===$stable&&$payments()===$beforePayments&&count($audits())===$auditBefore,$locale.': rejected payee edit changes no expense, payment or audit row');
        $check((int)$db->transDepth===0&&$db->transStatus(),$locale.': browser calls close only their healthy transactions');
    }
    $check((int)$db->table('notifications')->countAllResults()===$notifications&&(int)$db->table('notification_attempts')->countAllResults()===$attempts,'Actual browser exercise produces no queued notifications or outbound attempts');
    echo json_encode(['status'=>'PASS','scope'=>'Actual browser controller calls; no routed authentication/browser transport claim','run'=>$run,'retained_new_expense_id'=>$id,'check_count'=>count($checks),'checks'=>$checks],JSON_PRETTY_PRINT|JSON_THROW_ON_ERROR),"\n";
}catch(Throwable $e){if((int)$db->transDepth>0)$db->transRollback();echo json_encode(['status'=>'FAIL','run'=>$run,'error'=>$e->getMessage(),'retained_new_expense_id'=>$id,'checks'=>$checks],JSON_PRETTY_PRINT|JSON_THROW_ON_ERROR),"\n";exit(1);}finally{App\Libraries\ApiRuntimeContext::clear();}