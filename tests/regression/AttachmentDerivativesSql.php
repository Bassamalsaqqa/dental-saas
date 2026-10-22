<?php
declare(strict_types=1);
$bootstrap=getopt('',['bootstrap:'])['bootstrap']??'';if(PHP_SAPI!=='cli'||!is_file($bootstrap))exit(2);require$bootstrap;
$i=$db->query('SELECT DATABASE() db,@@port port,@@datadir datadir')->getRowArray();if($db->hostname!=='127.0.0.1'||$i['db']!=='m2c_finance_acceptance'||(int)$i['port']!==3307||!str_contains(str_replace('\\','/',$i['datadir']),'denta-m2c-20261021/disposable-mariadb/data'))throw new RuntimeException('Owned synthetic target required');
$prefix='media_'.bin2hex(random_bytes(5)).'_';$cfg=config('Database')->default;$cfg['DBPrefix']=$prefix;$test=Config\Database::connect($cfg,false);$test->initialize();
$test->query('CREATE TABLE `'.$prefix.'file_attachments`(id INT PRIMARY KEY,clinic_id INT,file_path VARCHAR(255),mime_type VARCHAR(50),file_size INT,deleted_at DATETIME NULL) ENGINE=InnoDB');
$test->query('CREATE TABLE `'.$prefix.'clinics`(id INT PRIMARY KEY) ENGINE=InnoDB');$test->query('INSERT INTO `'.$prefix.'clinics` VALUES(1),(2)');
require$root.'/app/Database/Migrations/2026-10-22-160000_CreateAttachmentVariants.php';$migration=new App\Database\Migrations\CreateAttachmentVariants(Config\Database::forge($test));$migration->up();
$base=sys_get_temp_dir().'/denta-derivatives-'.bin2hex(random_bytes(8));mkdir($base.'/clinic_1',0700,true);mkdir($base.'/clinic_2',0700,true);
$image=imagecreatetruecolor(2000,1000);imagefilledrectangle($image,0,0,1999,999,imagecolorallocate($image,40,120,200));imagepng($image,$base.'/clinic_1/original.png');imagedestroy($image);$hash=hash_file('sha256',$base.'/clinic_1/original.png');
$image=imagecreatetruecolor(80,40);imagejpeg($image,$base.'/clinic_1/small.jpg');imagedestroy($image);$jpegHash=hash_file('sha256',$base.'/clinic_1/small.jpg');
$test->table('file_attachments')->insertBatch([['id'=>1,'clinic_id'=>1,'file_path'=>'clinic_1/original.png','mime_type'=>'image/png','file_size'=>filesize($base.'/clinic_1/original.png')],['id'=>2,'clinic_id'=>1,'file_path'=>'clinic_1/small.jpg','mime_type'=>'image/jpeg','file_size'=>filesize($base.'/clinic_1/small.jpg')],['id'=>3,'clinic_id'=>2,'file_path'=>'clinic_1/original.png','mime_type'=>'image/png','file_size'=>123]]);
class SyntheticDerivativeQuota extends App\Services\PlanGuard{public array$calls=[];public bool$deny=false;public function __construct(){}public function assertQuota($clinic,$metric,$delta=1,array$meta=[]){$this->calls[]=$delta;if($this->deny)throw new DomainException('Synthetic quota denied');}}
$quota=new SyntheticDerivativeQuota();$s=new App\Services\AttachmentDerivativeService($test,$base,$quota);$n=0;function ck(bool$v,string$m):void{global$n;if(!$v)throw new RuntimeException($m);$n++;}function deny(callable$f,string$m):void{try{$f();}catch(Throwable$e){ck(true,$m);return;}throw new RuntimeException($m);}
config('MediaDerivatives')->enabled=false;deny(fn()=>$s->build(1,1,'thumbnail'),'Default-off builder refused');config('MediaDerivatives')->enabled=true;
$thumb=$s->build(1,1,'thumbnail');ck((int)$thumb['width']===480&&(int)$thumb['height']===240,'Bounded proportional thumbnail');$preview=$s->build(1,1,'preview');ck((int)$preview['width']===1600&&(int)$preview['height']===800,'Bounded preview');
ck(hash_file('sha256',$base.'/clinic_1/original.png')===$hash,'Original bytes preserved');$count=$test->table('attachment_variants')->countAllResults();$again=$s->build(1,1,'thumbnail');ck($again['id']===$thumb['id']&&$test->table('attachment_variants')->countAllResults()===$count,'Equivalent rebuild returns same metadata and artifact');
$small=$s->build(1,2,'thumbnail');ck((int)$small['width']===80&&(int)$small['height']===40,'No upscaling');ck(hash_file('sha256',$base.'/clinic_1/small.jpg')===$jpegHash,'JPEG original retained');
$before=$test->table('attachment_variants')->orderBy('id')->get()->getResultArray();deny(fn()=>$s->build(2,1,'thumbnail'),'Foreign attachment ID denied');deny(fn()=>$s->build(2,3,'thumbnail'),'Foreign physical tenant path denied');deny(fn()=>$s->build(1,1,'other'),'Unsupported variant denied');
$quota->deny=true;deny(fn()=>$s->build(1,2,'preview'),'Quota denial before publication');ck($before===$test->table('attachment_variants')->orderBy('id')->get()->getResultArray(),'Denied operations preserve metadata');$quota->deny=false;
$old=config('MediaDerivatives')->maxSourcePixels;config('MediaDerivatives')->maxSourcePixels=1;deny(fn()=>$s->build(1,2,'preview'),'Decode bounds refuse before raster allocation');config('MediaDerivatives')->maxSourcePixels=$old;
$originalVariant=file_get_contents($thumb['absolute_path']);file_put_contents($thumb['absolute_path'],'corrupt');deny(fn()=>$s->lookup(1,1,'thumbnail'),'Tampered derivative fails closed');file_put_contents($thumb['absolute_path'],$originalVariant);
$test->table('attachment_variants')->where('id',$thumb['id'])->update(['mime_type'=>'text/html']);deny(fn()=>$s->lookup(1,1,'thumbnail'),'Active MIME metadata rejected');$test->table('attachment_variants')->where('id',$thumb['id'])->update(['mime_type'=>$thumb['mime_type']]);
$model=new App\Models\FileAttachmentModel($test);$expected=(int)$test->table('file_attachments')->selectSum('file_size','n')->where('clinic_id',1)->get()->getRowArray()['n']+(int)$test->table('attachment_variants')->selectSum('file_size','n')->where('clinic_id',1)->get()->getRowArray()['n'];ck($model->getTotalSizeByClinic(1)===$expected,'Derivative bytes counted in retained quota');
// Inject failed metadata persistence after real physical publication; old evidence remains.
$fault=new class($cfg) extends CodeIgniter\Database\MySQLi\Connection {
    public function query(string$sql,$binds=null,bool$setEscapeFlags=true,string$queryClass='') {
        if(str_starts_with($sql,'INSERT INTO')&&str_contains($sql,'attachment_variants'))return false;
        return parent::query($sql,$binds,$setEscapeFlags,$queryClass);
    }
};$fault->initialize();$failed=new App\Services\AttachmentDerivativeService($fault,$base,$quota);
$priorRows=$test->table('attachment_variants')->orderBy('id')->get()->getResultArray();
deny(fn()=>$failed->build(1,2,'preview'),'Injected metadata failure rejected');
ck($priorRows===$test->table('attachment_variants')->orderBy('id')->get()->getResultArray(),'Metadata failure rolls back ledger state');
$retry=$s->build(1,2,'preview');ck(is_file($retry['absolute_path'])&&hash_equals($retry['file_sha256'],hash_file('sha256',$retry['absolute_path'])),'Explicit retry verifies/adopts equivalent published artifact');
ck(hash_file('sha256',$base.'/clinic_1/small.jpg')===$jpegHash,'Publication recovery preserves original');
// Both processes finish real rendering before either enters the clinic publication lock.
$test->table('file_attachments')->insert(['id'=>4,'clinic_id'=>1,'file_path'=>'clinic_1/original.png','mime_type'=>'image/png','file_size'=>filesize($base.'/clinic_1/original.png')]);
$manifest=$base.'/worker.json';file_put_contents($manifest,json_encode(['prefix'=>$prefix,'base'=>$base]));$workers=[];
foreach(['a','b']as$actor){$pipes=[];$process=proc_open([PHP_BINARY,$root.'/tests/_support/attachment_derivative_worker.php','--bootstrap='.$bootstrap,'--manifest='.$manifest,'--actor='.$actor],[0=>['pipe','r'],1=>['pipe','w'],2=>['pipe','w']],$pipes,$root);if(!is_resource($process))throw new RuntimeException('Worker creation failed');fclose($pipes[0]);$workers[]=[$process,$pipes];}
$outcomes=[];foreach($workers as[$process,$pipes]){$stdout=stream_get_contents($pipes[1]);$stderr=stream_get_contents($pipes[2]);fclose($pipes[1]);fclose($pipes[2]);$exit=proc_close($process);if($exit!==0)throw new RuntimeException('Derivative worker failed: '.$stdout.$stderr);$outcomes[]=json_decode(trim($stdout),true,512,JSON_THROW_ON_ERROR);}
ck(is_file($base.'/ready-a')&&is_file($base.'/ready-b'),'Both actual renderers reached common barrier');
ck($outcomes[0]['id']===$outcomes[1]['id']&&$outcomes[0]['hash']===$outcomes[1]['hash'],'Concurrent workers return same verified winner');
ck($test->table('attachment_variants')->where('attachment_id',4)->countAllResults()===1,'Concurrent publication creates one metadata row');
ck(hash_file('sha256',$base.'/clinic_1/original.png')===$hash,'Concurrent rendering preserves original bytes');
// All eight EXIF orientations use an explicit synthetic four-colour quadrant fixture.
$im=imagecreatetruecolor(80,40);foreach([[0,0,39,19,255,0,0],[40,0,79,19,0,255,0],[0,20,39,39,0,0,255],[40,20,79,39,255,255,0]]as[$x1,$y1,$x2,$y2,$r,$g,$b])imagefilledrectangle($im,$x1,$y1,$x2,$y2,imagecolorallocate($im,$r,$g,$b));ob_start();imagejpeg($im,null,95);$jpeg=ob_get_clean();imagedestroy($im);
$expectedCorners=[1=>['r','g'],2=>['g','r'],3=>['y','b'],4=>['b','y'],5=>['r','b'],6=>['b','r'],7=>['y','g'],8=>['g','y']];
foreach(range(1,8)as$orientation){$exif="Exif\0\0II".pack('vVv',42,8,1).pack('vvVvvV',0x112,3,1,$orientation,0,0);$bytes=substr($jpeg,0,2)."\xff\xe1".pack('n',strlen($exif)+2).$exif.substr($jpeg,2);$name='orientation-'.$orientation.'.jpg';file_put_contents($base.'/clinic_1/'.$name,$bytes);$sourceHash=hash('sha256',$bytes);$test->table('file_attachments')->insert(['id'=>10+$orientation,'clinic_id'=>1,'file_path'=>'clinic_1/'.$name,'mime_type'=>'image/jpeg','file_size'=>strlen($bytes)]);$variant=$s->build(1,10+$orientation,'thumbnail');ck([(int)$variant['width'],(int)$variant['height']]===($orientation>=5?[40,80]:[80,40]),'EXIF dimensions '.$orientation);
$decoded=imagecreatefromstring(file_get_contents($variant['absolute_path']));$corners=[];foreach([.25,.75]as$x){$pixel=imagecolorsforindex($decoded,imagecolorat($decoded,(int)(imagesx($decoded)*$x),(int)(imagesy($decoded)*.25)));$corners[]=$pixel['red']>150&&$pixel['green']>150?'y':($pixel['red']>$pixel['green']&&$pixel['red']>$pixel['blue']?'r':($pixel['green']>$pixel['blue']?'g':'b'));}imagedestroy($decoded);ck($corners===$expectedCorners[$orientation],'EXIF pixel orientation '.$orientation);ck(hash_file('sha256',$base.'/clinic_1/'.$name)===$sourceHash,'EXIF original unchanged '.$orientation);}
$test->table('file_attachments')->where('id',1)->update(['deleted_at'=>'2026-10-22 12:00:00']);deny(fn()=>$s->lookup(1,1,'thumbnail'),'Retired parent denies derivative');ck(is_file($thumb['absolute_path'])&&hash_file('sha256',$base.'/clinic_1/original.png')===$hash,'Retirement preserves both original and derivative');
$retainedCount=$test->table('attachment_variants')->countAllResults();deny(fn()=>$migration->down(),'Nonempty schema rollback refused');ck($test->table('attachment_variants')->countAllResults()===$retainedCount,'Nonempty rollback keeps metadata');
config('MediaDerivatives')->enabled=false;
$report=$s->report(1,0,1);ck(count($report['items'])===1&&$report['has_more']&&$report['next_after_id']!==null,'Disabled read-only report explicitly paginated');
ck($report['items'][0]['status']==='unavailable_or_retired_parent','Report identifies retained retired-parent artifact without deletion');
ck(is_file($thumb['absolute_path']),'Report preserves physical evidence');
echo json_encode(['status'=>'PASS','checks'=>$n,'fixture_prefix'=>$prefix,'scratch'=>$base,'originals_preserved'=>true,'quota_seam'=>'Synthetic refusal seam; real metadata quota aggregation verified','scope'=>'Actual MariaDB/GD/physical publication, retry, bounds, tenant confinement, tampering, source retention; no production'],JSON_PRETTY_PRINT),PHP_EOL;
