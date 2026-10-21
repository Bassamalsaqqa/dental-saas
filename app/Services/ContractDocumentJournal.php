<?php
namespace App\Services;

/** Private recoverable publication, separate from MariaDB ownership. Never deletes consent. */
class ContractDocumentJournal
{
    private const STATES = ['prepared', 'published', 'committed', 'unreferenced'];

    public function publish(int $clinicId, int $contractId, string $html, array $metadata): array
    {
        if ($clinicId < 1 || $contractId < 1 || $html === '') throw new \InvalidArgumentException('Invalid contract artifact');
        $operation = bin2hex(random_bytes(16));
        $tenant = $this->directory('uploads/clinic_'.$clinicId, false);
        $stage = $this->getStagingDir().'/'.$operation.'.tmp';
        $name = 'contract_'.$contractId.'_'.$operation.'.html';
        $artifact = ['version'=>1, 'operation'=>$operation, 'clinic_id'=>$clinicId, 'contract_id'=>$contractId,
            'relative_path'=>'clinic_'.$clinicId.'/'.$name, 'file_path'=>$tenant.'/'.$name, 'original_name'=>$name,
            'mime_type'=>'text/html', 'hash'=>hash('sha256', $html), 'size'=>strlen($html),
            'created_at'=>date('Y-m-d H:i:s'), 'metadata'=>$this->metadata($metadata), 'state'=>'prepared',
            'durability'=>PHP_OS_FAMILY==='Windows'?'file-fsync; directory-fsync unavailable on Windows':'file-and-directory-fsync'];
        return $this->locked($operation, function () use ($artifact, $stage, $html): array {
            if (file_exists($artifact['file_path']) || is_link($artifact['file_path']) || file_exists($stage)
                || is_link($stage) || file_exists($this->journalPath($artifact['operation']))) throw new \RuntimeException('Artifact collision');
            // Intent is durable before even staging bytes, so a crash/fault remains discoverable.
            $this->writeJournal($artifact);
            $this->writeExclusive($stage, $html);
            if (filesize($stage)!==$artifact['size'] || !hash_equals($artifact['hash'],hash_file('sha256',$stage)))
                throw new \RuntimeException('Staged artifact verification failed');
            if (!@link($stage,$artifact['file_path'])) throw new \RuntimeException('No-replace artifact publication failed; prepared evidence retained');
            $this->syncDirectory(dirname($artifact['file_path']));
            $published=$artifact;$published['state']='published';$published['published_at']=date('Y-m-d H:i:s');
            if (!$this->verify($published)) throw new \RuntimeException('Published artifact verification failed');
            $this->writeJournal($published);
            if (!unlink($stage)) throw new \RuntimeException('Owned staging cleanup failed; published evidence retained');
            $this->syncDirectory(dirname($stage));
            return $published;
        });
    }

    public function verify(array $artifact): bool
    {
        try {
            $this->validate($artifact);
            $path = $artifact['file_path'];
            $this->ancestors($path);
            clearstatcache(true, $path);
            return !is_link($path) && is_file($path) && is_readable($path)
                && str_replace('\\','/',(string)realpath($path))===$path && filesize($path)===$artifact['size']
                && hash_equals($artifact['hash'], hash_file('sha256', $path));
        } catch (\Throwable $e) { return false; }
    }

    public function markCommitted(array $artifact, int $signatureId, int $attachmentId): void
    {
        if ($signatureId < 1 || $attachmentId < 1) throw new \InvalidArgumentException('Positive evidence IDs required');
        $this->locked($artifact['operation'] ?? '', function () use ($artifact, $signatureId, $attachmentId): void {
            $entry=$this->owned($artifact);
            if ($entry['state']==='committed') {
                if (($entry['signature_id']??null)!==$signatureId || ($entry['attachment_id']??null)!==$attachmentId)
                    throw new \RuntimeException('Committed evidence ID conflict');
                return;
            }
            if ($entry['state']!=='published') throw new \RuntimeException('Journal state cannot become committed');
            $entry['state']='committed';$entry['signature_id']=$signatureId;$entry['attachment_id']=$attachmentId;
            $entry['committed_at']=date('Y-m-d H:i:s');$this->writeJournal($entry);
        });
    }

