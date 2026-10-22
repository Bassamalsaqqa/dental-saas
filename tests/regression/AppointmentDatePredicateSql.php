<?php
declare(strict_types=1);
/** Focused DATE-column predicate equivalence; connection-local synthetic table only. */
if (PHP_SAPI !== 'cli') { exit(2); }
$bootstrap = getenv('M2C_TEST_BOOTSTRAP');
if (!$bootstrap || !is_file($bootstrap)) { throw new RuntimeException('Explicit owned synthetic bootstrap required.'); }
require $bootstrap;
$identity=$db->query('SELECT DATABASE() db,@@port port,@@datadir datadir')->getRowArray();
if (!str_starts_with($identity['db'],'m2c_') || (int)$identity['port']!==3307 || !str_contains(str_replace('\\','/',$identity['datadir']),'denta-m2c-20261021/disposable-mariadb/data')) { throw new RuntimeException('Owned disposable instance required.'); }
$checks=0;
function expectDate($condition,string $name):void { global $checks; if(!$condition)throw new RuntimeException($name);$checks++; }
$source=file_get_contents($root.'/app/Models/AppointmentModel.php');
expectDate(!str_contains($source,'DATE(appointment_date)'),'No wrapped date predicates');
expectDate(substr_count($source,"'appointments.appointment_date'")>=5,'All five replacement predicates qualified');
$db->query('CREATE TEMPORARY TABLE m2c_date_predicate_fixture (id INT PRIMARY KEY,clinic_id INT NOT NULL,appointment_date DATE NOT NULL,KEY ix_clinic_date(clinic_id,appointment_date)) ENGINE=InnoDB');
foreach(['2026-10-24','2026-10-25','2026-10-26','2026-03-27','2026-03-28','2026-03-29','2026-12-31','2027-01-01']as$i=>$date){foreach([1,2]as$clinic)$db->query('INSERT INTO m2c_date_predicate_fixture(id,clinic_id,appointment_date)VALUES(?,?,?)',[$i*2+$clinic,$clinic,$date]);}
foreach(['2026-10-25','2026-03-28','2026-12-31','2027-01-01','2026-11-01']as$date){foreach([1,2]as$clinic){$old=$db->query('SELECT id FROM m2c_date_predicate_fixture WHERE clinic_id=? AND DATE(appointment_date)=? ORDER BY id',[$clinic,$date])->getResultArray();$new=$db->query('SELECT id FROM m2c_date_predicate_fixture WHERE clinic_id=? AND appointment_date=? ORDER BY id',[$clinic,$date])->getResultArray();expectDate($old===$new,'DATE equality equivalent across boundary and clinic');}}
echo json_encode(['status'=>'PASS','checks'=>$checks,'schema'=>'DATE','scope'=>'Temporary synthetic rows; result equivalence, no performance claim']),PHP_EOL;
