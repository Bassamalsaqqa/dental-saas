<?php
declare(strict_types=1);
require dirname(__DIR__,2).'/app/Services/StudioThemeContrastException.php';require dirname(__DIR__,2).'/app/Services/StudioThemeCatalog.php';
$css=file_get_contents(dirname(__DIR__,2).'/public/assets/css/studio-theme.css');preg_match_all('/--([a-z0-9-]+):var\(--color-([a-z-]+)\)/',$css,$matches,PREG_SET_ORDER);$aliases=[];$palette=App\Services\StudioThemeCatalog::palette('studio-forest');foreach($matches as$m){if(!isset($palette[$m[2]]))throw new RuntimeException('Unknown color authority:'.$m[2]);$aliases[$m[1]]=$m[2];}
$expected=['background'=>'canvas','foreground'=>'ink','card'=>'surface','card-foreground'=>'ink','popover'=>'surface','popover-foreground'=>'ink','primary'=>'action','primary-foreground'=>'on-action','secondary'=>'surface-strong','secondary-foreground'=>'ink','muted'=>'surface-tonal','muted-foreground'=>'ink-muted','accent'=>'selected-soft','accent-foreground'=>'on-selected','destructive'=>'danger','destructive-foreground'=>'on-action','border'=>'line','input'=>'control-border','ring'=>'focus','sidebar'=>'frame','sidebar-foreground'=>'on-frame','sidebar-primary'=>'selected','sidebar-primary-foreground'=>'on-selected','sidebar-accent'=>'frame-hover','sidebar-accent-foreground'=>'on-frame','sidebar-border'=>'frame-raised','sidebar-ring'=>'focus','chart-1'=>'action','chart-2'=>'avatar-copper-ink','chart-3'=>'info','chart-4'=>'warning','chart-5'=>'ink-muted'];foreach($expected as$key=>$role)if(($aliases[$key]??null)!==$role)throw new RuntimeException('Semantic alias drift:'.$key);
$consumerChecks=0;
foreach(glob(dirname(__DIR__,2).'/public/assets/css/studio*.css') as $path){
    $source=file_get_contents($path);
    if(preg_match('/(?<![-\w])(?:color|fill|stroke)\s*:\s*var\(--muted\)/',$source))throw new RuntimeException('Surface token used as foreground:'.basename($path));
    if(preg_match('/--muted\s*:\s*var\(--color-ink-muted\)/',$source))throw new RuntimeException('Legacy alias shadows semantic surface:'.basename($path));
    $consumerChecks++;
}
$forms=file_get_contents(dirname(__DIR__,2).'/public/assets/css/studio-forms.css');
if(!str_contains($forms,'.form-section :where(p){'))throw new RuntimeException('Form helper specificity must allow explicit warning/error roles');
echo json_encode(['passed'=>count($expected)+$consumerChecks+1,'semantic_aliases'=>count($expected),'consumer_stylesheets'=>$consumerChecks,'scope'=>'Static token ownership and foreground/surface collision guard; actual computed consumer contrast is separate']),PHP_EOL;
