<?php
declare(strict_types=1);

/** PR24 deny-only containment. CLI is pinned to one app; default is read-only. */
final class HostingerContainment
{
    public const SHA='9e1f8558a85b4a8956cd26c1e36b4e6767fb00d7';
    public const TREE='f927a6e8f1fea30333388c4ed924e3f446a31561';
    public const ORIGINAL='af27aced255b9699855d28a108bac66f7a985f09256295b00080a592b6764086';
    public const BASE='https://denta.palsync.net';
    public const APP='/home/u556956644/domains/palsync.net/public_html/denta';
    public const HOME='/home/u556956644';
    public const RULE='<FilesMatch "^(index_public\.php|redirect\.php|_debug_diff\.txt)$">';
    public const REWRITE="    # Denta legacy helper containment\nRewriteRule ^(index_public\\.php|redirect\\.php|_debug_diff\\.txt)$ - [F,L]\n";
    public const BLOCK24="    ".self::RULE."\n        Require all denied\n    </FilesMatch>\n";
    public const BLOCK22="    ".self::RULE."\n        Order allow,deny\n        Deny from all\n    </FilesMatch>\n";

    public static function candidate(string $original): string
    {
        if(hash('sha256',$original)!==self::ORIGINAL)throw new RuntimeException('Unrecognized original rewrite rules.');
        $anchor24="    # Block access to potentially dangerous scripts\n";
        $anchor22='    <FilesMatch "^(install|debug_env|repair_db.*|init_database|simple_login_test)\.php$">';
        if(substr_count($original,$anchor24)!==1||substr_count($original,$anchor22)!==2)throw new RuntimeException('Rewrite anchors are ambiguous.');
        $candidate=str_replace($anchor24,$anchor24.self::BLOCK24,$original);
        $position=strrpos($candidate,$anchor22);
        $candidate=substr_replace($candidate,self::BLOCK22,$position,0);
        if(substr_count($candidate,"RewriteEngine On\n")!==1)throw new RuntimeException('Rewrite engine anchor is ambiguous.');
        $candidate=str_replace("RewriteEngine On\n","RewriteEngine On\n".self::REWRITE,$candidate);
        if(str_replace([self::BLOCK24,self::BLOCK22,self::REWRITE],'',$candidate)!==$original)throw new RuntimeException('Unexpected rewrite change.');
        return $candidate;
    }

    private static function read(string $path): array
    {
        clearstatcache(true,$path);
        $stat=lstat($path);
        if(!$stat||is_link($path)||!is_file($path))throw new RuntimeException('Regular nonsymlink rewrite file required.');
        $bytes=file_get_contents($path);
        if($bytes===false)throw new RuntimeException('Cannot read rewrite file.');
        return ['bytes'=>$bytes,'hash'=>hash('sha256',$bytes),'mode'=>$stat['mode']&0777,'uid'=>$stat['uid'],'gid'=>$stat['gid'],'ino'=>$stat['ino']];
    }

    private static function writeExclusive(string $path,string $bytes,int $mode): void
    {
        $file=fopen($path,'xb');
        if(!$file)throw new RuntimeException('Cannot create exclusive file.');
        try {
            $written=0;
            while($written<strlen($bytes)){
                $n=fwrite($file,substr($bytes,$written));
                if(!$n)throw new RuntimeException('Incomplete file write.');
                $written+=$n;
            }
            if(!fflush($file)||!chmod($path,$mode))throw new RuntimeException('Cannot persist file or permissions.');
            if(function_exists('fsync')&&!fsync($file))throw new RuntimeException('Cannot sync file.');
        } finally {fclose($file);}
        if(file_get_contents($path)!==$bytes)throw new RuntimeException('File verification failed.');
    }

    private static function stage(string $app,string $bytes,array $original): string
    {
        $tmp=$app.'/.denta-containment-'.bin2hex(random_bytes(12));
        self::writeExclusive($tmp,$bytes,$original['mode']);
        $stat=self::read($tmp);
        if($stat['uid']!==$original['uid']||$stat['gid']!==$original['gid']||$stat['mode']!==$original['mode'])throw new RuntimeException('Cannot preserve rewrite owner/group/mode.');
        return $tmp;
    }

