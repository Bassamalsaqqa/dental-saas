<?php
// CLI-only, DB-free registration/search integration fixture. Never uses real patient records.
if (PHP_SAPI !== 'cli') exit(2);
define('APPPATH', dirname(__DIR__, 3).'/app/');
$locale=($argv[1]??'en')==='ar'?'ar':'en';
$mode=$argv[2]??'page';
$canView=($argv[3]??'allowed')==='allowed';
$translations=[];
foreach(['Studio','UI'] as $group)$translations[$group]=require APPPATH.'Language/'.$locale.'/'.$group.'.php';
function lang($key,$args=[]){[$group,$name]=explode('.',$key,2);$text=$GLOBALS['translations'][$group][$name]??$key;foreach($args as $k=>$v)$text=str_replace('{'.$k.'}',(string)$v,$text);return $text;}
function esc($value,$context=null){return htmlspecialchars((string)$value,ENT_QUOTES|ENT_SUBSTITUTE,'UTF-8');}
function old($key,$fallback=''){return $fallback;}
function has_permission($module,$action){return $action!=='view'||$GLOBALS['canView'];}
function settings(){return new class{public function get($key,$default=null){return $default;}};}
function base_url($path=''){return 'http://127.0.0.1:57526/'.ltrim($path,'/');}
function workflow_context_url($path,$return=null){return base_url($path);}
function csrf_field(){return '<input type="hidden" name="csrf_test_name" value="synthetic">';}
function asset_version($path){return 'synthetic';}
function helper($name){}
function view($path,$data=[]){extract($data);ob_start();include APPPATH.'Views/'.$path.'.php';return ob_get_clean();}
$studioIcon=static fn($name)=>'';
?>
<!doctype html><html lang="<?= $locale ?>" dir="<?= $locale==='ar'?'rtl':'ltr' ?>"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Registration search fixture</title>
<?php foreach(['studio','studio-theme','studio-workspace','studio-forms'] as $css): ?><link rel="stylesheet" href="/assets/css/<?= $css ?>.css"><?php endforeach; ?></head>
<body class="studio"><div id="application"><nav id="studio-nav"><div class="nav-groups"></div></nav><div class="workspace" id="studio-workspace"><main id="main" class="canvas">
<?php if($mode==='dialog'): ?>
<a href="/patient/create" data-patient-create-open>Register synthetic patient</a>
<?= view('patient/partials/studio_intake_dialog',['studioCanCreatePatient'=>true,'intakeReturn'=>'patients','studioIcon'=>$studioIcon]) ?>
<?php else: ?>
<form method="post" action="/patient/store" class="studio-form" data-patient-create-form>
<?= view('patient/partials/studio_create_fields',['studioEditing'=>$mode==='edit']) ?>
<button type="submit">Save fixture</button></form>
<?php endif; ?>
</main></div></div>
<?php if($canView): ?>
<dialog id="search-dialog" class="command-dialog" aria-labelledby="search-title"><header><h2 id="search-title"><?= esc(lang('Studio.search')) ?></h2><button type="button" data-close><?= esc(lang('Studio.close')) ?></button></header>
<input id="patient-search" type="search" aria-label="<?= esc(lang('Studio.searchHint')) ?>" data-url="/api/search/patients" data-record-base="/patient/" data-empty="<?= esc(lang('Studio.searchEmpty')) ?>" data-error="<?= esc(lang('Studio.searchFail')) ?>">
<p data-search-status role="status" data-loading="Loading" data-ready="Ready" data-denied="Denied" data-previous="Previous"></p><div id="search-results"></div></dialog>
<?php endif; ?>
<script src="/assets/js/studio-loading.js"></script><script src="/assets/js/studio.js"></script>
</body></html>
