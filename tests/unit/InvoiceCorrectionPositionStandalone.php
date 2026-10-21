<?php
declare(strict_types=1);
require __DIR__.'/../../app/Services/InvoiceCorrectionMoney.php';
require __DIR__.'/../../app/Services/InvoiceCorrectionPosition.php';
use App\Services\InvoiceCorrectionMoney as Money;
use App\Services\InvoiceCorrectionPosition as Position;
$n=0;
function check(bool $ok,string $why):void{global $n;if(!$ok)throw new RuntimeException($why);$n++;}
function reject(callable $f,string $why):void{try{$f();}catch(DomainException $e){check(true,$why);return;}throw new RuntimeException($why);}
foreach(['0'=>0,'0.01'=>1,'1.20'=>120,'1.2000'=>120,'-1.20'=>-120,'9999999999.99'=>999999999999]as$v=>$expected){check(Money::cents((string)$v)===$expected,'Exact decimal conversion');check(Money::cents(Money::decimal($expected))===$expected,'Roundtrip');}
foreach(['1e2','1.001',' 1.00','1,000.00','NaN','--1','1.0001','10000000000.00']as$v)reject(fn()=>Money::cents($v),'Malformed/precision rejected');
reject(fn()=>Money::cents(INF),'Infinity');reject(fn()=>Money::cents(true),'Boolean');reject(fn()=>Money::sum(Money::MAX_CENTS,1),'Overflow');
$cases=[
 ['unpaid',10000,0,0,0,0,0,10000,0],
 ['partial receipt',10000,0,4000,0,0,0,6000,0],
 ['unpaid partialcredit',10000,2000,0,0,0,0,8000,0],
 ['partialpaid credit',10000,8000,6000,0,0,0,0,4000],
 ['paid partialcredit',10000,4000,10000,0,0,0,0,4000],
 ['refundaftercredit',10000,4000,6000,0,0,0,0,0],
 ['refundwithoutcredit',10000,0,6000,0,0,0,4000,0],
 ['fullcredit no receipts',10000,10000,0,0,0,0,0,0],
 ['fullcredit paid',10000,10000,10000,0,0,0,0,10000],
 ['applysourcecredit',10000,4000,10000,0,3000,0,0,1000],
 ['replacement funded',3000,0,0,0,0,3000,0,0],
 ['noncashnotliability',10000,4000,0,10000,0,0,0,0],
 ['mixednoncash',10000,8000,7000,3000,0,0,0,7000],
 ['replacement thencredit',10000,5000,0,0,0,10000,0,5000],
];
foreach($cases as[$name,$gross,$credit,$cash,$adjust,$out,$in,$due,$liability]){$s=Position::calculate($gross,$credit,$cash,$adjust,$out,$in);check($s['outstanding_cents']===$due,$name.' receivable');check($s['patient_credit_cents']===$liability,$name.' liability');check($s['cash_net_cents']===$cash,$name.' cash unchanged bycredit');}
reject(fn()=>Position::calculate(100,101,0,0),'Overcredit');reject(fn()=>Position::calculate(100,0,10,0,11),'Unfunded transfer');reject(fn()=>Position::calculate(100,0,-1,0),'Negative net cash');
echo json_encode(['status'=>'PASS','checks'=>$n,'scope'=>'Exact money, cash/noncash separation, credits, refund, explicit allocation and replacement credit liability'],JSON_PRETTY_PRINT),PHP_EOL;
