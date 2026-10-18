<?php
$root=$argv[1]??dirname(__DIR__);if(!is_dir($root.'/system')||is_file($root.'/.env'))throw new RuntimeException('Expected isolated source without .env');
define('ENVIRONMENT','production');define('CI_DEBUG',false);define('FCPATH',$root.'/public/');require $root.'/app/Config/Paths.php';$paths=new Config\Paths();$ownedBase=sys_get_temp_dir().'/denta-framework-475-'.bin2hex(random_bytes(5));mkdir($ownedBase,0700);foreach(['cache','logs','session']as$d)mkdir($ownedBase.'/'.$d,0700);$paths->writableDirectory=$ownedBase;require $root.'/system/Boot.php';CodeIgniter\Boot::bootConsole($paths);
class S1OwnedUploadsRequest extends CodeIgniter\HTTP\IncomingRequest{
 public array $one=[];public array $many=[];public function __construct(){}
 public function getFile(string $name):?CodeIgniter\HTTP\Files\UploadedFile{return $this->one[$name]??null;}
 public function getFileMultiple(string $name):?array{return $this->many[$name]??null;}
}
$dir=$ownedBase.'/uploads';if(!mkdir($dir,0700))throw new RuntimeException('Owned fixture dir failed');
$image=imagecreatetruecolor(2,2);imagejpeg($image,$dir.'/valid.jpg');imagepng($image,$dir.'/valid.png');imagedestroy($image);file_put_contents($dir.'/valid.pdf',"%PDF-1.4\n1 0 obj<</Type/Catalog>>endobj\n%%EOF\n");
$request=new S1OwnedUploadsRequest();$rules=new CodeIgniter\Validation\StrictRules\FileRules($request);$checks=[];
$upload=function(string$path,string$name,int$error=UPLOAD_ERR_OK,?int$size=null){return new CodeIgniter\HTTP\Files\UploadedFile($path,$name,'application/octet-stream',$size??filesize($path),$error);};
foreach(['jpg','jpeg','png','pdf']as$ext){$file=$ext==='jpeg'?'jpg':$ext;$request->one['clinical']=$upload($dir.'/valid.'.$file,'clinical.'.$ext);$ok=$rules->mime_in(null,'clinical,image/jpeg,image/png,application/pdf')&&$rules->ext_in(null,'clinical,jpg,jpeg,png,pdf');if($ext!=='pdf')$ok=$ok&&$rules->is_image(null,'clinical');if(!$ok)throw new RuntimeException('Valid clinical '.$ext.' rejected');$checks['valid_'.$ext]=true;}
foreach(['clinical.php.jpg','clinical.jpg.','clinical.php.png','clinical.pdf.']as$name){$file=str_contains($name,'pdf')?'pdf':(str_contains($name,'png')?'png':'jpg');$request->one['clinical']=$upload($dir.'/valid.'.$file,$name);$ok=$rules->mime_in(null,'clinical,image/jpeg,image/png,application/pdf')&&$rules->ext_in(null,'clinical,jpg,jpeg,png,pdf');if($ok)throw new RuntimeException('Unsafe client name accepted');$checks['reject_'.$name]=true;}
$request->many['clinical']=[$upload($dir.'/valid.jpg','',UPLOAD_ERR_NO_FILE,0),$upload($dir.'/valid.jpg','oversize.jpg',UPLOAD_ERR_OK,1000000)];if($rules->max_size(null,'clinical,10'))throw new RuntimeException('Empty first upload bypassed max_size');$checks['empty_first_multi_upload_rejected']=true;
helper('security');$safe=sanitize_filename('../../public/shell.php');if(str_contains($safe,'/')||str_contains($safe,'\\')||str_contains($safe,'..'))throw new RuntimeException('Filename traversal not removed');$checks['traversal_sanitized']=true;
if(CodeIgniter\CodeIgniter::CI_VERSION!=='4.7.5')throw new RuntimeException('Unexpected framework');
if(!isset(service('routes')->getRoutes('GET')['auth/login']))throw new RuntimeException('Missing application route');$checks['application_routes']=true;
$response=Config\Services::response(null,false);$response->setJSON(['ok'=>true,'ar'=>'مرحبا']);if(json_decode($response->getBody(),true)['ar']!=='مرحبا')throw new RuntimeException('JSON encoding failed');$checks['json_format_config']=true;
$renderer=Config\Services::renderer(null,null,false);if($renderer->setData(['view'=>'<?php echo "INJECTED"; ?>'])->renderString('SAFE')!=='SAFE')throw new RuntimeException('View data replaced source');$checks['view_reserved_key_safe']=true;
$parser=Config\Services::parser(null,null,false);if($parser->setData(['template'=>'<?php echo "INJECTED"; ?>'])->renderString('SAFE')!=='SAFE')throw new RuntimeException('Parser data replaced source');$checks['parser_reserved_key_safe']=true;
$parser=Config\Services::parser(null,null,false);$parsed=$parser->setData(['first'=>'{!second!}','second'=>'<script>alert(1)</script>'])->renderString('{first} {second}');if(str_contains($parsed,'<script>')||!str_contains($parsed,'{!second!}'))throw new RuntimeException('Parser recursively expanded user data');$checks['parser_resubstitution_safe']=true;
$serverBefore=$_SERVER;
foreach([
 'direct_tls'=>[['REMOTE_ADDR'=>'203.0.113.2','HTTPS'=>'on'],[],true],
 'forged_xfp'=>[['REMOTE_ADDR'=>'203.0.113.2','HTTP_X_FORWARDED_PROTO'=>'https'],[],false],
 'trusted_xfp'=>[['REMOTE_ADDR'=>'127.0.0.1','HTTP_X_FORWARDED_PROTO'=>'https'],['127.0.0.1'=>'X-Forwarded-For'],true],
 'untrusted_xfp'=>[['REMOTE_ADDR'=>'203.0.113.2','HTTP_X_FORWARDED_PROTO'=>'https'],['127.0.0.1'=>'X-Forwarded-For'],false],
 'trusted_cidr'=>[['REMOTE_ADDR'=>'192.168.2.3','HTTP_X_FORWARDED_PROTO'=>'https'],['192.168.0.0/16'=>'X-Forwarded-For'],true],
]as$name=>[$server,$proxy,$expected]){$_SERVER=$server;$config=new Config\App();$config->proxyIPs=$proxy;$req=new CodeIgniter\HTTP\IncomingRequest($config,new CodeIgniter\HTTP\URI('http://localhost/'),'php://input',new CodeIgniter\HTTP\UserAgent());$req->setGlobal('server',$server);if($req->isSecure()!==$expected)throw new RuntimeException($name.' proxy trust failed');$checks[$name]=true;}
$_SERVER=$serverBefore;
echo json_encode(['status'=>'PASS','framework'=>CodeIgniter\CodeIgniter::CI_VERSION,'checks'=>$checks,'fixture_dir'=>$dir,'no_database_calls'=>true],JSON_PRETTY_PRINT),PHP_EOL;