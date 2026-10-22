<?php
declare(strict_types=1);
/** Fresh, disposable synthetic DB only. Bootstrap must supply $db and $root; never use blanket migrate. */
if (PHP_SAPI !== 'cli') { exit(2); }
$bootstrap = getenv('M2C_TEST_BOOTSTRAP');
if (!$bootstrap || !is_file($bootstrap)) { throw new RuntimeException('Explicit synthetic bootstrap required.'); }
require $bootstrap;
$identity = $db->query('SELECT DATABASE() db, @@port port, @@datadir datadir')->getRowArray();
if (!str_starts_with($identity['db'], 'm2c_migration_') || (int) $identity['port'] !== 3307
    || !str_contains(str_replace('\\', '/', $identity['datadir']), 'denta-m2c-20261021/disposable-mariadb/data')) {
    throw new RuntimeException('Owned synthetic migration instance required.');
}
$db->resetDataCache();
if ($db->listTables() !== []) { throw new RuntimeException('Fresh empty disposable database required; preserve existing evidence.'); }
require $root.'/app/Database/Migrations/2026-10-21-190000_CreateInvoiceCorrectionLedger.php';
use App\Database\Migrations\CreateInvoiceCorrectionLedger;
$m=new CreateInvoiceCorrectionLedger(Config\Database::forge($db));$tables=['invoice_corrections','invoice_correction_lines','invoice_credit_applications','invoice_correction_approvals','invoice_correction_sequences','invoice_replacement_links','invoice_credit_application_reversals'];$checks=[];
function ok($v,$name){global$checks;if(!$v)throw new RuntimeException('FAILED '.$name);$checks[]=$name;}
function ledgerTables(){global$db,$tables;$db->resetDataCache();return array_values(array_intersect($db->listTables(),$tables));}
function refuses($f,$name){try{$f();}catch(Throwable$e){ok(true,$name);return;}ok(false,$name);}
// All rows and tables below are newly created synthetic fixture data.
$db->query('CREATE TABLE permissions (id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,name VARCHAR(100),module VARCHAR(100),action VARCHAR(100),description TEXT) ENGINE=InnoDB');
$db->query("INSERT INTO permissions(name,module,action)VALUES('Synthetic1','finance','approve_corrections'),('Synthetic2','finance','approve_corrections')");
refuses(fn()=>$m->up(),'Ambiguous permission rejected');ok(count(ledgerTables())===0,'Ambiguity rejects before DDL');
$db->table('permissions')->where('name','Synthetic2')->delete();
foreach(['name','description']as$missing){$db->query('RENAME TABLE permissions TO permissions_fixture_original');$defs=['id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY'];foreach(['name','module','action','description']as$c)if($c!==$missing)$defs[]="`$c` VARCHAR(100)";$db->query('CREATE TABLE permissions ('.implode(',',$defs).') ENGINE=InnoDB');refuses(fn()=>$m->up(),'Missing '.$missing.' rejected');ok(count(ledgerTables())===0,'Missing '.$missing.' no DDL');$db->query('DROP TABLE permissions');$db->query('RENAME TABLE permissions_fixture_original TO permissions');}
$db->query('CREATE TABLE role_permissions (id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,role_id INT UNSIGNED,permission_id INT UNSIGNED,granted INT) ENGINE=InnoDB');
$db->query("CREATE TABLE invoice_correction_lines (id INT PRIMARY KEY) ENGINE=InnoDB");refuses(fn()=>$m->up(),'Partial ledger rejected');ok(ledgerTables()===['invoice_correction_lines'],'Partial evidence unchanged');$m->down();
$db->table('permissions')->where('name','Synthetic1')->delete();$m->up();ok(count(ledgerTables())===7,'Up creates seven tables');ok((int)$db->table('permissions')->where('module','finance')->where('action','approve_corrections')->countAllResults()===1,'One canonical approval permission');ok((int)$db->table('role_permissions')->countAllResults()===0,'Zero automatic grants');
foreach($tables as$t){$row=[];foreach($db->query('SHOW COLUMNS FROM `'.$t.'`')->getResultArray()as$c){if(str_contains($c['Extra'],'auto_increment')||$c['Null']==='YES'||$c['Default']!==null)continue;$row[$c['Field']]=preg_match('/^(tinyint|smallint|mediumint|int|bigint|decimal)/',$c['Type'])?1:(str_contains($c['Type'],'date')?'2026-10-22 10:00:00':'x');}$db->transBegin();ok($db->table($t)->insert($row),'Insert owned '.$t.' row');refuses(fn()=>$m->down(),'Down refuses '.$t.' history');ok(count(ledgerTables())===7,'Down preserved all tables for '.$t);$db->transRollback();}
$m->down();ok(count(ledgerTables())===0,'Empty down removes all seven');ok((int)$db->table('permissions')->where('module','finance')->where('action','approve_corrections')->countAllResults()===1,'Down retains permission identity');$m->up();ok(count(ledgerTables())===7,'Re-up recreates schema');ok((int)$db->table('role_permissions')->countAllResults()===0,'Re-up zero grants');
echo json_encode(['status'=>'PASS','checks'=>$checks,'count'=>count($checks),'database'=>$identity['db'],'scope'=>'Owned fresh synthetic database; no production writes'],JSON_PRETTY_PRINT|JSON_THROW_ON_ERROR),PHP_EOL;