    /** Dependency callbacks are for isolated tests; the CLI below supplies real Git and HTTP. */
    public static function execute(string $app,string $privateRoot,bool $apply,callable $identity,callable $postflight,?callable $beforeSwap=null): array
    {
        if(realpath($app)!==$app||is_link($app)||!is_dir($app))throw new RuntimeException('Canonical nonsymlink app path required.');
        if($identity()!==[self::SHA,self::TREE])throw new RuntimeException('Installed Git identity drift.');
        $path=$app.'/.htaccess';$original=self::read($path);
        // Exact derived hash only: unrelated text containing a filename is not containment.
        $contained=$original['hash']!==self::ORIGINAL;
        if($contained){
            $stripped=str_replace([self::BLOCK24,self::BLOCK22,self::REWRITE],'',$original['bytes']);
            if(substr_count($original['bytes'],self::BLOCK24)!==1||substr_count($original['bytes'],self::BLOCK22)!==1||substr_count($original['bytes'],self::REWRITE)!==1||hash('sha256',$stripped)!==self::ORIGINAL)throw new RuntimeException('Rewrite hash drift or malformed containment.');
            if($apply&&!$postflight())throw new RuntimeException('Existing containment failed HTTP verification.');
            return ['mode'=>$apply?'verified-existing':'dry-run-existing','hash'=>$original['hash']];
        }
        $candidate=self::candidate($original['bytes']);
        if(!$apply)return ['mode'=>'dry-run','original'=>$original['hash'],'candidate'=>hash('sha256',$candidate),'insertions'=>[self::BLOCK24,self::BLOCK22,self::REWRITE]];
        if(realpath($privateRoot)!==$privateRoot||is_link($privateRoot)||!is_dir($privateRoot)||$privateRoot===$app||str_starts_with($privateRoot,$app.DIRECTORY_SEPARATOR))throw new RuntimeException('Existing canonical private root outside app required.');
        $oldMask=umask(0077);$replaced=false;$candidateHash=hash('sha256',$candidate);
        try {
            $backupDir=$privateRoot.'/.denta-containment-'.bin2hex(random_bytes(12));
            if(!mkdir($backupDir,0700)||!chmod($backupDir,0700))throw new RuntimeException('Private backup creation failed.');
            if(PHP_OS_FAMILY!=='Windows'&&(fileperms($backupDir)&0777)!==0700)throw new RuntimeException('Private directory mode verification failed.');
            $backup=$backupDir.'/htaccess.preimage';
            self::writeExclusive($backup,$original['bytes'],0600);
            self::writeExclusive($backupDir.'/metadata.json',json_encode(array_diff_key($original,['bytes'=>true]),JSON_THROW_ON_ERROR),0600);
            if(hash_file('sha256',$backup)!==$original['hash'])throw new RuntimeException('Preimage verification failed.');
            $temp=self::stage($app,$candidate,$original);
            if($beforeSwap)$beforeSwap();
            if($identity()!==[self::SHA,self::TREE]||self::read($path)!==$original)throw new RuntimeException('Late identity/file drift; no replacement.');
            if(!rename($temp,$path))throw new RuntimeException('Atomic replacement failed.');
            $replaced=true;
            $current=self::read($path);
            if($current['hash']!==$candidateHash||$current['mode']!==$original['mode']||$current['uid']!==$original['uid']||$current['gid']!==$original['gid']||!$postflight())throw new RuntimeException('Postflight failed.');
            return ['mode'=>'applied','hash'=>$candidateHash,'preimage'=>$backup,'original_mode'=>decoct($original['mode'])];
        } catch(Throwable $error){
            if($replaced){
                // Refuse to overwrite a third party change during recovery.
                if(self::read($path)['hash']!==$candidateHash||hash_file('sha256',$backup)!==$original['hash'])throw new RuntimeException('RECOVERY CONFLICT: manual intervention required.',0,$error);
                $recovery=self::stage($app,(string)file_get_contents($backup),$original);
                if(!rename($recovery,$path))throw new RuntimeException('RECOVERY FAILED: atomic restore failed.',0,$error);
                $restored=self::read($path);
                foreach(['hash','mode','uid','gid'] as $key)if($restored[$key]!==$original[$key])throw new RuntimeException('RECOVERY FAILED: restored metadata differs.',0,$error);
                throw new RuntimeException('Containment failed; original bytes/owner/mode restored. Prior exposure remains. Preimage: '.$backup,0,$error);
            }
            throw $error;
        } finally {umask($oldMask);}
    }

    public const LEGACY_HASHES=[
        'index_public.php'=>'a87235a31545c0a825bed2f0a63743e567378e3f2a8015d9524369bf406e2029',
        'redirect.php'=>'76c7bd3bd5539ee066a11bb419be124f464192855d082e15ab2f08797d298a4e',
        '_debug_diff.txt'=>'40486ddd861a79921670d6a284df47882c68fc39d448bd2b5525226e4b698795',
    ];

