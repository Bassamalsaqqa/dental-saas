<?php
declare(strict_types=1);
if(PHP_SAPI!=='cli'||$argc!==2||!is_file($argv[1]))throw new RuntimeException('Explicit owned bootstrap required.');
require $argv[1];
if(!defined('STUDIO_THEME_SYNTHETIC_OWNED')||STUDIO_THEME_SYNTHETIC_OWNED!==true)throw new RuntimeException('Owned fixture only.');
$id=$themeDb->query('SELECT DATABASE() db,@@port port')->getRowArray();
if(!preg_match('/\Adenta_theme_synthetic_[0-9_]+\z/D',$id['db'])||(int)$id['port']!==3307)throw new RuntimeException('Disposable target only.');
use App\Services\StudioThemeCatalog as Catalog;
use App\Services\StudioThemeStore as Store;
$store=new Store($themeDb,true);$checks=0;
function policyCheck(bool $ok,string $message):void{global $checks;if(!$ok)throw new RuntimeException($message);$checks++;}
function policyReject(callable $f,string $message):void{try{$f();}catch(DomainException $e){policyCheck(true,$message);return;}throw new RuntimeException($message);}
foreach([['enabled'=>[]],['enabled'=>['unknown']],['enabled'=>['studio-forest','studio-forest']],['enabled'=>'studio-forest'],['enabled'=>['x'=>'studio-forest']],['enabled'=>[[]]],['css'=>'body{}']] as $input)policyReject(fn()=>Catalog::normalizePolicy($input),'Unsafe catalog rejected');
policyCheck(Catalog::normalizePolicy(['enabled'=>['high-contrast','studio-forest']])['enabled']===['studio-forest','high-contrast'],'Canonical catalog order');
$platform=$store->load(null);$original=$store->catalog();
$before=$themeDb->table('studio_theme_current')->where('scope_key !=','catalog')->orderBy('scope_key')->get()->getResultArray();
$oldHistory=$themeDb->table('studio_theme_revisions')->orderBy('scope_key')->orderBy('version')->get()->getResultArray();
$selected=$platform['theme']['preset'];$disabled=array_values(array_diff(array_keys(Catalog::presets()),[$selected]))[0];
$input=['enabled'=>[$selected]];
$allow=static fn()=>true;
policyReject(fn()=>$store->publishCatalog(7,$original['version'],$platform['version'],$input,'Denied catalog',static fn()=>false),'Denied catalog leaves state unchanged');
policyCheck($store->catalog()===$original,'Authorization failure rollback');
$restricted=$store->publishCatalog(7,$original['version'],$platform['version'],$input,'Synthetic policy restrict new choices',$allow);
policyCheck($restricted['policy']['enabled']===[$selected],'Global catalog published');
policyCheck($themeDb->table('studio_theme_current')->where('scope_key !=','catalog')->orderBy('scope_key')->get()->getResultArray()===$before,'Existing platform and clinic palettes untouched');
$historyCount=count($store->catalogHistory());
$store->publishCatalog(7,$restricted['version'],$platform['version'],$input,'Equivalent catalog retry',$allow);
policyCheck(count($store->catalogHistory())===$historyCount,'Equivalent policy retry has no new history');
policyReject(fn()=>$store->publishCatalog(7,$restricted['version'],$platform['version'],['enabled'=>[$disabled]],'Cannot retire current default',$allow),'Current platform default cannot be retired');
$clinic=$store->load(1);
policyReject(fn()=>$store->publish(1,7,$clinic['version'],$platform['version'],['preset'=>$disabled],'Retired preset forged choice',$allow),'New selection rejected under locked current policy');
policyReject(fn()=>$store->publish(null,7,$platform['version'],$platform['version'],['preset'=>$disabled],'Retired global default',$allow),'Retired global selection rejected');
if($selected!==Catalog::DEFAULT_PRESET)policyReject(fn()=>$store->publish(null,7,$platform['version'],$platform['version'],null,'Reset to retired default',$allow),'Global reset cannot bypass catalog availability');
// Existing explicit choices remain valid and keep their frozen colors even when no longer selectable.
$clinic=$store->load(2);
$retry=$store->publish(2,7,$clinic['version'],$platform['version'],$clinic['theme'],'Existing retained theme retry',$allow);
policyCheck($retry['theme']===$clinic['theme'],'Existing explicit choice is preserved');
$rows=$store->clinicOverrides(1,'');policyCheck(count($rows['rows'])<=50&&!$rows['has_more'],'Bounded clinic status projection');
foreach($rows['rows'] as $row)policyCheck(!array_key_exists('payload_json',$row),'No raw theme payload in registry');
policyCheck($store->clinicOverrides(1,"%' OR 1=1 --")['rows']===[],'Search metacharacters cannot escape query');
$enabled=$original['policy'];$enabled['enabled']=array_keys(Catalog::presets());
$restored=$store->publishCatalog(7,$restricted['version'],$platform['version'],$enabled,'Restore fixture catalog choices',$allow);
policyReject(fn()=>$store->publishCatalog(7,$restricted['version'],$platform['version'],$input,'Stale catalog retry',$allow),'Stale catalog rejected');
$after=$themeDb->table('studio_theme_revisions')->orderBy('scope_key')->orderBy('version')->get()->getResultArray();
foreach($oldHistory as $old){$matches=array_values(array_filter($after,fn($r)=>$r['scope_key']===$old['scope_key']&&$r['version']===$old['version']));policyCheck(count($matches)===1&&$matches[0]===$old,'Prior history preserved');}
echo json_encode(['passed'=>$checks,'catalog_version'=>$restored['version'],'scope'=>'Actual owned MariaDB policy/CAS/preservation and bounded projection; callback authorizer fixture, not browser RBAC']),PHP_EOL;
