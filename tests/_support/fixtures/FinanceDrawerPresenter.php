<?php
// CLI-only synthetic presenter: real shared views/assets; no app or DB bootstrap.
if(PHP_SAPI!=='cli')exit(2);
define('APPPATH',dirname(__DIR__,3).'/app/');
$locale=($argv[1]??'en')==='ar'?'ar':'en';
$translations=[];
foreach(['Finance','Studio','UI','Provider'] as $group)$translations[$group]=require APPPATH.'Language/'.$locale.'/'.$group.'.php';
function helper($name) {}
function esc($value,$context=null){return htmlspecialchars((string)$value,ENT_QUOTES|ENT_SUBSTITUTE,'UTF-8');}
function lang($key,$args=[]){[$group,$name]=explode('.',$key,2);$value=$GLOBALS['translations'][$group][$name]??$key;foreach($args as $k=>$v)$value=str_replace('{'.$k.'}',(string)$v,$value);return $value;}
function base_url($path=''){return 'http://127.0.0.1:57526/'.ltrim($path,'/');}
function asset_version($path){return 'synthetic-review';}
function has_permission($module,$action){return true;}
function csrf_field(){return '<input type="hidden" name="csrf_test_name" value="synthetic">';}
function csrf_hash(){return 'synthetic';}
function csrf_token(){return 'csrf_test_name';}
function csrf_header(){return 'X-CSRF-TOKEN';}
class FinanceReviewView {
    public function extend($layout){return '';}
    private string $sectionName='';
    public function section($name){$this->sectionName=$name;ob_start();return '';}
    public function endSection(){$content=ob_get_clean();if($this->sectionName==='scripts'){$GLOBALS['reviewScripts']=($GLOBALS['reviewScripts']??'').$content;return '';}return $content;}
    public function render($path,$data){extract($data);ob_start();include APPPATH.'Views/'.$path.'.php';return ob_get_clean();}
}
function view($path,$data=[]){return (new FinanceReviewView())->render($path,$data);}
function service($name){return new class {public function getUri(){return new class {public function getPath(){return 'finance/payouts';}};}};}
require APPPATH.'Services/ProviderCurrencySummary.php';
$stats=\App\Services\ProviderCurrencySummary::summarize([
 ['id'=>1,'finance_id'=>101,'currency'=>'ILS','gross_amount'=>1000,'provider_share_amount'=>400,'provider_paid_amount'=>0,'provider_balance_due'=>400,'status'=>'unpaid'],
 ['id'=>2,'finance_id'=>102,'currency'=>'USD','gross_amount'=>500,'provider_share_amount'=>250,'provider_paid_amount'=>100,'provider_balance_due'=>150,'status'=>'partial']
]);
function config($name) { return (object)['enabled' => false, 'cookieName' => 'csrf_cookie_name', 'headerName' => 'X-CSRF-TOKEN', 'tokenName' => 'csrf_test_name']; }
function settings() {
    return new class {
        public function get($key, $default = null) {
            return ['currency' => 'ILS', 'timezone' => 'Asia/Hebron'][$key] ?? $default;
        }
    };
}
?><!doctype html><html lang="<?= $locale ?>" dir="<?= $locale==='ar'?'rtl':'ltr' ?>"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><meta name="csrf-name" content="csrf_test_name"><meta name="csrf-token" content="synthetic"><meta name="csrf-header" content="X-CSRF-TOKEN"><?php foreach(['studio','studio-theme','studio-workspace','studio-scale','studio-forms','studio-money'] as $css): ?><link rel="stylesheet" href="/assets/css/<?= $css ?>.css"><?php endforeach; ?></head><body class="studio"><main style="padding:24px"><?= view('finance/payouts',['stats'=>$stats]) ?><?= view('components/studio_settlement_assets') ?></main><script src="/assets/js/studio-loading.js" defer></script><script src="/assets/js/studio-grid.js" defer></script><?= $GLOBALS['reviewScripts']??'' ?></body></html>
