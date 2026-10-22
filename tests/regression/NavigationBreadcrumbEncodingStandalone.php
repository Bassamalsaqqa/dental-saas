<?php
declare(strict_types=1);
namespace CodeIgniter { class Controller {} }
namespace {
if(PHP_SAPI!=='cli')exit(2);
function service(string $name){return $GLOBALS['fixtureServices'][$name]??throw new RuntimeException('Unexpected service');}
function lang(string $key):string{return $GLOBALS['locale']==='ar'?"\u{0627}\u{0644}\u{0645}\u{0631}\u{0636}\u{0649}":'Patients';}
function log_message($level,$message):void{throw new RuntimeException($message);}
require __DIR__.'/../../app/Controllers/BaseController.php';
$source=file_get_contents(__DIR__.'/../../app/Controllers/BaseController.php');if(preg_match('//u',$source)!==1||str_starts_with($source,"\xEF\xBB\xBF"))throw new RuntimeException('UTF8/no BOM required');
class SyntheticBreadcrumbController extends App\Controllers\BaseController {}
$controller=new SyntheticBreadcrumbController();
$request=new class {public function getUri(){return 'https://synthetic.example/patient/42';}};
(new ReflectionProperty(App\Controllers\BaseController::class,'request'))->setValue($controller,$request);
$method=new ReflectionMethod(App\Controllers\BaseController::class,'trackNavigation');$checks=0;
foreach(['en','ar']as$locale){$GLOBALS['locale']=$locale;
 $nav=new class {public array$entries=[];public function shouldSkip(){return false;}public function push($label,$url){$this->entries[]=[$label,$url];}};
 $GLOBALS['fixtureServices']=['navigation'=>$nav,'uri'=>new class {public function getSegments(){return ['patient','42'];}}];
 $method->invoke($controller);$expected="#42 \u{2014} ".($locale==='ar'?"\u{0627}\u{0644}\u{0645}\u{0631}\u{0636}\u{0649}":'Patients');
 if($nav->entries!==[[$expected,'https://synthetic.example/patient/42']])throw new RuntimeException('Exact localized breadcrumb mismatch '.json_encode([$nav->entries,$expected]));++$checks;
 $rendered=htmlspecialchars($nav->entries[0][0],ENT_QUOTES|ENT_SUBSTITUTE,'UTF-8');if(html_entity_decode($rendered,ENT_QUOTES,'UTF-8')!==$expected||str_contains($rendered,'??'))throw new RuntimeException('Breadcrumb rendering encoding corrupted');++$checks;
}
echo json_encode(['status'=>'PASS','checks'=>$checks+1,'scope'=>'Actual BaseController navigation method and escaped EN/AR labels with synthetic services; no DB or history rewrites']),PHP_EOL;
}
