<?php
declare(strict_types=1);
$o=getopt('',['bootstrap:','manifest:','actor:']);if(PHP_SAPI!=='cli'||!is_file($o['bootstrap']??'')||!is_file($o['manifest']??''))exit(2);require$o['bootstrap'];
$m=json_decode(file_get_contents($o['manifest']),true);$cfg=config('Database')->default;
if(!preg_match('/^media_[a-f0-9]+_$/D',$m['prefix']))throw new RuntimeException('Owned prefix required');$cfg['DBPrefix']=$m['prefix'];$test=Config\Database::connect($cfg,false);$test->initialize();
$i=$test->query('SELECT DATABASE() db,@@port port,@@datadir datadir')->getRowArray();if($test->hostname!=='127.0.0.1'||$i['db']!=='m2c_finance_acceptance'||(int)$i['port']!==3307||!str_contains(str_replace('\\','/',$i['datadir']),'denta-m2c-20261021/disposable-mariadb/data'))throw new RuntimeException('Owned instance required');
class WorkerDerivativeQuota extends App\Services\PlanGuard {public function __construct(){}public function assertQuota($c,$m,$d=1,array$meta=[]){} }
config('MediaDerivatives')->enabled=true;$test->query('SET SESSION innodb_lock_wait_timeout=5');$s=new App\Services\AttachmentDerivativeService($test,$m['base'],new WorkerDerivativeQuota());$actor=$o['actor'];if(!in_array($actor,['a','b'],true))exit(2);
$s->beforePublicationFn=static function()use($m,$actor){file_put_contents($m['base'].'/ready-'.$actor,'ready');$until=microtime(true)+8;while(!is_file($m['base'].'/ready-a')||!is_file($m['base'].'/ready-b')){if(microtime(true)>$until)throw new RuntimeException('Both renderers did not reach publication barrier');usleep(10000);}};
$row=$s->build(1,4,'thumbnail');echo json_encode(['id'=>(int)$row['id'],'hash'=>$row['file_sha256'],'ready'=>true]),PHP_EOL;
