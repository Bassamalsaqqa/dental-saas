<?php
declare(strict_types=1);
require dirname(__DIR__,2).'/app/Services/StudioThemeContrastException.php';
require dirname(__DIR__,2).'/app/Services/StudioThemeCatalog.php';
use App\Services\StudioThemeCatalog as Catalog;
$checks=0;$root=dirname(__DIR__,2);
foreach(glob($root.'/public/assets/css/studio*.css') as $file){preg_match_all('/[^{}]+\{[^{}]*\}/',file_get_contents($file),$blocks);foreach($blocks[0] as $block){if(str_contains($block,'background:var(--color-action)')&&str_contains($block,'color:var(--color-on-frame)'))throw new RuntimeException('Action incorrectly uses frame foreground: '.basename($file));}$checks++;}
$ratios=[];foreach(array_keys(Catalog::presets()) as $preset){$p=Catalog::palette($preset);$ratio=Catalog::contrast($p['action'],$p['on-action']);if($ratio<4.5)throw new RuntimeException('Action text contrast below4.5: '.$preset);$ratios[$preset]=round($ratio,3);$checks++;}
echo json_encode(['passed'=>$checks,'ratios'=>$ratios,'scope'=>'Source action foreground ownership and five catalog-preset pairs; browser and full theme certification are separate']),PHP_EOL;
