<?php
declare(strict_types=1);
$bootstrap=getopt('',['bootstrap:'])['bootstrap']??'';
if(PHP_SAPI!=='cli'||!is_file($bootstrap))exit(2);
require $bootstrap;
$i=$db->query('SELECT DATABASE() db,@@port port,@@datadir datadir,CURRENT_USER() actor')->getRowArray();
if($db->hostname!=='127.0.0.1'||(int)$i['port']!==3307||!str_starts_with($i['db'],'m3m4_host_schema_')||!str_starts_with($i['actor'],'m2c_test@')||!str_contains(str_replace('\\','/',$i['datadir']),'denta-m2c-20261021/disposable-mariadb/data'))throw new RuntimeException('Owned synthetic database required');
$n=0;$ok=function(bool$v,string$why)use(&$n){if(!$v)throw new RuntimeException($why);$n++;};
$model=new App\Models\FileAttachmentModel($db);$before=[];
foreach(['file_attachments','attachment_variants']as$t)$before[$t]=$db->table($t)->orderBy('id')->get()->getResultArray();
$base=$model->getTotalSizeByClinic(1);$other=$model->getTotalSizeByClinic(2);$run='quota_'.bin2hex(random_bytes(6));$now=date('Y-m-d H:i:s');$ids=[];
$fixtures=[[1,'same',100,$now],[1,'same',100,null],[1,'same',120,$now],[1,'different',100,null],[1,'Case',40,null],[1,'case',60,null],[1,'',10,null],[1,'',20,$now],[2,'same',900,null]];
foreach($fixtures as[$clinic,$path,$size,$deleted]){
 $row=['clinic_id'=>$clinic,'file_name'=>$run,'original_name'=>'synthetic.txt','file_path'=>$path===''?'':$run.'/'.$path,'mime_type'=>'text/plain','file_size'=>$size,'entity_type'=>'quota_probe','entity_id'=>1,'purpose'=>'quota_probe','created_by'=>1,'created_at'=>$now,'updated_at'=>$now,'deleted_at'=>$deleted];
 if(!$db->table('file_attachments')->insert($row))throw new RuntimeException('Fixture failed');$ids[]=(int)$db->insertID();
}
$ok($model->getTotalSizeByClinic(1)===$base+350,'Retained duplicate paths use maximum once; equal-sized distinct paths and case distinctions preserved; blank paths charged separately');
$ok($model->getTotalSizeByClinic(2)===$other+900,'Identical path in another clinic isolated');
$newRows=$db->table('file_attachments')->whereIn('id',$ids)->orderBy('id')->get()->getResultArray();
$variantIds=[];
foreach([[1,'same',200],[1,'same',250],[1,'different',250],[1,'Case',20],[1,'case',30],[1,'',7],[1,'',9],[2,'same',800]]as$k=>[$clinic,$path,$size]){
 $row=['clinic_id'=>$clinic,'attachment_id'=>$clinic===1?$ids[0]:$ids[8],'kind'=>'thumbnail','source_sha256'=>hash('sha256',$run.':'.$k),'file_sha256'=>hash('sha256',$run.':variant:'.$k),'relative_path'=>$path===''?'':$run.'/variants/'.$path,'mime_type'=>'image/webp','file_size'=>$size,'width'=>1,'height'=>1,'created_at'=>$now];
 if(!$db->table('attachment_variants')->insert($row))throw new RuntimeException('Variant fixture failed');$variantIds[]=(int)$db->insertID();
}
$ok($model->getTotalSizeByClinic(1)===$base+350+566,'Variant exact paths deduplicated with conservative size/case/unknown-path semantics');
$ok($model->getTotalSizeByClinic(2)===$other+900+800,'Variant clinic isolation');
$ok($model->getTotalSizeByClinic(1)===$model->getTotalSizeByClinic(1),'Repeated quota reads stable');
$ok($newRows===$db->table('file_attachments')->whereIn('id',$ids)->orderBy('id')->get()->getResultArray(),'No historical metadata reclassification');
foreach($before as$t=>$rows){$added=$t==='file_attachments'?$ids:$variantIds;$ok($rows===$db->table($t)->whereNotIn('id',$added)->orderBy('id')->get()->getResultArray(),'Pre-existing '.$t.' preserved');}
echo json_encode(['status'=>'PASS','checks'=>$n,'source'=>'actual FileAttachmentModel','database'=>$i['db'],'schema_changes'=>0,'data_rewrites'=>0,'fixtures_retained'=>true]),PHP_EOL;
