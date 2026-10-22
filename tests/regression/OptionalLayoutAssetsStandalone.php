<?php
declare(strict_types=1);
namespace CodeIgniter { class Controller {} }
namespace {
function view($name,$data,$options){ return json_encode($data); }
require __DIR__.'/../../app/Controllers/BaseController.php';
class AssetController extends App\Controllers\BaseController {
    protected function isLoggedIn() { return false; }
    public function renderAssetData(string $name,array $data=[]):array { return json_decode($this->view($name,$data+['clinic'=>[]]),true); }
}
$c=new AssetController();$n=0;
function ck(bool $ok,string $why):void{global $n;if(!$ok)throw new RuntimeException($why);$n++;}
foreach(['patient/show','inventory/index','finance/show','treatment/show']as$v){$d=$c->renderAssetData($v);ck(!$d['loadChartJs']&&!$d['loadFullCalendar'],'No unused graph/calendar vendors on '.$v);}
foreach(['dashboard/index','reports/index','finance/reports']as$v){$d=$c->renderAssetData($v);ck($d['loadChartJs']&&!$d['loadFullCalendar'],'Chart consumers retained '.$v);}
foreach(['appointment/calendar','examination/calendar']as$v){$d=$c->renderAssetData($v);ck(!$d['loadChartJs']&&$d['loadFullCalendar'],'Calendar consumers retained '.$v);}
$d=$c->renderAssetData('patient/show',['loadChartJs'=>true]);ck($d['loadChartJs'],'Explicit future consumer supported');
$layout=file_get_contents(__DIR__.'/../../app/Views/layouts/main_auth.php');
$start=strpos($layout,'    <?php if ($loadChartJs ?? true): ?>');$end=strpos($layout,'    <script src="https://code.jquery.com/', $start);$block=substr($layout,$start,$end-$start);
foreach([[false,false,0],[true,false,1],[false,true,1],[true,true,2]]as[$loadChartJs,$loadFullCalendar,$count]){ob_start();eval('?>'.$block);$html=ob_get_clean();ck(substr_count($html,'<script src=')===$count,'Actual layout vendor conditional');}
echo json_encode(['status'=>'PASS','checks'=>$n,'scope'=>'Actual BaseController view dispatch and layout vendor branches; no auth bypass or network']),PHP_EOL;
}