    public function markUnreferenced(array $artifact, string $reason): void
    {
        $this->locked($artifact['operation'] ?? '', function () use ($artifact, $reason): void {
            $entry=$this->owned($artifact);
            if ($entry['state']==='committed') throw new \RuntimeException('Committed evidence is never unreferenced');
            if ($entry['state']==='unreferenced') return;
            $entry['state']='unreferenced';$entry['unreferenced_at']=date('Y-m-d H:i:s');
            // Operational callers supply fixed reasons; do not persist arbitrary consent text.
            $entry['reason_code']=$reason==='Concurrent complete winner'?'concurrent-winner':'requires-operator-review';
            $this->writeJournal($entry);
        });
    }

    /** Read-only report input: do not create missing directories or alter corrupt journals. */
    public function entries(): array
    {
        $dir=$this->directory('contract-signing/operations', true, false);
        if (!is_dir($dir)) return [];
        $entries=[];
        foreach (scandir($dir) ?: [] as $file) {
            if (str_starts_with($file, '.')) continue;
            if (!preg_match('/^([a-f0-9]{32})\.json$/D',$file,$m)) throw new \RuntimeException('Noncanonical journal entry');
            $entries[]=$this->read($m[1]);
        }
        return $entries;
    }

    public function getJournalDir(): string { return $this->directory('contract-signing/operations',true); }
    public function getStagingDir(): string { return $this->directory('contract-signing/staging',true); }
    public function getUploadsRootDir(): string { return $this->directory('uploads',false); }

    private function owned(array $artifact): array
    {
        $this->validate($artifact);$entry=$this->read($artifact['operation']);
        foreach (['operation','clinic_id','contract_id','relative_path','file_path','original_name','mime_type','hash','size','created_at','metadata'] as $key)
            if ($entry[$key]!==$artifact[$key]) throw new \RuntimeException('Journal evidence ownership mismatch');
        return $entry;
    }

    private function read(string $operation): array
    {
        $path=$this->journalPath($operation);$this->ancestors($path);
        if (is_link($path)||!is_file($path)||str_replace('\\','/',(string)realpath($path))!==$path) throw new \RuntimeException('Noncanonical journal file');
        $this->permissions($path,0600,false);
        $entry=json_decode(file_get_contents($path),true,512,JSON_THROW_ON_ERROR);
        if (!is_array($entry)) throw new \RuntimeException('Corrupt journal');
        $this->validate($entry);
        if ($entry['operation']!==$operation) throw new \RuntimeException('Journal operation mismatch');
        return $entry;
    }

    private function validate(array $a): void
    {
        if (($a['version']??null)!==1 || !is_string($a['operation']??null) || !preg_match('/^[a-f0-9]{32}$/D',$a['operation'])
            || !is_int($a['clinic_id']??null)||$a['clinic_id']<1||!is_int($a['contract_id']??null)||$a['contract_id']<1
            || !is_int($a['size']??null)||$a['size']<1||!is_string($a['hash']??null)||!preg_match('/^[a-f0-9]{64}$/D',$a['hash'])
            || !in_array($a['state']??null,self::STATES,true)||($a['mime_type']??null)!=='text/html'
            || !is_string($a['created_at']??null)||!preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/D',$a['created_at'])
            || !is_array($a['metadata']??null)||$this->metadata($a['metadata'])!==$a['metadata']) throw new \RuntimeException('Invalid journal descriptor');
        $name='contract_'.$a['contract_id'].'_'.$a['operation'].'.html';$relative='clinic_'.$a['clinic_id'].'/'.$name;
        if (($a['relative_path']??null)!==$relative||($a['original_name']??null)!==$name
            ||($a['file_path']??null)!==$this->root().'/uploads/'.$relative) throw new \RuntimeException('Journal path ownership mismatch');
    }

    private function metadata(array $data): array
    {
        $out=[];
        foreach ($data as $key=>$value) {
            if (in_array($key,['source_fingerprint','consent_fingerprint'],true)) {
                if (!is_string($value)||!preg_match('/^[a-f0-9]{64}$/D',$value)) throw new \RuntimeException('Invalid evidence fingerprint');
                $out[$key]=$value;
            } elseif (in_array($key,['staff_user_id','actor_user_id'],true)&&is_int($value)&&$value>=0) $out[$key]=$value;
            else throw new \RuntimeException('Unsupported private journal metadata');
        }
        return $out;
    }

