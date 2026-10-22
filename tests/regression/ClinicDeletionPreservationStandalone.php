<?php
declare(strict_types=1);
namespace CodeIgniter\Database { interface ConnectionInterface {} }
namespace CodeIgniter { class Model { public function __construct(){}public function find($id):?array{return$id===999?null:['id'=>$id,'name'=>'Synthetic'];}public function __call($name,$args){throw new \RuntimeException('Unexpected model operation '.$name);} } }
namespace App\Controllers { class BaseController {protected$request;protected$response;} }
namespace {
require __DIR__.'/../../app/Models/ClinicModel.php';
require __DIR__.'/../../app/Services/FinancialParentDeletionGuard.php';
require __DIR__.'/../../app/Controllers/ControlPlane/Clinics.php';
$locale='en';$messages=[];foreach(['en','ar']as$code)$messages[$code]=require __DIR__.'/../../app/Language/'.$code.'/IonAuth.php';
function lang(string$key):string{global$locale,$messages;return$messages[$locale][substr($key,8)]??$key;}
function redirect():object{return new class {public function back():self{return$this;}public function with(string$key,string$value):array{return[$key=>$value];}};}
final class DatabaseTrap implements \CodeIgniter\Database\ConnectionInterface {public function __call($name,$args){throw new \RuntimeException('Unexpected database mutation/read '.$name);}}
final class ResponseFixture {public int$status=200;public array$data=[];public function setStatusCode(int$value):self{$this->status=$value;return$this;}public function setJSON(array$value):self{$this->data=$value;return$this;}}
$n=0;function ck(bool$value,string$why):void{global$n;if(!$value)throw new \RuntimeException($why);$n++;}
$model=new \App\Models\ClinicModel();foreach([null,0,1,[1,2],PHP_INT_MAX]as$id)foreach([false,true]as$purge)ck($model->delete($id,$purge)===false,'Direct model deletion fails closed including purge and bulk');
$guard=new \App\Services\FinancialParentDeletionGuard(new DatabaseTrap());foreach([1,PHP_INT_MAX]as$id)foreach(['deleteClinic','assertClinicCanBeDeleted']as$method){try{$method==='deleteClinic'?$guard->$method($id,static function(){throw new \RuntimeException('Destructive callback reached');}):$guard->$method($id);throw new \RuntimeException('Deletion accepted');}catch(\App\Services\FinancialParentDeletionException$e){ck(str_contains($e->getMessage(),'disabled'),'Shared service denies before DB or callback');}}
try{$guard->deleteClinic(0,static fn()=>true);throw new \RuntimeException('Invalid clinic accepted');}catch(\App\Services\FinancialParentNotFoundException$e){ck(true,'Invalid ID preserved');}
foreach(['en','ar']as$locale){$message=lang('IonAuth.clinic_delete_history_protected');ck(preg_match('//u',$message)===1&&!str_contains($message,'??')&&!str_contains($message,'<'),'Plain UTF-8 localized guidance');if($locale==='ar')ck(preg_match('/\p{Arabic}/u',$message)===1,'Actual Arabic');
foreach([true,false]as$ajax){$controller=(new \ReflectionClass(\App\Controllers\ControlPlane\Clinics::class))->newInstanceWithoutConstructor();$response=new ResponseFixture();foreach(['request'=>new class($ajax){public function __construct(private bool$ajax){}public function isAJAX():bool{return$this->ajax;}},'response'=>$response,'auditService'=>new class{public function __call($name,$args){throw new \RuntimeException('Deletion audit must not run');}},'ionAuth'=>new class{public function __call($name,$args){throw new \RuntimeException('No implicit identity mutation');}}]as$name=>$value)(new \ReflectionProperty($controller,$name))->setValue($controller,$value);$result=$controller->delete(42);ck($ajax?($result===$response&&$response->status===409&&$response->data===['success'=>false,'message'=>$message]):$result===['error'=>$message],'Actual route denies without invoking destructive services');if($ajax)ck($controller->delete(999)->data===['success'=>false,'message'=>'Clinic not found.'],'Missing clinic behavior preserved');}}
$source=file_get_contents(__DIR__.'/../../app/Views/control_plane/clinics.php');ck(!str_contains($source,'/delete')&&!str_contains($source,'deleteModal')&&str_contains($source,'delete_disabled_label'),'Destructive modal/handlers removed, disabled action visible');ck(str_contains($source,'toggleClinicStatus')&&str_contains($source,'clinic_delete_history_protected'),'Explicit clinic status controls and guidance retained');
echo json_encode(['status'=>'PASS','checks'=>$n,'scope'=>'Actual clinic model, service and controller with DB/callback/audit traps; no database or production writes'],JSON_PRETTY_PRINT),PHP_EOL;
}
