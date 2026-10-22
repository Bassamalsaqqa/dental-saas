<?php
declare(strict_types=1);
$bootstrap=getopt('',['bootstrap:'])['bootstrap']??'';
if(PHP_SAPI!=='cli'||!is_file($bootstrap))exit(2);require $bootstrap;
$i=$db->query('SELECT DATABASE() db,@@port port,@@datadir datadir')->getRowArray();
if($db->hostname!=='127.0.0.1'||$i['db']!=='m2c_finance_acceptance'||(int)$i['port']!==3307||!str_contains(str_replace('\\','/',$i['datadir']),'denta-m2c-20261021/disposable-mariadb/data'))throw new RuntimeException('Owned disposable target required');
$prefix='checkout_'.bin2hex(random_bytes(5)).'_';$cfg=config('Database')->default;$cfg['DBPrefix']=$prefix;
$test=Config\Database::connect($cfg,false);$test->initialize();
$test->query('CREATE TABLE `'.$prefix.'clinics`(id INT UNSIGNED PRIMARY KEY) ENGINE=InnoDB');
$test->query('CREATE TABLE `'.$prefix.'patients`(id INT UNSIGNED PRIMARY KEY,clinic_id INT UNSIGNED NOT NULL, FOREIGN KEY(clinic_id) REFERENCES `'.$prefix.'clinics`(id)) ENGINE=InnoDB');
$test->query('CREATE TABLE `'.$prefix.'examinations`(id INT UNSIGNED PRIMARY KEY,clinic_id INT UNSIGNED NOT NULL,patient_id INT UNSIGNED NOT NULL,FOREIGN KEY(clinic_id) REFERENCES `'.$prefix.'clinics`(id),FOREIGN KEY(patient_id) REFERENCES `'.$prefix.'patients`(id)) ENGINE=InnoDB');
$test->query('CREATE TABLE `'.$prefix.'finances`(id INT UNSIGNED PRIMARY KEY,clinic_id INT UNSIGNED NOT NULL,patient_id INT UNSIGNED NOT NULL,examination_id INT UNSIGNED NULL,transaction_type VARCHAR(20) NOT NULL,payment_status VARCHAR(20) NOT NULL DEFAULT \'pending\', FOREIGN KEY(clinic_id) REFERENCES `'.$prefix.'clinics`(id),FOREIGN KEY(patient_id) REFERENCES `'.$prefix.'patients`(id),FOREIGN KEY(examination_id) REFERENCES `'.$prefix.'examinations`(id)) ENGINE=InnoDB');
$test->query('CREATE TABLE `'.$prefix.'finance_payments`(id INT UNSIGNED PRIMARY KEY,clinic_id INT UNSIGNED NOT NULL,finance_id INT UNSIGNED NOT NULL,type VARCHAR(20) NOT NULL,amount DECIMAL(12,2) NOT NULL, FOREIGN KEY(clinic_id) REFERENCES `'.$prefix.'clinics`(id),FOREIGN KEY(finance_id) REFERENCES `'.$prefix.'finances`(id)) ENGINE=InnoDB');
$test->query('INSERT INTO `'.$prefix.'clinics` VALUES(1),(2)');$test->query('INSERT INTO `'.$prefix.'patients` VALUES(1,1),(2,2)');$test->query('INSERT INTO `'.$prefix.'examinations` VALUES(1,1,1),(2,2,2)');
require $root.'/app/Database/Migrations/2026-10-22-170000_CreateFinanceCheckoutRequests.php';
$migration=new App\Database\Migrations\CreateFinanceCheckoutRequests(Config\Database::forge($test));$migration->up();
$s=new App\Services\FinanceCheckoutRequestService($test);$key=str_repeat('a',32);$payload=['invoice'=>['currency'=>'ILS','amount'=>'100.00'],'receipt'=>['amount'=>'40.00']];$n=0;
function ck(bool$v,string$m):void{global$n;if(!$v)throw new RuntimeException($m);$n++;}
function deny(callable$f,string$m):void{try{$f();}catch(Throwable$e){ck(true,$m);return;}throw new RuntimeException($m);}
function finish($test,$s,$claim,int$clinic,int$patient,int$exam,int$invoice,int$payment):void {
    if(!$test->table('finances')->insert(['id'=>$invoice,'clinic_id'=>$clinic,'patient_id'=>$patient,'examination_id'=>$exam,'transaction_type'=>'invoice']))throw new RuntimeException('Invoice fixture insert failed');
    if(!$test->table('finance_payments')->insert(['id'=>$payment,'clinic_id'=>$clinic,'finance_id'=>$invoice,'type'=>'payment','amount'=>'40.00']))throw new RuntimeException('Receipt fixture insert failed');
    $s->finalize($clinic,$claim['request_id'],$invoice,$payment);
}
deny(fn()=>$s->claim(1,1,$key,$payload,1,1),'No ambient transaction refused');
$test->transBegin();$test->transBegin();deny(fn()=>$s->claim(1,1,$key,$payload,1,1),'Nested transaction refused');$test->transRollback();$test->transRollback();
$test->transBegin();deny(fn()=>$s->claim(1,1,'bad',$payload,1,1),'Invalid key refused');deny(fn()=>$s->claim(1,0,$key,$payload,1,1),'Missing actor refused');deny(fn()=>$s->claim(1,1,$key,['huge'=>str_repeat('x',131073)],1,1),'Oversized terms refused');deny(fn()=>$s->claim(1,1,$key,$payload,2,2),'Foreign parent refused');$test->transRollback();
$test->transBegin();$claim=$s->claim(1,1,$key,$payload,1,1);ck(!$claim['idempotent']&&$claim['finance_id']===null,'New request claimed');finish($test,$s,$claim,1,1,1,1,1);$s->finalize(1,$claim['request_id'],1,1);ck($test->transCommit(),'Owned commit succeeds');
$history=$test->table('finance_checkout_requests')->get()->getResultArray();$originalPayments=$test->table('finance_payments')->get()->getResultArray();
$test->transBegin();$retry=$s->claim(1,1,$key,['receipt'=>['amount'=>'40.00'],'invoice'=>['amount'=>'100.00','currency'=>'ILS']],1,1);ck($retry['idempotent']&&$retry['finance_id']===1&&$retry['payment_id']===1,'Canonical identical retry returns same IDs');$test->transCommit();
ck($history===$test->table('finance_checkout_requests')->get()->getResultArray(),'Replay preserves request history verbatim');ck($originalPayments===$test->table('finance_payments')->get()->getResultArray(),'Replay creates no extra receipt');
$test->transBegin();deny(fn()=>$s->claim(1,2,$key,$payload,1,1),'Another actor cannot reuse identity');deny(fn()=>$s->claim(1,1,$key,['invoice'=>['amount'=>'101.00']],1,1),'Changed financial terms refused');$test->transRollback();
$test->transBegin();$otherClinic=$s->claim(2,1,$key,$payload,2,2);finish($test,$s,$otherClinic,2,2,2,2,2);ck($test->transCommit(),'Same key isolated by clinic');
$test->transBegin();deny(fn()=>$s->finalize(1,$claim['request_id'],2,2),'Foreign final invoice rejected');deny(fn()=>$s->finalize(2,$claim['request_id'],2,2),'Foreign request ID rejected');$test->transRollback();
// Fail after invoice and receipt insertion: caller rollback removes all three records.
$test->transBegin();$failed=$s->claim(1,1,str_repeat('b',32),$payload,1,1);finish($test,$s,$failed,1,1,1,3,3);$test->transRollback();
ck($test->table('finances')->where('id',3)->countAllResults()===0,'Rollback removes new invoice');ck($test->table('finance_payments')->where('id',3)->countAllResults()===0,'Rollback removes new receipt');ck($test->table('finance_checkout_requests')->where('request_key',str_repeat('b',32))->countAllResults()===0,'Rollback removes request identity');
$test->transBegin();$retryFailed=$s->claim(1,1,str_repeat('b',32),$payload,1,1);ck(!$retryFailed['idempotent'],'Retry after rollback can claim fresh');$test->transRollback();
// Two independent connections contend on canonical clinic lock; failed contender does not leak a request.
$other=Config\Database::connect($cfg,false);$other->initialize();$other->query('SET SESSION innodb_lock_wait_timeout=1');$otherService=new App\Services\FinanceCheckoutRequestService($other);
$test->transBegin();$racing=$s->claim(1,1,str_repeat('c',32),$payload,1,1);$other->transBegin();deny(fn()=>$otherService->claim(1,1,str_repeat('c',32),$payload,1,1),'Concurrent contender cannot create duplicate invoice');$other->transRollback();finish($test,$s,$racing,1,1,1,4,4);$test->transCommit();
$other->close();$other=Config\Database::connect($cfg,false);$other->initialize();$otherService=new App\Services\FinanceCheckoutRequestService($other);$other->transBegin();$replayed=$otherService->claim(1,1,str_repeat('c',32),$payload,1,1);ck($replayed['idempotent']&&$replayed['finance_id']===4,'Contender retry sees committed winner');$other->transCommit();
ck($test->table('finance_checkout_requests')->where('clinic_id',1)->where('request_key',str_repeat('c',32))->countAllResults()===1,'One committed racing request');
// Later cancellation/refund history never allows original key to create another receipt.
$test->table('finances')->where('id',1)->update(['payment_status'=>'cancelled']);$test->table('finance_payments')->insert(['id'=>5,'clinic_id'=>1,'finance_id'=>1,'type'=>'refund','amount'=>'40.00']);
$test->transBegin();$afterRefund=$s->claim(1,1,$key,$payload,1,1);ck($afterRefund['idempotent']&&$afterRefund['payment_id']===1,'Historical retry remains stable after later refund/cancellation');$test->transCommit();
// A charge selection can derive an examination only within the same clinic/patient.
$test->transBegin();$derived=$s->claim(1,1,str_repeat('d',32),$payload,1,null);finish($test,$s,$derived,1,1,1,6,6);$test->transCommit();
$test->transBegin();ck($s->claim(1,1,str_repeat('d',32),$payload,1,null)['idempotent'],'Derived examination replay preserves original empty input');$test->transCommit();
$test->transBegin();$badExam=$s->claim(1,1,str_repeat('e',32),$payload,1,null);$test->table('finances')->insert(['id'=>7,'clinic_id'=>1,'patient_id'=>1,'examination_id'=>2,'transaction_type'=>'invoice']);$test->table('finance_payments')->insert(['id'=>7,'clinic_id'=>1,'finance_id'=>7,'type'=>'payment','amount'=>'40.00']);deny(fn()=>$s->finalize(1,$badExam['request_id'],7,7),'Foreign derived examination refused despite independent parent FKs');$test->transRollback();
$rows=$test->table('finance_checkout_requests')->orderBy('id')->get()->getResultArray();deny(fn()=>$migration->down(),'Populated rollback refused');ck($rows===$test->table('finance_checkout_requests')->orderBy('id')->get()->getResultArray(),'Migration down preserves identities');
// DDL must fail before creating replay state when a parent cannot participate in rollback.
$badCfg=$cfg;$badCfg['DBPrefix']='checkout_bad_'.bin2hex(random_bytes(5)).'_';$bad=Config\Database::connect($badCfg,false);$bad->initialize();$bad->query('CREATE TABLE `'.$badCfg['DBPrefix'].'clinics`(id INT UNSIGNED PRIMARY KEY) ENGINE=MyISAM');
$badMigration=new App\Database\Migrations\CreateFinanceCheckoutRequests(Config\Database::forge($bad));deny(fn()=>$badMigration->up(),'Nontransactional parent fails before DDL');ck(!$bad->tableExists('finance_checkout_requests'),'Rejected migration creates no replay table');
echo json_encode(['status'=>'PASS','checks'=>$n,'fixture_prefix'=>$prefix,'fixtures_retained'=>true,'scope'=>'Actual MariaDB additive migration, FK-backed parents, owned-transaction replay, tenant/actor isolation, rollback and two-connection contention; not full controller checkout certification']),PHP_EOL;
