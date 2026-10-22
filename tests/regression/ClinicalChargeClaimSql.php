<?php
declare(strict_types=1);
$bootstrap=getopt('', ['bootstrap:'])['bootstrap']??'';
if(PHP_SAPI!=='cli'||!is_file($bootstrap))exit(2);require $bootstrap;
$i=$db->query('SELECT DATABASE() db,@@port port,@@datadir datadir')->getRowArray();
if($db->hostname!=='127.0.0.1'||$i['db']!=='m2c_finance_acceptance'||(int)$i['port']!==3307||!str_contains(str_replace('\\','/',$i['datadir']),'denta-m2c-20261021/disposable-mariadb/data'))throw new RuntimeException('Owned disposable target required');
$prefix='charge_claim_'.bin2hex(random_bytes(5)).'_';$cfg=config('Database')->default;$cfg['DBPrefix']=$prefix;$test=Config\Database::connect($cfg,false);$test->initialize();$other=Config\Database::connect($cfg,false);$other->initialize();
$test->query('CREATE TABLE `'.$prefix.'clinics`(id INT UNSIGNED PRIMARY KEY) ENGINE=InnoDB');
$test->query('CREATE TABLE `'.$prefix.'patients`(id INT UNSIGNED PRIMARY KEY,clinic_id INT UNSIGNED NOT NULL,FOREIGN KEY(clinic_id) REFERENCES `'.$prefix.'clinics`(id)) ENGINE=InnoDB');
$test->query('CREATE TABLE `'.$prefix.'finances`(id INT UNSIGNED PRIMARY KEY,clinic_id INT UNSIGNED NOT NULL,patient_id INT UNSIGNED NOT NULL,transaction_type VARCHAR(20) NOT NULL,FOREIGN KEY(clinic_id) REFERENCES `'.$prefix.'clinics`(id),FOREIGN KEY(patient_id) REFERENCES `'.$prefix.'patients`(id)) ENGINE=InnoDB');
$test->query('CREATE TABLE `'.$prefix.'clinical_charge_lines`(id INT UNSIGNED PRIMARY KEY,clinic_id INT UNSIGNED NOT NULL,patient_id INT UNSIGNED NOT NULL,finance_id INT UNSIGNED NULL,status VARCHAR(20) NOT NULL,currency CHAR(3),net_production_amount DECIMAL(12,2),source_entity_type VARCHAR(30),source_entity_id INT,updated_at DATETIME NULL,FOREIGN KEY(clinic_id) REFERENCES `'.$prefix.'clinics`(id),FOREIGN KEY(patient_id) REFERENCES `'.$prefix.'patients`(id),FOREIGN KEY(finance_id) REFERENCES `'.$prefix.'finances`(id)) ENGINE=InnoDB');
$test->query('INSERT INTO `'.$prefix.'clinics` VALUES(1),(2)');$test->query('INSERT INTO `'.$prefix.'patients` VALUES(1,1),(2,1),(3,2)');
foreach([[1,1,1],[2,1,2],[3,2,3]] as [$id,$clinic,$patient]){$test->table('finances')->insert(['id'=>$id,'clinic_id'=>$clinic,'patient_id'=>$patient,'transaction_type'=>'invoice']);}
foreach([[1,1,1],[2,1,1],[3,1,2],[4,2,3]] as [$id,$clinic,$patient]){$test->table('clinical_charge_lines')->insert(['id'=>$id,'clinic_id'=>$clinic,'patient_id'=>$patient,'status'=>'accepted','currency'=>'ILS','net_production_amount'=>100,'source_entity_type'=>'treatment','source_entity_id'=>$id]);}
$s=new App\Services\ClinicalChargeLineService(new App\Models\ClinicalChargeLineModel($test));$otherService=new App\Services\ClinicalChargeLineService(new App\Models\ClinicalChargeLineModel($other));$n=0;
function claimAssert(bool $ok,string $message):void{global $n;if(!$ok)throw new RuntimeException($message);++$n;}
function claimDeny(callable $f,string $message):void{try{$f();}catch(Throwable $e){claimAssert(true,$message);return;}throw new RuntimeException($message);}
$snapshot=$test->table('clinical_charge_lines')->where('id',1)->get()->getResultArray();
claimDeny(fn()=>$s->markInvoiced(1,[1],1,$snapshot),'No owned transaction rejected');
$test->transBegin();$test->transBegin();claimDeny(fn()=>$s->markInvoiced(1,[1],1,$snapshot),'Nested transaction rejected');$test->transRollback();$test->transRollback();
$test->transBegin();claimAssert(!$s->markInvoiced(1,[1],3,$snapshot),'Foreign invoice rejected');claimAssert(!$s->markInvoiced(1,[3],1),'Other patient charge rejected');claimAssert(!$s->markInvoiced(1,[4],1),'Other clinic charge rejected');claimAssert(!$s->markInvoiced(1,[999],1),'Missing selected row rejected');claimAssert(!$s->markInvoiced(1,[[1]],1),'Malformed identity rejected');$test->transRollback();
// Winner holds charge claim while contender owns a distinct new invoice.
$test->transBegin();claimAssert($s->markInvoiced(1,[1],1,$snapshot),'Winner claims selected source');
$other->transBegin();$other->table('finances')->insert(['id'=>10,'clinic_id'=>1,'patient_id'=>1,'transaction_type'=>'invoice']);
try{$contender=$otherService->markInvoiced(1,[1],10,$snapshot);}catch(Throwable $e){$contender=false;}
claimAssert($contender===false,'Concurrent contender cannot claim locked source');$other->transRollback();claimAssert($test->transCommit(),'Winner commit checked');
claimAssert($test->table('finances')->where('id',10)->countAllResults()===0,'Contender invoice rolled back');
$committed=$test->table('clinical_charge_lines')->where('id',1)->get()->getRowArray();
$other->close();$other=Config\Database::connect($cfg,false);$other->initialize();$otherService=new App\Services\ClinicalChargeLineService(new App\Models\ClinicalChargeLineModel($other));
$other->transBegin();$other->table('finances')->insert(['id'=>11,'clinic_id'=>1,'patient_id'=>1,'transaction_type'=>'invoice']);claimAssert(!$otherService->markInvoiced(1,[1],11,$snapshot),'Stale prior selection cannot claim committed winner');$other->transRollback();
claimAssert($committed===$test->table('clinical_charge_lines')->where('id',1)->get()->getRowArray(),'Winner history preserved verbatim');claimAssert($test->table('finances')->where('id',11)->countAllResults()===0,'Stale candidate invoice rolled back');
// A still-open charge changed after selection is also stale, not merely an available ID.
$before=$test->table('clinical_charge_lines')->where('id',2)->get()->getResultArray();$other->table('clinical_charge_lines')->where('id',2)->update(['net_production_amount'=>125]);
$test->transBegin();claimAssert(!$s->markInvoiced(1,[2],1,$before),'Changed accounting terms rejected under lock');$test->transRollback();
$after=$test->table('clinical_charge_lines')->where('id',2)->get()->getResultArray();$test->transBegin();claimAssert($s->markInvoiced(1,[2],1,$after),'Refreshed exact selected snapshot accepted');claimAssert($test->transCommit(),'Refreshed claim commit checked');
$foreign=$test->table('clinical_charge_lines')->whereIn('id',[3,4])->orderBy('id')->get()->getResultArray();claimAssert(count($foreign)===2&&$foreign[0]['status']==='accepted'&&$foreign[1]['status']==='accepted','Foreign clinic and patient source records preserved');
echo json_encode(['status'=>'PASS','checks'=>$n,'fixture_prefix'=>$prefix,'fixtures_retained'=>true,'scope'=>'Actual clinical-charge claim service/model connection; FK-backed disposable MariaDB tables; two connections, NOWAIT contention, stale state and amount snapshots, candidate invoice rollback. Not full controller or treatment-writer concurrency certification.']),PHP_EOL;