    /** Reversible exact-file quarantine when web-layer denial is not enforced. */
    public static function quarantine(string $app,string $privateRoot,bool $apply,callable $identity,callable $postflight,?callable $beforeMove=null): array
    {
        if(realpath($app)!==$app||is_link($app)||!is_dir($app)||$identity()!==[self::SHA,self::TREE])throw new RuntimeException('Quarantine app/source identity drift.');
        $rewrite=self::read($app.'/.htaccess');
        if($rewrite['hash']!==self::ORIGINAL)throw new RuntimeException('Quarantine rewrite identity drift.');
        $files=[];
        foreach(self::LEGACY_HASHES as $name=>$hash){$files[$name]=self::read($app.'/'.$name);if($files[$name]['hash']!==$hash)throw new RuntimeException('Legacy file drift: '.$name);}
        if(!$apply)return ['mode'=>'quarantine-dry-run','files'=>array_keys($files),'hashes'=>self::LEGACY_HASHES];
        if(realpath($privateRoot)!==$privateRoot||is_link($privateRoot)||!is_dir($privateRoot)||$privateRoot===$app||str_starts_with($privateRoot,$app.DIRECTORY_SEPARATOR)||stat($app)['dev']!==stat($privateRoot)['dev'])throw new RuntimeException('Private same-filesystem root outside app required.');
        $oldMask=umask(0077);$moved=[];
        try{
            $backup=$privateRoot.'/.denta-legacy-quarantine-'.bin2hex(random_bytes(12));
            if(!mkdir($backup,0700)||!chmod($backup,0700))throw new RuntimeException('Cannot create exclusive quarantine.');
            if(PHP_OS_FAMILY!=='Windows'&&(fileperms($backup)&0777)!==0700)throw new RuntimeException('Quarantine privacy verification failed.');
            self::writeExclusive($backup.'/metadata.json',json_encode(array_map(static fn($file)=>array_diff_key($file,['bytes'=>true]),$files),JSON_THROW_ON_ERROR),0600);
            foreach($files as $name=>$file)self::writeExclusive($backup.'/'.$name.'.preimage',$file['bytes'],0600);
            if($beforeMove)$beforeMove();
            if($identity()!==[self::SHA,self::TREE]||self::read($app.'/.htaccess')!==$rewrite)throw new RuntimeException('Late quarantine identity drift.');
            foreach($files as $name=>$file)if(self::read($app.'/'.$name)!==$file)throw new RuntimeException('Late legacy file drift.');
            foreach($files as $name=>$file){
                if(!rename($app.'/'.$name,$backup.'/'.$name))throw new RuntimeException('Quarantine move failed.');
                $moved[]=$name;
                if(!chmod($backup.'/'.$name,0600)||hash_file('sha256',$backup.'/'.$name)!==$file['hash'])throw new RuntimeException('Quarantine verification failed.');
            }
            if(!$postflight()||self::read($app.'/.htaccess')!==$rewrite)throw new RuntimeException('Quarantine postflight failed.');
            return ['mode'=>'quarantined','files'=>$moved,'private_recovery'=>$backup,'rewrite_unchanged'=>true];
        }catch(Throwable $error){
            foreach(array_reverse($moved) as $name){
                $destination=$app.'/'.$name;$stored=$backup.'/'.$name;$expected=$files[$name];
                if(file_exists($destination)||is_link($destination)||hash_file('sha256',$stored)!==$expected['hash'])throw new RuntimeException('QUARANTINE RECOVERY CONFLICT: manual intervention required.',0,$error);
                if(!chmod($stored,$expected['mode'])||!rename($stored,$destination))throw new RuntimeException('QUARANTINE RECOVERY FAILED.',0,$error);
                $restored=self::read($destination);
                foreach(['hash','mode','uid','gid'] as $key)if($restored[$key]!==$expected[$key])throw new RuntimeException('QUARANTINE RECOVERY metadata mismatch.',0,$error);
            }
            throw new RuntimeException('Quarantine failed; moved files restored. Prior exposure remains. '.$error->getMessage(),0,$error);
        }finally{umask($oldMask);}
    }

