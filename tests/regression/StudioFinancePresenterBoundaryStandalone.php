<?php
declare(strict_types=1);
$root=dirname(__DIR__,2);chdir($root);
$before=shell_exec('git show cde62b838f25abd9c1d09e253e3c14dda547c51e:app/Controllers/Finance.php');
if(!is_string($before)||$before==='')throw new RuntimeException('Pinned finance baseline unavailable');
function methods(string $source):array{
 $tokens=PhpToken::tokenize(str_replace("\r\n","\n",$source));$result=[];
 for($i=0,$n=count($tokens);$i<$n;$i++){
  if($tokens[$i]->id!==T_FUNCTION)continue;
  $start=$i;$name=null;
  while(++$i<$n&&$tokens[$i]->text!=='{')if($name===null&&$tokens[$i]->id===T_STRING)$name=$tokens[$i]->text;
  if($name===null)throw new RuntimeException('Unexpected anonymous controller method');
  $depth=1;
  while(++$i<$n&&$depth>0){$t=$tokens[$i];if($t->text==='{'||in_array($t->id,[T_CURLY_OPEN,T_DOLLAR_OPEN_CURLY_BRACES],true))$depth++;elseif($t->text==='}')$depth--;}
  $result[$name]=hash('sha256',implode('',array_map(static fn($t)=>$t->text,array_slice($tokens,$start,$i-$start))));
 }
 return $result;
}
$a=methods($before);$b=methods(file_get_contents($root.'/app/Controllers/Finance.php'));
// Owner review5701870044 now authorizes transaction-envelope corrections.
// Preserve the monetary calculators and every unrelated controller method exactly.
$added=array_values(array_diff(array_keys($b),array_keys($a)));sort($added);
$expected=['settlementRequests','settlementInput','claimSettlement','replaySettlement','settlementConflict','settlementContext'];sort($expected);
if($added!==$expected||array_diff(array_keys($a),array_keys($b)))throw new RuntimeException('Unexpected controller method inventory');
$allowed=['update','updateExpense','addProviderPayoutPayment','markProviderPayoutPaid','markAsPaid','addInvoicePayment','refundInvoicePayment','voidInvoicePayment','closeInvoiceBalance','bulkMarkAsPaid'];$checked=0;
foreach($a as$name=>$hash){if(in_array($name,$allowed,true))continue;if($b[$name]!==$hash)throw new RuntimeException('Protected financial method changed: '.$name);$checked++;}
echo json_encode(['passed'=>$checked,'authorized_transaction_envelopes'=>$allowed,'new_guard_methods'=>$added,'scope'=>'All other controller methods, including monetary normalization and settlement calculations, remain byte-identical to cde62b83. SQL and browser certification is separate.']),PHP_EOL;
