<?php
declare(strict_types=1);
namespace App\Models {
    // Query-contract harness only: no CodeIgniter boot or database access.
    abstract class TenantAwareModel { protected $db;public function __construct($db){$this->db=$db;} }
}
namespace {
require __DIR__.'/../../app/Models/ExaminationModel.php';
final class CountDatabase {
    public array $queries=[];
    public function __construct(public array $fixtures){}
    public function table(string$table):CountBuilder { if($table!=='examinations')throw new RuntimeException('Unexpected table');return new CountBuilder($this); }
}
final class CountBuilder {
    private int $clinic=0;private array $ids=[];
    public function __construct(private CountDatabase$db){}
    public function select(string$columns,bool$escape):self {if($columns!=='patient_id, COUNT(*) AS examination_count'||$escape!==false)throw new RuntimeException('Aggregate contract');return$this;}
    public function where(string$key,int$value):self {if($key!=='clinic_id')throw new RuntimeException('Explicit tenant predicate required');$this->clinic=$value;return$this;}
    public function whereIn(string$key,array$ids):self {if($key!=='patient_id')throw new RuntimeException('Page ID restriction required');$this->ids=$ids;return$this;}
    public function groupBy(string$key):self {if($key!=='patient_id')throw new RuntimeException('Grouping required');return$this;}
    public function get():object {$this->db->queries[]=['clinic'=>$this->clinic,'ids'=>$this->ids];$counts=[];foreach($this->db->fixtures as$row)if($row['clinic_id']===$this->clinic&&in_array($row['patient_id'],$this->ids,true))$counts[$row['patient_id']]=($counts[$row['patient_id']]??0)+1;$rows=[];foreach($counts as$id=>$n)$rows[]=['patient_id'=>$id,'examination_count'=>(string)$n];return new class($rows){public function __construct(private array$rows){}public function getResultArray():array{return$this->rows;}};}
}
$n=0;function ok(bool$condition,string$message):void{global$n;if(!$condition)throw new RuntimeException($message);$n++;}
$db=new CountDatabase([['clinic_id'=>1,'patient_id'=>1],['clinic_id'=>1,'patient_id'=>1],['clinic_id'=>1,'patient_id'=>2],['clinic_id'=>2,'patient_id'=>1],['clinic_id'=>1,'patient_id'=>99]]);$model=new App\Models\ExaminationModel($db);
ok($model->countByPatientIdsForClinic(1,[1,2,3])===[1=>2,2=>1,3=>0],'Independent per-patient counts including zero');ok(count($db->queries)===1,'One query for page');ok($db->queries[0]['clinic']===1&&$db->queries[0]['ids']===[1,2,3],'Clinic and page constrained');
ok($model->countByPatientIdsForClinic(2,[1])===[1=>1],'Foreign-clinic counts excluded');ok($model->countByPatientIdsForClinic(1,[2])===[2=>1],'Repeated calls retain no previous patient predicate');
$before=count($db->queries);ok($model->countByPatientIdsForClinic(1,[])===[]&&count($db->queries)===$before,'Empty page does not query');ok($model->countByPatientIdsForClinic(1,[0,-3,'2',2])===[2=>1],'Deduplicated positive IDs');
try{$model->countByPatientIdsForClinic(0,[1]);throw new RuntimeException('Invalid clinic accepted');}catch(InvalidArgumentException$e){ok(true,'Missing clinic fails closed');}
foreach([10,25,100,1001]as$size){$before=count($db->queries);$result=$model->countByPatientIdsForClinic(1,range(1,$size));ok(count($db->queries)-$before===(int)ceil($size/500),'Bounded aggregate query count '.$size);ok(count($result)===$size,'No silent result cap '.$size);}
$source=file_get_contents(__DIR__.'/../../app/Controllers/Patient.php');$a=strpos($source,'public function getData()');$b=strpos($source,'public function ',$a+20);$method=substr($source,$a,$b-$a);ok(str_contains($method,'countByPatientIdsForClinic('),'Controller uses bounded counts');ok(!str_contains($method,"->where('patient_id', \$patient['id'])->countAllResults(false)"),'Per-row retained predicate query removed');
echo json_encode(['status'=>'PASS','checks'=>$n,'scope'=>'Deterministic query-contract fixture, not actual SQL timing','before_count_queries'=>['10'=>10,'25'=>25,'100'=>100,'1001'=>1001],'after_count_queries'=>['10'=>1,'25'=>1,'100'=>1,'1001'=>3],'business_database_access'=>false],JSON_PRETTY_PRINT),PHP_EOL;
}
