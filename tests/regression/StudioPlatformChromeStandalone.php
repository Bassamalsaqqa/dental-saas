<?php
declare(strict_types=1);
// Pure presentation contract; actual role/session/CSRF tests use guarded synthetic HTTP fixtures.
$root=dirname(__DIR__,2);$state=[];
function session(string $key){global $state;return $state[$key]??null;}
function esc($text,string $context='html'):string{return htmlspecialchars((string)$text,ENT_QUOTES|ENT_SUBSTITUTE,'UTF-8');}
function lang(string $key,array $args=[]):string{return $key==='Studio.platformClinicReference'?'Clinic #'.($args[0]??''):$key;}
function base_url(string $path):string{return 'https://example.invalid/'.$path;}
function csrf_field():string{return '<input type="hidden" name="csrf_test_name" value="synthetic">';}
function renderCase(array $s,$admin,string $name):array{global $root,$state;$state=$s;$is_super_admin=$admin;$clinic=['name'=>$name];require $root.'/app/Views/layouts/partials/studio_platform_context.php';ob_start();require $root.'/app/Views/components/studio_impersonation_banner.php';$html=ob_get_clean();return[$studioPlatformAdmin,$studioImpersonating,$html];}
$good=['user_id'=>7,'active_clinic_id'=>3,'impersonated_clinic_id'=>3,'global_mode'=>false,'is_impersonating'=>true];$checks=0;
foreach([false,null,1,'true']as$untrusted){[$admin,$active,$html]=renderCase($good,$untrusted,'Clinic');if($admin||$active||trim($html)!=='')throw new RuntimeException('Only genuine boolean server-authority is accepted');$checks++;}
foreach([['global_mode'=>true],['is_impersonating'=>false],['impersonated_clinic_id'=>4],['active_clinic_id'=>0],['user_id'=>0]]as$override){[$admin,$active,$html]=renderCase(array_replace($good,$override),true,'Clinic');if($active||trim($html)!=='')throw new RuntimeException('Mismatched security context was displayed');$checks++;}
[$admin,$active,$html]=renderCase($good,true,'<script>owned</script>');if(!$admin||!$active||!str_contains($html,'method="post"')||!str_contains($html,'controlplane/impersonation/exit')||!str_contains($html,'name="csrf_test_name"')||str_contains($html,'<script>owned</script>')||!str_contains($html,'&lt;script&gt;owned&lt;/script&gt;'))throw new RuntimeException('Canonical context, CSRF or escaping failure');$checks++;
foreach(['en','ar']as$locale){$messages=require $root.'/app/Language/'.$locale.'/Studio.php';foreach(['platformViewingClinic','returnControlPlane','openControlPlane','platformClinicReference']as$key){$v=$messages[$key]??'';if($v===''||str_contains($v,'?')||!preg_match('//u',$v))throw new RuntimeException('Corrupted platform copy');if($locale==='ar'&&!preg_match('/\p{Arabic}/u',$v))throw new RuntimeException('Arabic copy missing');$checks++;}}
echo json_encode(['passed'=>$checks,'scope'=>'Presentation state/escaping/UTF8 contract; no database or credentials']),PHP_EOL;