    private function locked(string $operation, callable $work): mixed
    {
        $this->operation($operation);$path=$this->getJournalDir().'/.'.$operation.'.lock';$this->ancestors($path);
        if (is_link($path)) throw new \RuntimeException('Journal lock alias');
        $new=!file_exists($path);$h=@fopen($path,$new?'x+b':'r+b');
        if (!$h) throw new \RuntimeException('Journal lock unavailable');
        try {
            $this->permissions($path,0600,$new);
            if (!flock($h,LOCK_EX)) throw new \RuntimeException('Journal lock failed');
            return $work();
        } finally {flock($h,LOCK_UN);fclose($h);}
    }

    private function writeJournal(array $entry): void
    {
        $this->validate($entry);$target=$this->journalPath($entry['operation']);$this->ancestors($target);
        if (is_link($target)) throw new \RuntimeException('Journal target alias');
        $tmp=$this->getJournalDir().'/.'.$entry['operation'].'.'.bin2hex(random_bytes(8)).'.tmp';
        $this->writeExclusive($tmp,json_encode($entry,JSON_UNESCAPED_SLASHES|JSON_THROW_ON_ERROR)."\n");
        // Never rotate/delete old evidence to simulate an atomic replacement.
        if (!@rename($tmp,$target)) throw new \RuntimeException('Atomic journal update failed; old and temporary evidence retained');
        $this->syncDirectory(dirname($target));
    }

    private function writeExclusive(string $path,string $bytes): void
    {
        $this->ancestors($path);$h=@fopen($path,'x+b');
        if (!$h) throw new \RuntimeException('Exclusive artifact write failed');
        try {
            $this->permissions($path,0600,true);$offset=0;$length=strlen($bytes);
            while ($offset<$length) {$n=fwrite($h,substr($bytes,$offset));if($n===false||$n===0)throw new \RuntimeException('Artifact short write');$offset+=$n;}
            if (!fflush($h)||!function_exists('fsync')||!fsync($h)) throw new \RuntimeException('Durable artifact flush failed');
        } finally {if(!fclose($h))throw new \RuntimeException('Artifact close failed');}
    }

    private function syncDirectory(string $path): void
    {
        // Windows PHP cannot fsync directory handles. Its weaker guarantee is recorded explicitly.
        if (PHP_OS_FAMILY==='Windows') return;
        $h=@fopen($path,'rb');if(!$h)throw new \RuntimeException('Directory durability unavailable');
        try {if(!fsync($h))throw new \RuntimeException('Directory fsync failed');}finally{fclose($h);}
    }

    private function root(): string
    {
        if (!defined('WRITEPATH')) throw new \RuntimeException('Application writable root required');
        $root=rtrim(str_replace('\\','/',WRITEPATH),'/');$this->ancestors($root);
        if (!is_dir($root)||str_replace('\\','/',(string)realpath($root))!==$root) throw new \RuntimeException('Writable root is not canonical');
        return $root;
    }

    private function directory(string $relative,bool $private,bool $create=true): string
    {
        $root=$this->root();$path=$root;
        foreach(explode('/',$relative)as$piece){
            if(!preg_match('/^[a-zA-Z0-9_-]+$/D',$piece))throw new \RuntimeException('Unsafe private directory');
            $path.='/'.$piece;$this->ancestors($path);
            if(!is_dir($path)){
                if(!$create)return $root.'/'.$relative;
                if(!mkdir($path,0700))throw new \RuntimeException('Private directory creation failed');
                $this->permissions($path,0700,true);$this->syncDirectory(dirname($path));
            }elseif($private)$this->permissions($path,0700,false);
        }
        return $path;
    }

    private function ancestors(string $path): void
    {
        for($p=$path;;$p=str_replace('\\','/',dirname($p))){
            if(is_link($p))throw new \RuntimeException('Artifact ancestor alias');
            if(file_exists($p)&&str_replace('\\','/',(string)realpath($p))!==$p)throw new \RuntimeException('Noncanonical artifact ancestor');
            $parent=str_replace('\\','/',dirname($p));if($parent===$p)break;
        }
    }

    private function permissions(string $path,int $mode,bool $set): void
    {
        if($set&&!chmod($path,$mode))throw new \RuntimeException('Private permissions failed');
        clearstatcache(true,$path);
        if(PHP_OS_FAMILY!=='Windows'&&((fileperms($path)&0777)!==$mode))throw new \RuntimeException('Private permissions differ');
    }
    private function operation(string $id):void{if(!preg_match('/^[a-f0-9]{32}$/D',$id))throw new \RuntimeException('Invalid operation identity');}
    private function journalPath(string $id):string{$this->operation($id);return $this->directory('contract-signing/operations',true,false).'/'.$id.'.json';}
}
