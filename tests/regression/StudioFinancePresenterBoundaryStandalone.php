<?php
declare(strict_types=1);
$root=dirname(__DIR__,2);chdir($root);
$before=shell_exec('git show ad2fae23c309e80f515cb6fbf3d1eaff49692930:app/Controllers/Finance.php');
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
if(array_keys($a)!==array_keys($b))throw new RuntimeException('Controller method inventory changed');
$allowed=['show','refundableInvoiceCash'];$checked=0;
foreach($a as$name=>$hash){if(in_array($name,$allowed,true))continue;if($b[$name]!==$hash)throw new RuntimeException('Protected financial method changed: '.$name);$checked++;}
echo json_encode(['passed'=>$checked,'allowed_readonly_presenter_delta'=>$allowed,'scope'=>'Pinned controller method-body identity; actual synthetic mutation/denial evidence is separate']),PHP_EOL;
