<?php
declare(strict_types=1);
namespace CodeIgniter\Database { class Migration { protected $db; public function setTestDatabase(object $db): void {$this->db=$db;} } }
namespace {
require dirname(__DIR__,2).'/app/Database/Migrations/2026-10-29-120000_WidenTreatmentTypeStorage.php';
$m=new \App\Database\Migrations\WidenTreatmentTypeStorage();$checks=[];
function check(bool $ok,string $name):void{global$checks;if(!$ok)throw new \RuntimeException($name);$checks[]=$name;}
$method=new \ReflectionMethod($m,'verifyCompletionMetadata');
$hash=str_repeat('a',64);$valid=['version'=>1,'prepared_sha256'=>$hash,'target_type'=>'varchar(100)','verified_at'=>'2026-10-30T08:00:00+00:00'];$method->invoke($m,$valid,$hash);check(true,'Valid completion metadata accepted');
foreach(['version'=>2,'target_type'=>'varchar(99)','prepared_sha256'=>str_repeat('b',64),'verified_at'=>'2026-02-30T08:00:00+00:00']as$key=>$value){$bad=$valid;$bad[$key]=$value;$failed=false;try{$method->invoke($m,$bad,$hash);}catch(\RuntimeException){$failed=true;}check($failed,'Reject altered '.$key);}
foreach(['','2026-10-30','2026-10-30T08:00:00+02:00']as$value){$bad=$valid;$bad['verified_at']=$value;$failed=false;try{$method->invoke($m,$bad,$hash);}catch(\RuntimeException){$failed=true;}check($failed,'Reject missing/noncanonical verification time');}
$bad=$valid;unset($bad['version']);$failed=false;try{$method->invoke($m,$bad,$hash);}catch(\RuntimeException){$failed=true;}check($failed,'Reject missing completion version');
$source="CREATE TABLE `treatments` (\n  `id` int unsigned NOT NULL AUTO_INCREMENT,\n  `treatment_type` enum('cleaning','extraction','filling','crown','root_canal','orthodontic','implant','other') NOT NULL,\n  PRIMARY KEY (`id`)\n) ENGINE=InnoDB AUTO_INCREMENT=45 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
$target=str_replace("enum('cleaning','extraction','filling','crown','root_canal','orthodontic','implant','other')",'varchar(100)',$source);
$db=new class {public string $ddl='';public function query(string $sql):object{return new class($this->ddl){public function __construct(private string $ddl){}public function getRowArray():array{return['Create Table'=>$this->ddl];}};}};$m->setTestDatabase($db);$verify=new \ReflectionMethod($m,'verifyConvertedDefinition');$db->ddl=$target;$verify->invoke($m,$source,'`treatments`',true);check(true,'Unchanged completed definition accepted');
$db->ddl=str_replace('AUTO_INCREMENT=45','AUTO_INCREMENT=100',$target);$verify->invoke($m,$source,'`treatments`',true);check(true,'Legitimate advancing sequence counter accepted');
$failed=false;try{$verify->invoke($m,$source,'`treatments`');}catch(\RuntimeException){$failed=true;}check($failed,'Initial/incomplete conversion retains exact sequence check');
foreach([str_replace('PRIMARY KEY (`id`)','PRIMARY KEY (`id`), KEY `unexpected` (`treatment_type`)',$target),str_replace('varchar(100) NOT NULL','varchar(100) DEFAULT NULL',$target),str_replace('ENGINE=InnoDB','ENGINE=Aria',$target),str_replace('utf8mb4_unicode_ci','utf8mb4_general_ci',$target),$target." COMMENT='unreviewed'"]as$changed){$db->ddl=$changed;$failed=false;try{$verify->invoke($m,$source,'`treatments`',true);}catch(\RuntimeException){$failed=true;}check($failed,'Completed definition drift rejected');}
echo json_encode(['passed'=>count($checks),'checks'=>$checks,'scope'=>'Pure completion metadata and DDL comparison regression; real SQL retries separately certified'],JSON_THROW_ON_ERROR),PHP_EOL;
}
