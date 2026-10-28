<?php
declare(strict_types=1);
// CLI only. Supply an outside-source guarded bootstrap defining $themeDb and the ownership constant.
if (PHP_SAPI !== 'cli' || $argc !== 2 || !is_file($argv[1])) throw new RuntimeException('Private synthetic bootstrap required.');
require $argv[1];
if (!defined('STUDIO_THEME_SYNTHETIC_OWNED') || STUDIO_THEME_SYNTHETIC_OWNED !== true) throw new RuntimeException('No synthetic ownership proof.');
$identity = $themeDb->query('SELECT DATABASE() db,@@port port')->getRowArray();
if (!preg_match('/\Adenta_theme_synthetic_[0-9_]+\z/D',$identity['db']) || (int)$identity['port']!==3307) throw new RuntimeException('Refusing non-disposable database.');
use App\Services\StudioThemeStore;
use App\Services\StudioThemeCatalog;
use App\Database\Migrations\CreateStudioThemeHistory;
require dirname(__DIR__,2).'/app/Database/Migrations/2026-10-28-120000_CreateStudioThemeHistory.php';
$checks = 0;
function themeCheck(bool $ok, string $message): void { global $checks; if (!$ok) throw new RuntimeException($message); $checks++; }
function themeReject(callable $operation, string $message): void {
    try { $operation(); } catch (DomainException $e) { themeCheck(true,$message); return; }
    throw new RuntimeException($message);
}
function questions($db): int { return (int)$db->query("SHOW SESSION STATUS LIKE 'Questions'")->getRowArray()['Value']; }
$before = questions($themeDb);
$disabled = new StudioThemeStore($themeDb, false);
themeCheck($disabled->load(1)['theme']['preset']==='studio-forest', 'Default-off fallback');
themeCheck($disabled->history(1)===[], 'Default-off no history access');
themeCheck(questions($themeDb)-$before===1, 'Default-off performs zero theme queries');
$store = new StudioThemeStore($themeDb, true);
// The runner must start with a fresh owned schema. It never clears previous fixture evidence.
themeCheck(!$themeDb->tableExists('studio_theme_current') && !$themeDb->tableExists('studio_theme_revisions'), 'Fresh target only');
themeCheck($store->load(1)['theme']['preset']==='studio-forest', 'Missing-schema fallback');
$clinicsBefore = $themeDb->query('SELECT * FROM clinics ORDER BY id')->getResultArray();
$ids = array_column(array_filter($clinicsBefore,fn($r)=>$r['status']==='active'),'id');
if (count($ids)<2) throw new RuntimeException('Two active synthetic clinics required');
[$a,$b] = array_map('intval',array_slice($ids,0,2));
$migration = new CreateStudioThemeHistory(Config\Database::forge($themeDb));
$migration->up();
themeCheck($store->available(), 'Two additive tables available');
try { $migration->up(); throw new LogicException('Existing schema accepted'); } catch (RuntimeException $e) { themeCheck(str_contains($e->getMessage(),'already or partially'),'Migration refuses existing/partial ownership'); }
try { $migration->down(); throw new LogicException('History removed'); } catch (RuntimeException $e) { themeCheck(str_contains($e->getMessage(),'preserved'),'Source rollback preserves theme history'); }
$authorize = static fn($db,$clinic,$actor) => $actor===7;
$platform=$store->publish(null,7,0,0,['preset'=>'sandstone-copper'],'Synthetic global publish',$authorize);
themeCheck($platform['version']===1 && $store->load($b)['theme']['preset']==='sandstone-copper', 'Global inheritance without tenant backfill');
themeCheck($themeDb->table('studio_theme_current')->countAllResults()===1, 'No per-clinic initial copies');
$custom=$store->publish($a,7,0,1,['preset'=>'slate-sage'],'Synthetic explicit clinic',$authorize);
themeCheck(!$custom['inherited'] && $custom['version']===1, 'Explicit clinic theme');
$store->publish(null,7,1,1,['preset'=>'deep-charcoal'],'Synthetic platform change',$authorize);
themeCheck($store->load($a)['theme']['preset']==='slate-sage' && $store->load($b)['theme']['preset']==='deep-charcoal', 'Global changes affect inheritors only');
themeReject(fn()=>$store->publish($a,7,1,1,['preset'=>'studio-forest'],'Stale global',$authorize),'Stale platform preview rejected');
themeReject(fn()=>$store->publish($a,7,0,2,['preset'=>'studio-forest'],'Stale clinic',$authorize),'Stale clinic preview rejected');
$inherit=$store->publish($a,7,1,2,null,'Explicit use platform',$authorize);
themeCheck($inherit['inherited'] && $inherit['version']===2 && $inherit['theme']['preset']==='deep-charcoal','Explicit reset re-inherits');
$revert=$store->publish($a,7,2,2,null,'Recover clinic version one',$authorize,1);
themeCheck($revert['version']===3 && $revert['theme']['preset']==='slate-sage','Revert appends a new revision');
$history=$store->history($a);
themeCheck(count($history)===3 && $history[0]['action']==='revert','Append-only scope history');
$store->publish($a,7,3,2,$revert['theme'],'Identical authorized retry',$authorize);
themeCheck(count($store->history($a))===3 && $store->load($a)['version']===3,'Equivalent retry does not rewrite history');
themeReject(fn()=>$store->publish($a,8,3,2,$revert['theme'],'Revoked equivalent retry',$authorize),'No-op still requires current authorization');
themeReject(fn()=>$store->publish($b,8,0,2,['preset'=>'studio-forest'],'Denied mutation',$authorize),'Denied creation fails');
themeCheck(!$themeDb->table('studio_theme_current')->where('scope_key','clinic:'.$b)->get()->getRowArray(),'Denied scope creation rolled back');
try { $store->publish($b,7,0,2,['preset'=>'studio-forest'],'Injected authorization failure',static function(){throw new LogicException('Injected failure');}); throw new RuntimeException('Failure not raised'); } catch (LogicException $e) { themeCheck(true,'Injected failure propagated'); }
themeCheck(!$themeDb->table('studio_theme_current')->where('scope_key','clinic:'.$b)->get()->getRowArray() && $store->history($b)===[],'Rollback leaves no scope or audit');
themeReject(fn()=>$store->publish($b,7,0,2,null,'Foreign recovery version',$authorize,1),'Cannot recover another clinic revision');
themeReject(fn()=>$store->publish(999999,7,0,2,['preset'=>'studio-forest'],'Missing clinic',$authorize),'Missing parent rejected');
$themeDb->transBegin();
try { $store->publish($a,7,3,2,null,'Nested mutation',$authorize); throw new LogicException('Nested accepted'); } catch (RuntimeException $e) { themeCheck((int)$themeDb->transDepth===1,'No nested transaction ownership mistake'); }
$themeDb->transRollback();
themeCheck($themeDb->query('SELECT * FROM clinics ORDER BY id')->getResultArray()===$clinicsBefore,'All synthetic parent rows preserved byte-for-byte');
themeCheck($store->load($a)['version']===3 && $store->load(null)['version']===2,'Failure paths preserve prior committed versions');
echo json_encode(['passed'=>$checks,'database'=>$identity['db'],'platform_version'=>2,'clinic_version'=>3,'scope'=>'Real isolated MariaDB migration/CAS/inheritance/revert/rollback; authorization callback fixture, NOT routed RBAC acceptance']),PHP_EOL;
