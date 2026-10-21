<?php
declare(strict_types=1);
$root=dirname(__DIR__,2);$ar=require $root.'/app/Language/ar/InvoiceCorrections.php';$en=require $root.'/app/Language/en/InvoiceCorrections.php';$n=0;
function ok(bool $v,string $m):void{global $n;if(!$v)throw new RuntimeException($m);$n++;}
ok(array_keys($ar)===array_keys($en),'Locale key parity');
foreach($ar as $k=>$v){ok(is_string($v)&&preg_match('//u',$v)===1&&preg_match('/[\x{0600}-\x{06ff}]/u',$v)===1,'Valid Arabic '.$k);ok(!str_contains($v,'???')&&!str_contains($v,"\xef\xbf\xbd"),'No corruption '.$k);}
foreach(glob($root.'/app/Views/finance/corrections/*.php')as$f){$s=file_get_contents($f);ok(!preg_match('/innerHTML|outerHTML|insertAdjacentHTML|\.html\(/',$s),'No DOM sink '.basename($f));}
$config=file_get_contents($root.'/app/Config/InvoiceCorrections.php');ok(str_contains($config,'$enabled = false'),'Default off');ok(str_contains($config,"'operational'"),'Operational only');
echo json_encode(['status'=>'PASS','checks'=>$n,'scope'=>'Source i18n and default-off guards only; no browser/SQL acceptance claim']),PHP_EOL;
