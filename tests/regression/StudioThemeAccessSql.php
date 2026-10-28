<?php
declare(strict_types=1);
if (PHP_SAPI!=='cli' || $argc!==2 || !is_file($argv[1])) throw new RuntimeException('Guarded private bootstrap required.');
require $argv[1];
if (!defined('STUDIO_THEME_SYNTHETIC_OWNED') || STUDIO_THEME_SYNTHETIC_OWNED!==true) throw new RuntimeException('Disposable ownership required.');
$identity=$themeDb->query('SELECT DATABASE() db,@@port port')->getRowArray();
if(!preg_match('/\Adenta_theme_synthetic_[0-9_]+\z/D',$identity['db'])||(int)$identity['port']!==3307)throw new RuntimeException('Disposable target only.');
$access=new App\Services\StudioThemeAccess();$checks=0;
function assertAccess(bool $ok,string $message):void{global $checks;if(!$ok)throw new RuntimeException($message);$checks++;}
session()->set(['user_id'=>1,'active_clinic_id'=>1,'global_mode'=>false,'is_impersonating'=>false]);
assertAccess($access->allows($themeDb,1,1,false),'Canonical practice-manager read');
assertAccess(!$access->allows($themeDb,1,2,false),'Restricted viewer denied');
assertAccess(!$access->allows($themeDb,2,1,false),'Foreign scope denied even with explicit forged parameter');
try{$access->allows($themeDb,1,1,true);throw new LogicException('Unowned accepted');}catch(RuntimeException $e){assertAccess(true,'Publish grant check requires an owned transaction');}
$themeDb->transBegin();
try{
 assertAccess($access->allows($themeDb,1,1,true),'Current locked manager grants');
 $role=(new App\Services\PermissionService($themeDb))->getTenantRoleId(1,1);
 $themeDb->table('role_permissions')->where('role_id',$role)->update(['granted'=>0]);
 assertAccess(!$access->allows($themeDb,1,1,true),'Current grant revocation denies publication');
}finally{$themeDb->transRollback();}
$themeDb->transBegin();
try{$themeDb->table('users')->where('id',1)->update(['active'=>0]);assertAccess(!$access->allows($themeDb,1,1,true),'Inactive actor denied under transaction');}finally{$themeDb->transRollback();}
$platform=$themeDb->query("SELECT ur.user_id FROM user_roles ur JOIN roles r ON r.id=ur.role_id JOIN users u ON u.id=ur.user_id WHERE r.slug='super_admin' AND r.clinic_id IS NULL AND r.is_active=1 AND ur.is_active=1 AND u.active=1 AND (ur.expires_at IS NULL OR ur.expires_at>NOW()) ORDER BY ur.user_id LIMIT 1")->getRowArray();
if(!$platform)throw new RuntimeException('Synthetic platform fixture required');
$actor=(int)$platform['user_id'];session()->set(['user_id'=>$actor,'global_mode'=>true,'active_clinic_id'=>null]);
assertAccess($access->allows($themeDb,null,$actor,false),'Genuine global role allowed');
$themeDb->transBegin();
try{$themeDb->table('user_roles')->where('user_id',$actor)->update(['expires_at'=>'2000-01-01 00:00:00']);assertAccess(!$access->allows($themeDb,null,$actor,true),'Expired platform role denied by canonical service');}finally{$themeDb->transRollback();}
session()->set(['global_mode'=>false,'active_clinic_id'=>1,'is_impersonating'=>true,'impersonated_clinic_id'=>1]);
assertAccess($access->allows($themeDb,1,$actor,false),'Genuine matched impersonation may inspect');
$themeDb->transBegin();try{assertAccess(!$access->allows($themeDb,1,$actor,true),'Impersonation cannot implicitly publish');}finally{$themeDb->transRollback();}
session()->set(['user_id'=>1,'active_clinic_id'=>1,'global_mode'=>false,'is_impersonating'=>false]);
assertAccess($access->allows($themeDb,1,1,false),'Grant and actor mutations rolled back');
echo json_encode(['passed'=>$checks,'scope'=>'Actual canonical RBAC on owned SQL fixture, current revocation/expiry/inactive checks; no production changes']),PHP_EOL;
