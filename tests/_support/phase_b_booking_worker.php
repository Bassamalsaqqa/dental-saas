<?php
// Integration subprocess: ONLY owned loopback disposable DB, never business defaults.
if (PHP_SAPI!=='cli') { http_response_code(404); exit; }
$root=dirname(__DIR__,2);
require $root.'/scripts/TestDatabasePreflight.php';
\Scripts\TestDatabasePreflight::enforceEnvironment();
require $root.'/vendor/autoload.php'; require $root.'/system/Test/bootstrap.php';
$config=new \Config\Database();
$errors=\Scripts\TestDatabasePreflight::validateConfigBeforeConnection($config->tests,$config->default);
if ($errors || (int)$config->tests['port']!==3307) { fwrite(STDERR,'Unsafe test target'); exit(2); }
$db=\Config\Database::connect('tests');
$ownership=\Scripts\TestDatabasePreflight::verifyOwnershipForReset($db);
if (\Scripts\TestDatabasePreflight::validateLiveConnection($db)||!$ownership['allowed']) exit(2);
$input=json_decode(file_get_contents($argv[1]),true,512,JSON_THROW_ON_ERROR);
session()->set(['user_id'=>$input['actor'],'identity'=>$input['email'],'active_clinic_id'=>$input['clinic'],'global_mode'=>false,'is_impersonating'=>false]);
file_put_contents($argv[2],'ready');
$result=(new \App\Services\AppointmentSchedulingService($db))->createForClinic($input['clinic'],$input['payload'],$input['actor']);
echo json_encode($result,JSON_THROW_ON_ERROR);
