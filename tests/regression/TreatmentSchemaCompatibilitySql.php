<?php
declare(strict_types=1);
/** Read-only gate: success means every offered treatment key is representable without coercion. */
if(PHP_SAPI!=='cli'||$argc!==2||!is_file($argv[1]))throw new RuntimeException('Explicit owned synthetic bootstrap required');
require $argv[1];
if(!defined('STUDIO_THEME_SYNTHETIC_OWNED')||STUDIO_THEME_SYNTHETIC_OWNED!==true)throw new RuntimeException('Owned target required');
$identity=$themeDb->query('SELECT DATABASE() db,@@port port')->getRowArray();
if(!preg_match('/\Adenta_theme_synthetic_[0-9_]+\z/D',$identity['db'])||(int)$identity['port']!==3307)throw new RuntimeException('Disposable target only');
$row=$themeDb->query("SELECT DATA_TYPE,COLUMN_TYPE,CHARACTER_MAXIMUM_LENGTH FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='treatments' AND COLUMN_NAME='treatment_type'")->getRowArray();
$offered=array_keys(App\Libraries\TreatmentCatalog::forSelection());$unsupported=$offered;
if($row&&$row['DATA_TYPE']==='enum'){
    $values=str_getcsv(substr($row['COLUMN_TYPE'],5,-1),',',"'",'\\');
    $unsupported=array_values(array_diff($offered,$values));
}elseif($row&&in_array($row['DATA_TYPE'],['varchar','char'],true)){
    $unsupported=array_values(array_filter($offered,static fn($value)=>strlen($value)>(int)$row['CHARACTER_MAXIMUM_LENGTH']));
}
$report=['passed'=>$unsupported===[],'offered_count'=>count($offered),'unsupported_keys'=>$unsupported,'storage_type'=>$row['COLUMN_TYPE']??'missing','scope'=>'Read-only schema compatibility gate; a failing result is a defect, not passing characterization','mutations'=>0];
echo json_encode($report,JSON_PRETTY_PRINT|JSON_THROW_ON_ERROR),PHP_EOL;
exit($report['passed']?0:1);
