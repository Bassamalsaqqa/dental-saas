<?php
declare(strict_types=1);
$root=dirname(__DIR__,2);
$source=file_get_contents($root.'/public/assets/js/studio-patient-picker.js');
$layout=file_get_contents($root.'/app/Views/layouts/studio.php');
foreach(['retry','previous','next'] as $key){
 if(!str_contains($source,'dataset.'.$key))throw new RuntimeException('Missing translated picker control: '.$key);
 if(!str_contains($layout,"lang('Studio.".$key."')"))throw new RuntimeException('Missing escaped server label: '.$key);
 foreach(['en','ar'] as $locale){$labels=require $root.'/app/Language/'.$locale.'/Studio.php';$value=$labels[$key]??'';if($value===''||str_contains($value,'??')||($locale==='ar'&&!preg_match('/[\x{0600}-\x{06FF}]/u',$value)))throw new RuntimeException('Corrupt control label: '.$locale.'/'.$key);}
}
if(preg_match('/\?{3,}/',$source))throw new RuntimeException('Corrupted picker translation literal');
echo json_encode(['passed'=>10,'scope'=>'Picker action labels use escaped canonical translations in both locales; not full localization acceptance']),PHP_EOL;
