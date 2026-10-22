<?php
declare(strict_types=1);
$root=dirname(__DIR__,2);$source=file_get_contents($root.'/scripts/ci/saas_guardrails.sh');$base=file_get_contents($root.'/app/Controllers/BaseController.php');$checks=0;
function guardExpect($ok,$why){global$checks;if(!$ok)throw new RuntimeException($why);$checks++;}
$direct='~ionAuth\s*->\s*(inGroup|isAdmin)\s*\(~';$wrapper='~\$this\s*->\s*(inGroup|requireGroup|requireAdmin)\s*\(~';
foreach(['$this->ionAuth->inGroup("admins")','$ionAuth -> isAdmin ()']as$s)guardExpect(preg_match($direct,$s)===1,'Direct camelCase caught');
foreach(['$this->requireAdmin()','$this -> requireGroup ("doctor")','$this->inGroup("staff")']as$s)guardExpect(preg_match($wrapper,$s)===1,'Legacy wrapper caught');
guardExpect(preg_match($direct,'$this->permissionService->isAdmin($userId)')===0,'Canonical permission service distinct');
foreach(['inGroup','requireGroup','requireAdmin','isAdmin']as$name)guardExpect(!str_contains($base,'function '.$name.'('),'Unused wrapper removed');
guardExpect(str_contains($source,"app/Controllers app/Views"),'Wrapper scan scoped to authorization entrypoints, not IonAuth internals');
echo json_encode(['status'=>'PASS','checks'=>$checks]),PHP_EOL;
