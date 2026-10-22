<?php
declare(strict_types=1);
namespace App\Controllers { class BaseController { protected $request; protected $response; } }
namespace App\Models {
    // Controller fixture only. The actual IonAuth deletion implementation is loaded below.
    class UserModel { public function find($id): ?array { return $id === 999 ? null : ['id'=>$id,'email'=>'synthetic@example.invalid']; } }
}
namespace {
require __DIR__.'/../../app/Models/IonAuthModel.php';
require __DIR__.'/../../app/Controllers/ControlPlane/Users.php';
$locale='en';$messages=[];
foreach(['en','ar'] as $code)$messages[$code]=require __DIR__.'/../../app/Language/'.$code.'/IonAuth.php';
function lang(string $key): string { global $locale,$messages;return $messages[$locale][substr($key,8)]??$key; }
function session(): object { return new class { public function get(string$key){if($key!=='user_id')throw new \RuntimeException('Unexpected session lookup');return 5;} }; }
function redirect(): object { return new class { public function back(): self{return$this;}public function with(string$key,string$value):array{return[$key=>$value];} }; }
final class DeletionTrap extends \App\Models\IonAuthModel {
    public function __construct() { $this->db=new class {public function __call($name,$args){throw new \RuntimeException('Unexpected database operation '.$name);}}; }
    public function triggerEvents($events):void {throw new \RuntimeException('Deletion hooks must not run');}
    public function removeFromGroup($groupIds=0,int$userId=0):bool {throw new \RuntimeException('Group removal must not run');}
}
final class ResponseFixture { public int$status=200;public array$data=[];public function setStatusCode(int$value):self{$this->status=$value;return$this;}public function setJSON(array$value):self{$this->data=$value;return$this;} }
final class AuthFixture { public function __construct(private DeletionTrap$model){}public function deleteUser(int$id):bool{throw new \RuntimeException('Controller must deny before invoking identity deletion');}public function __call($name,$args){throw new \RuntimeException('Unexpected identity mutation '.$name);} }
$n=0;function ck(bool$value,string$why):void{global$n;if(!$value)throw new \RuntimeException($why);$n++;}
foreach(['en','ar'] as $locale){
    $model=new DeletionTrap();
    foreach([0,1,5,42,-1,PHP_INT_MAX] as$id)ck($model->deleteUser($id)===false,'Every direct physical deletion denied before side effects');
    ck(array_unique($model->errorsArray(false))===['IonAuth.delete_history_protected'],'Specific preservation error');
    $message=lang('IonAuth.delete_history_protected');ck($message!==''&&!str_contains($message,'<')&&!str_contains($message,'??')&&preg_match('//u',$message)===1,'Plain UTF-8 preservation message');
    if($locale==='ar')ck(preg_match('/\p{Arabic}/u',$message)===1,'Real Arabic text');
    foreach([true,false]as$ajax){
        $controller=(new \ReflectionClass(\App\Controllers\ControlPlane\Users::class))->newInstanceWithoutConstructor();$response=new ResponseFixture();
        foreach(['request'=>new class($ajax){public function __construct(private bool$ajax){}public function isAJAX():bool{return$this->ajax;}},'response'=>$response,'ionAuth'=>new AuthFixture($model),'auditService'=>new class {public function __call($name,$args){throw new \RuntimeException('Successful deletion audit must not run');}}]as$name=>$value)(new \ReflectionProperty($controller,$name))->setValue($controller,$value);
        $result=$controller->delete(42);
        if($ajax){ck($result===$response&&$response->status===409&&$response->data===['success'=>false,'message'=>$message],'Actual controller denies forged direct AJAX request with plain localized 409');}
        else ck($result===['error'=>$message],'Actual controller browser path flashes plain localized reason');
        foreach([1,5,999] as$id){$result=$controller->delete($id);ck($ajax ? $result->data['success']===false : isset($result['error']),'Primary-admin, self and missing account safeguards preserved');}
    }
}
foreach(['index','show']as$view){$source=file_get_contents(__DIR__.'/../../app/Views/control_plane/users/'.$view.'.php');ck(!str_contains($source,'/delete')&&!str_contains($source,'deleteModal')&&str_contains($source,'delete_disabled_label'),'Destructive UI removed, disabled action retained');ck(str_contains($source,'toggle-status')&&str_contains($source,'delete_history_protected'),'Explicit status controls and preservation explanation retained');}
echo json_encode(['status'=>'PASS','checks'=>$n,'scope'=>'Actual IonAuthModel and ControlPlane controller with database/hooks/group/audit/identity traps; EN/AR messages; no database writes'],JSON_PRETTY_PRINT),PHP_EOL;
}
