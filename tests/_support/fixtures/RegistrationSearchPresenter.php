<?php
// CLI-only, DB-free registration/search integration fixture. Never uses real patient records.
if (PHP_SAPI !== 'cli') exit(2);
define('APPPATH', dirname(__DIR__, 3).'/app/');
$locale=($argv[1]??'en')==='ar'?'ar':'en';
$mode=$argv[2]??'page';
$canView=($argv[3]??'allowed')==='allowed';
$translations=[];
foreach(['Studio','UI','Inventory'] as $group)$translations[$group]=require APPPATH.'Language/'.$locale.'/'.$group.'.php';
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
class RegistrationFixtureView {
 private array $data=[];private string $section='';
 public function getData(){return $this->data;}
 public function setVar($name,$value){$this->data[$name]=$value;return $this;}
 public function extend($layout){return '';}
 public function section($name){$this->section=$name;ob_start();return '';}
 public function endSection(){$content=ob_get_clean();return $content;}
 public function render($path,$data){$this->data=$data;extract($data);ob_start();include APPPATH.'Views/'.$path.'.php';return ob_get_clean();}
}
function view($path,$data=[]){return (new RegistrationFixtureView())->render($path,$data);}
function session($key=null){return null;}
function workflow_back($path,$return=null){return base_url($path);}
function formatCurrency($amount){return 'ILS '.number_format((float)$amount,2);}
function formatDate($date){return (string)$date;}
function formatDateTime($date){return (string)$date;}
$studioIcon=static fn($name)=>'';
?>
<!doctype html><html lang="<?= $locale ?>" dir="<?= $locale==='ar'?'rtl':'ltr' ?>"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Registration search fixture</title>
<?php foreach(['studio','studio-theme','studio-workspace','studio-forms','studio-item-detail'] as $css): ?><link rel="stylesheet" href="/assets/css/<?= $css ?>.css"><?php endforeach; ?></head>
<body class="studio"><div class="application" id="application"><aside class="navigation" id="studio-nav"><div class="nav-groups"></div></aside><div class="workspace" id="studio-workspace"><main id="main" class="canvas">
<?php if($mode==='inventory'): ?>
<?= view('inventory/show',['item'=>['id'=>901,'item_name'=>'Synthetic stock item','quantity'=>4,'min_quantity'=>2,'unit_price'=>10,'unit'=>'pieces','category'=>'consumables','status'=>'active'],'supplier_links'=>[],'movements'=>[],'batches'=>[],'suppliers'=>[]]) ?>
<?php elseif($mode==='dialog'): ?>
<a href="/patient/create" data-patient-create-open>Register synthetic patient</a>
<?= view('patient/partials/studio_intake_dialog',['studioCanCreatePatient'=>true,'intakeReturn'=>'patients','studioIcon'=>$studioIcon]) ?>
<?php else: ?>
<form method="post" action="/patient/store" class="studio-form" data-patient-create-form data-unsaved-form>
<?= csrf_field() ?>
<?= view('patient/partials/studio_create_fields',['studioEditing'=>$mode==='edit']) ?>
<footer class="form-actions"><a class="button secondary" href="<?= base_url('patients') ?>" data-form-cancel><?= esc(lang('UI.cancel')) ?></a><button type="submit">Save fixture</button></footer></form>
<dialog id="form-discard-dialog" class="help-dialog" aria-labelledby="discard-title"><header><div><h2 id="discard-title"><?= esc(lang('Studio.discardTitle')) ?></h2><p><?= esc(lang('Studio.discardHelp')) ?></p></div><button class="icon-button" data-close aria-label="<?= esc(lang('Studio.close')) ?>"></button></header><div class="discard-actions"><button class="button secondary" type="button" data-discard-stay><?= esc(lang('Studio.keepEditing')) ?></button><button class="button primary" type="button" data-discard-confirm><?= esc(lang('Studio.discardChanges')) ?></button></div></dialog>
<?= view('patient/partials/create_form_script') ?>
<script src="<?= base_url('assets/js/studio-forms.js') ?>" defer></script>
<?php endif; ?>
</main></div></div>
<?php if($canView): ?>
<dialog id="search-dialog" class="command-dialog" aria-labelledby="search-title"><header><h2 id="search-title"><?= esc(lang('Studio.search')) ?></h2><button type="button" data-close><?= esc(lang('Studio.close')) ?></button></header>
<input id="patient-search" type="search" aria-label="<?= esc(lang('Studio.searchHint')) ?>" data-url="/api/search/patients" data-record-base="/patient/" data-empty="<?= esc(lang('Studio.searchEmpty')) ?>" data-error="<?= esc(lang('Studio.searchFail')) ?>">
<p data-search-status role="status" data-loading="<?= esc(lang('Studio.refreshingList')) ?>" data-ready="<?= esc(lang('Studio.listReady')) ?>" data-denied="<?= esc(lang('Studio.requestDenied')) ?>" data-previous="<?= esc(lang('Studio.previousResults')) ?>"></p><div id="search-results"></div></dialog>
<?php endif; ?>
<script src="/assets/js/studio-loading.js"></script><script src="/assets/js/studio.js"></script>
</body></html>