    public static function gitIdentity(string $app): array
    {
        if(!is_dir($app.'/.git')||is_link($app.'/.git'))throw new RuntimeException('Real Git metadata required.');
        $values=[];
        foreach(['HEAD','HEAD^{tree}'] as $ref){
            $proc=proc_open(['git','-C',$app,'rev-parse',$ref],[0=>['pipe','r'],1=>['pipe','w'],2=>['pipe','w']],$pipes);
            if(!is_resource($proc))throw new RuntimeException('Cannot inspect Git.');
            fclose($pipes[0]);$value=trim(stream_get_contents($pipes[1]));fclose($pipes[1]);stream_get_contents($pipes[2]);fclose($pipes[2]);
            if(proc_close($proc)!==0||!preg_match('/^[a-f0-9]{40}$/D',$value))throw new RuntimeException('Git inspection failed.');
            $values[]=$value;
        }
        return $values;
    }

    public static function httpChecks(bool $contained): bool
    {
        if(!function_exists('curl_init'))throw new RuntimeException('Verified HTTPS curl support required.');
        $paths=['/auth/login'=>[200],'/assets/css/tailwind.css'=>[200],'/assets/js/studio-money.js'=>[200]];
        foreach(['/index_public.php','/redirect.php','/_debug_diff.txt'] as $path)$paths[$path]=$contained?[403,404]:[200,301,302,403,404];
        $ok=true;
        foreach($paths as $path=>$codes)foreach(['HEAD','GET'] as $method){
            $ch=curl_init(self::BASE.$path.'?denta_containment_check='.bin2hex(random_bytes(8)));
            curl_setopt_array($ch,[CURLOPT_NOBODY=>$method==='HEAD',CURLOPT_FOLLOWLOCATION=>false,CURLOPT_TIMEOUT=>15,CURLOPT_CONNECTTIMEOUT=>5,CURLOPT_SSL_VERIFYPEER=>true,CURLOPT_SSL_VERIFYHOST=>2,CURLOPT_WRITEFUNCTION=>static fn($handle,$bytes)=>strlen($bytes)]);
            $result=curl_exec($ch);$code=(int)curl_getinfo($ch,CURLINFO_HTTP_CODE);$error=curl_errno($ch);curl_close($ch);
            echo $method.' '.$path.' '.$code.PHP_EOL;
            if($result===false||$error!==0||!in_array($code,$codes,true))$ok=false;
        }
        return $ok;
    }
}

function runHostingerContainment(array $argv): int
{
    if(PHP_SAPI!=='cli')return 2;
    try {
        $args=array_slice($argv,1);
        if(!in_array($args,[[],['--quarantine-dry-run'],['--apply-quarantine']],true))throw new RuntimeException('Use default rewrite preview, --quarantine-dry-run or --apply-quarantine. Root-rule apply is disabled after failed host postflight. No bypass flags.');
        $quarantine=in_array($args,[['--quarantine-dry-run'],['--apply-quarantine']],true);
        $apply=$args===['--apply-quarantine'];
        if($apply&&!HostingerContainment::httpChecks(false))throw new RuntimeException('Baseline availability check failed.');
        $protected=[];
        foreach(['.env','public/.htaccess','public/assets/css/tailwind.css'] as $relative){
            $file=HostingerContainment::APP.'/'.$relative;
            if(!is_file($file)||is_link($file))throw new RuntimeException('Protected anchor is unavailable.');
            $protected[$relative]=hash_file('sha256',$file);
        }
        $verify=static function()use($protected):bool{
            $http=HostingerContainment::httpChecks(true);
            foreach($protected as $relative=>$hash)if(hash_file('sha256',HostingerContainment::APP.'/'.$relative)!==$hash)return false;
            return $http;
        };
        $operation=$quarantine?'quarantine':'execute';
        $result=HostingerContainment::$operation(HostingerContainment::APP,HostingerContainment::HOME,$apply,static fn()=>HostingerContainment::gitIdentity(HostingerContainment::APP),$verify);
        echo json_encode($result,JSON_PRETTY_PRINT|JSON_THROW_ON_ERROR),PHP_EOL;
        return 0;
    } catch(Throwable $error){
        fwrite(STDERR,$error->getMessage().PHP_EOL);
        if(str_contains($error->getMessage(),'original bytes/owner/mode restored')||str_contains($error->getMessage(),'moved files restored')){
            if(!HostingerContainment::httpChecks(false))fwrite(STDERR,'RECOVERY AVAILABILITY FAILED: operator intervention required.'.PHP_EOL);
        }
        return 1;
    }
}
if(realpath($_SERVER['SCRIPT_FILENAME']??'')===__FILE__)exit(runHostingerContainment($argv));
