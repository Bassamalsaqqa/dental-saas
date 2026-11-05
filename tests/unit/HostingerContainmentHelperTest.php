<?php
declare(strict_types=1);
namespace Tests\Unit;
use HostingerContainment as H;
use PHPUnit\Framework\TestCase;
require_once dirname(__DIR__,2).'/scripts/hostinger_containment.php';
final class HostingerContainmentHelperTest extends TestCase
{
    private const FIXTURE='IyAtLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tCiMgU2VjdXJpdHkgSGFyZGVuaW5nIChQMC0wMikKIyAtLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tCgojIEFwYWNoZSAyLjQrCjxJZk1vZHVsZSBtb2RfYXV0aHpfY29yZS5jPgogICAgIyBCbG9jayBhY2Nlc3MgdG8gZG90ZmlsZXMgKGV4Y2VwdCAuaHRhY2Nlc3MpCiAgICA8RmlsZXNNYXRjaCAiXlwuKD8hd2VsbC1rbm93bikiPgogICAgICAgIFJlcXVpcmUgYWxsIGRlbmllZAogICAgPC9GaWxlc01hdGNoPgoKICAgICMgQmxvY2sgYWNjZXNzIHRvIHNwZWNpZmljIHNlbnNpdGl2ZSBmaWxlcwogICAgPEZpbGVzTWF0Y2ggIl4oRU5WSVJPTk1FTlR8ZW52aXJvbm1lbnRcLmVudnxjb21wb3NlclwuanNvbnxjb21wb3NlclwubG9ja3xwYWNrYWdlXC5qc29ufHBhY2thZ2UtbG9ja1wuanNvbikkIj4KICAgICAgICBSZXF1aXJlIGFsbCBkZW5pZWQKICAgIDwvRmlsZXNNYXRjaD4KCiAgICAjIEJsb2NrIGFjY2VzcyB0byBzZW5zaXRpdmUgZXh0ZW5zaW9ucwogICAgPEZpbGVzTWF0Y2ggIlwuKHNxbHxzcWxcLmd6fGR1bXB8ZHVtcFwuZ3p8bG9nfGJha3xkaXN0fGluaXxzaHxtZCkkIj4KICAgICAgICBSZXF1aXJlIGFsbCBkZW5pZWQKICAgIDwvRmlsZXNNYXRjaD4KCiAgICAjIEJsb2NrIGFjY2VzcyB0byBwb3RlbnRpYWxseSBkYW5nZXJvdXMgc2NyaXB0cwogICAgPEZpbGVzTWF0Y2ggIl4oaW5zdGFsbHxkZWJ1Z19lbnZ8cmVwYWlyX2RiLip8aW5pdF9kYXRhYmFzZXxzaW1wbGVfbG9naW5fdGVzdClcLnBocCQiPgogICAgICAgIFJlcXVpcmUgYWxsIGRlbmllZAogICAgPC9GaWxlc01hdGNoPgo8L0lmTW9kdWxlPgoKIyBBcGFjaGUgMi4yIEZhbGxiYWNrCjxJZk1vZHVsZSAhbW9kX2F1dGh6X2NvcmUuYz4KICAgIDxGaWxlc01hdGNoICJeXC4oPyF3ZWxsLWtub3duKSI+CiAgICAgICAgT3JkZXIgYWxsb3csZGVueQogICAgICAgIERlbnkgZnJvbSBhbGwKICAgIDwvRmlsZXNNYXRjaD4KICAgIDxGaWxlc01hdGNoICJeKEVOVklST05NRU5UfGVudmlyb25tZW50XC5lbnZ8Y29tcG9zZXJcLmpzb258Y29tcG9zZXJcLmxvY2t8cGFja2FnZVwuanNvbnxwYWNrYWdlLWxvY2tcLmpzb24pJCI+CiAgICAgICAgT3JkZXIgYWxsb3csZGVueQogICAgICAgIERlbnkgZnJvbSBhbGwKICAgIDwvRmlsZXNNYXRjaD4KICAgIDxGaWxlc01hdGNoICJcLihzcWx8c3FsXC5nenxkdW1wfGR1bXBcLmd6fGxvZ3xiYWt8ZGlzdHxpbml8c2h8bWQpJCI+CiAgICAgICAgT3JkZXIgYWxsb3csZGVueQogICAgICAgIERlbnkgZnJvbSBhbGwKICAgIDwvRmlsZXNNYXRjaD4KICAgIDxGaWxlc01hdGNoICJeKGluc3RhbGx8ZGVidWdfZW52fHJlcGFpcl9kYi4qfGluaXRfZGF0YWJhc2V8c2ltcGxlX2xvZ2luX3Rlc3QpXC5waHAkIj4KICAgICAgICBPcmRlciBhbGxvdyxkZW55CiAgICAgICAgRGVueSBmcm9tIGFsbAogICAgPC9GaWxlc01hdGNoPgo8L0lmTW9kdWxlPgoKIyBEaXNhYmxlIGRpcmVjdG9yeSBicm93c2luZwpPcHRpb25zIC1JbmRleGVzCgojIC0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0KIyBSZXdyaXRlIFJ1bGVzIChSb3V0aW5nICYgRGlyZWN0b3J5IEJsb2NraW5nKQojIC0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0KRGlyZWN0b3J5SW5kZXggaW5kZXgucGhwClJld3JpdGVFbmdpbmUgT24KIyBCbG9jayBhY2Nlc3MgdG8gc2Vuc2l0aXZlIGRpcmVjdG9yaWVzIChSZXBvLVJvb3QgRG9jcm9vdCBTY2VuYXJpbykKIyBUaGlzIE1VU1QgYmUgYmVmb3JlIG90aGVyIHJld3JpdGUgcnVsZXMuClJld3JpdGVSdWxlIF4oYXBwfHN5c3RlbXx0ZXN0c3x3cml0YWJsZXxzY3JpcHRzfGRvY3N8bGlifHNyY3x2ZW5kb3J8bm9kZV9tb2R1bGVzKS8gLSBbRixMXQoKIyAtLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tCiMgRGVmZW5zZS1pbi1EZXB0aDogQmxvY2sgQmFja3VwIEVuZHBvaW50cyBhdCBXZWItU2VydmVyIExheWVyCiMgLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLQpSZXdyaXRlUnVsZSBec2V0dGluZ3MvKGJhY2t1cHxjcmVhdGUtYmFja3VwfGRvd25sb2FkLWJhY2t1cHxyZXN0b3JlKSgvLiopPyQgLSBbRixMXQoKIyBDYW5vbmljYWwgcHVibGljIGFzc2V0IGFsaWFzZXMuCiMgUnVudGltZSBhc3NldHMgbXVzdCBjb21lIGZyb20gcHVibGljL2Fzc2V0cyBhbmQgcHVibGljL3VwbG9hZHMgb25seS4KIyBLZWVwIHRoZXNlIGFsaWFzZXMgdW5jb25kaXRpb25hbCBzbyBhIHN0cmF5IHJlcG8tcm9vdCBhc3NldHMvIG9yIHVwbG9hZHMvCiMgdHJlZSBjYW4gbmV2ZXIgb3ZlcnJpZGUgdGhlIHJlYWwgcHVibGljIGZpbGVzLgpSZXdyaXRlUnVsZSBeYXNzZXRzLyguKikkIHB1YmxpYy9hc3NldHMvJDEgW0xdClJld3JpdGVSdWxlIF51cGxvYWRzLyguKikkIHB1YmxpYy91cGxvYWRzLyQxIFtMXQoKIyBIYW5kbGUgYWxsIG90aGVyIHJlcXVlc3RzIHRocm91Z2ggdGhlIHJvb3QgaW5kZXgucGhwClJld3JpdGVDb25kICV7UkVRVUVTVF9GSUxFTkFNRX0gIS1mClJld3JpdGVDb25kICV7UkVRVUVTVF9GSUxFTkFNRX0gIS1kClJld3JpdGVSdWxlIF4oLiopJCBpbmRleC5waHAgW1FTQSxMXQo=';
    private string $root;
    private string $app;
    private string $private;
    private string $original;
    protected function setUp(): void
    {
        $this->root=str_replace('\\','/',sys_get_temp_dir()).'/denta-containment-test-'.bin2hex(random_bytes(10));
        mkdir($this->root,0700);mkdir($this->root.'/app',0755);mkdir($this->root.'/private',0700);
        $this->app=realpath($this->root.'/app');$this->private=realpath($this->root.'/private');
        $this->original=base64_decode(self::FIXTURE,true);
        file_put_contents($this->app.'/.htaccess',$this->original);chmod($this->app.'/.htaccess',0640);
    }
    protected function tearDown(): void
    {
        // Only this fixture's exclusive random root may be removed; never follow links.
        $root=realpath($this->root);
        if(!$root||dirname($root)!==realpath(sys_get_temp_dir())||!str_starts_with(basename($root),'denta-containment-test-'))throw new \RuntimeException('Unsafe test cleanup root.');
        $walk=function($dir)use(&$walk){foreach(scandir($dir) as $name){if($name==='.'||$name==='..')continue;$path=$dir.DIRECTORY_SEPARATOR.$name;if(is_dir($path)&&!is_link($path))$walk($path);else unlink($path);}rmdir($dir);};
        $walk($root);
    }
    private function executeFixture(bool $apply=true,?callable $probe=null,?callable $identity=null,?callable $before=null): array
    {
        return H::execute($this->app,$this->private,$apply,$identity??static fn()=>[H::SHA,H::TREE],$probe??static fn()=>true,$before);
    }
    private function failure(callable $fn,string $message): void
    {
        try{$fn();self::fail('Expected refusal.');}catch(\RuntimeException $error){self::assertStringContainsString($message,$error->getMessage());}
    }
    public function testDryRunHasNoFilesystemOrHttpEffects(): void
    {
        $calls=0;$result=$this->executeFixture(false,function()use(&$calls){$calls++;return true;});
        self::assertSame('dry-run',$result['mode']);self::assertSame(0,$calls);
        self::assertSame($this->original,file_get_contents($this->app.'/.htaccess'));
        self::assertSame(['.','..'],scandir($this->private));self::assertSame(['.','..','.htaccess'],scandir($this->app));
    }
    public function testApplyPreservesBytesOutsideBlocksAndRecordedMode(): void
    {
        $calls=0;$before=fileperms($this->app.'/.htaccess')&0777;
        $result=$this->executeFixture(true,function()use(&$calls){$calls++;return true;});
        self::assertSame('applied',$result['mode']);self::assertSame(1,$calls);
        $bytes=file_get_contents($this->app.'/.htaccess');
        self::assertSame($this->original,str_replace([H::BLOCK24,H::BLOCK22,H::REWRITE],'',$bytes));
        self::assertSame($this->original,file_get_contents($result['preimage']));
        clearstatcache();self::assertSame($before,fileperms($this->app.'/.htaccess')&0777);
    }
    public function testHashDriftAndMisleadingContainmentAreRejected(): void
    {
        file_put_contents($this->app.'/.htaccess',$this->original."\n# index_public.php\n");
        $this->failure(fn()=>$this->executeFixture(),'hash drift');self::assertSame(['.','..'],scandir($this->private));
    }
    public function testGitDriftIsRejectedBeforeBackup(): void
    {
        $this->failure(fn()=>$this->executeFixture(true,null,static fn()=>['SKIP','SKIP']),'identity drift');
        self::assertSame(['.','..'],scandir($this->private));
    }
    public function testMissingGitMetadataIsNotSilentlySkipped(): void
    {
        $this->failure(fn()=>H::gitIdentity($this->app),'Git metadata required');
    }
    public function testIdempotentApplyStillRunsPostflight(): void
    {
        $this->executeFixture();$calls=0;
        $result=$this->executeFixture(true,function()use(&$calls){$calls++;return true;});
        self::assertSame('verified-existing',$result['mode']);self::assertSame(1,$calls);
        $this->failure(fn()=>$this->executeFixture(true,static fn()=>false),'failed HTTP');
    }
    public function testPostflightFailureRestoresExactOriginalAndMode(): void
    {
        $before=fileperms($this->app.'/.htaccess')&0777;
        $this->failure(fn()=>$this->executeFixture(true,static fn()=>false),'original bytes/owner/mode restored');
        self::assertSame($this->original,file_get_contents($this->app.'/.htaccess'));
        clearstatcache();self::assertSame($before,fileperms($this->app.'/.htaccess')&0777);
    }
    public function testThrownPostflightAlsoRestores(): void
    {
        $this->failure(fn()=>$this->executeFixture(true,static function(){throw new \RuntimeException('HTTP fault');}),'original bytes/owner/mode restored');
        self::assertSame($this->original,file_get_contents($this->app.'/.htaccess'));
    }
    public function testBackupFailureCannotTouchTarget(): void
    {
        $this->failure(fn()=>H::execute($this->app,$this->private.'/missing',true,static fn()=>[H::SHA,H::TREE],static fn()=>true),'private root');
        self::assertSame($this->original,file_get_contents($this->app.'/.htaccess'));
    }
    public function testLateWriterIsNotOverwritten(): void
    {
        $foreign=$this->original."\n# concurrent change\n";
        $this->failure(fn()=>$this->executeFixture(true,null,null,fn()=>file_put_contents($this->app.'/.htaccess',$foreign)),'Late identity/file drift');
        self::assertSame($foreign,file_get_contents($this->app.'/.htaccess'));
    }
    public function testRollbackDoesNotClobberAThirdPartyChange(): void
    {
        $foreign=$this->original."\n# concurrent postflight change\n";
        $this->failure(fn()=>$this->executeFixture(true,function()use($foreign){file_put_contents($this->app.'/.htaccess',$foreign);return false;}),'RECOVERY CONFLICT');
        self::assertSame($foreign,file_get_contents($this->app.'/.htaccess'));
    }
    public function testWebrootBackupAndMalformedDuplicateBlocksAreRejected(): void
    {
        $this->failure(fn()=>H::execute($this->app,$this->app,true,static fn()=>[H::SHA,H::TREE],static fn()=>true),'outside app');
        file_put_contents($this->app.'/.htaccess',H::candidate($this->original).H::BLOCK24);
        $this->failure(fn()=>$this->executeFixture(),'malformed containment');
    }
    private function legacyFiles(): void
    {
        foreach(H::LEGACY_HASHES as $name=>$hash){
            $proc=proc_open(['git','show',H::SHA.':'.$name],[0=>['pipe','r'],1=>['pipe','w'],2=>['pipe','w']],$pipes,dirname(__DIR__,2));
            fclose($pipes[0]);$bytes=stream_get_contents($pipes[1]);fclose($pipes[1]);stream_get_contents($pipes[2]);fclose($pipes[2]);
            self::assertSame(0,proc_close($proc));self::assertSame($hash,hash('sha256',$bytes));
            file_put_contents($this->app.'/'.$name,$bytes);chmod($this->app.'/'.$name,0640);
        }
    }
    public function testQuarantineDryRunMakesNoChanges(): void
    {
        $this->legacyFiles();
        $result=H::quarantine($this->app,$this->private,false,static fn()=>[H::SHA,H::TREE],static fn()=>false);
        self::assertSame('quarantine-dry-run',$result['mode']);self::assertSame(['.','..'],scandir($this->private));
        foreach(H::LEGACY_HASHES as $name=>$hash)self::assertSame($hash,hash_file('sha256',$this->app.'/'.$name));
    }
    public function testQuarantineRetainsPrivateRecoverableFilesAndRewrite(): void
    {
        $this->legacyFiles();
        $result=H::quarantine($this->app,$this->private,true,static fn()=>[H::SHA,H::TREE],static fn()=>true);
        self::assertSame('quarantined',$result['mode']);
        foreach(H::LEGACY_HASHES as $name=>$hash){self::assertFileDoesNotExist($this->app.'/'.$name);self::assertSame($hash,hash_file('sha256',$result['private_recovery'].'/'.$name));}
        self::assertSame($this->original,file_get_contents($this->app.'/.htaccess'));
    }
    public function testQuarantinePostflightFailureRestoresBytesModesAndRewrite(): void
    {
        $this->legacyFiles();$mode=fileperms($this->app.'/redirect.php')&0777;
        $this->failure(fn()=>H::quarantine($this->app,$this->private,true,static fn()=>[H::SHA,H::TREE],static fn()=>false),'moved files restored');
        foreach(H::LEGACY_HASHES as $name=>$hash){self::assertSame($hash,hash_file('sha256',$this->app.'/'.$name));clearstatcache();self::assertSame($mode,fileperms($this->app.'/'.$name)&0777);}
        self::assertSame($this->original,file_get_contents($this->app.'/.htaccess'));
    }
    public function testQuarantineRejectsUnexpectedAndLateFileChanges(): void
    {
        $this->legacyFiles();
        $this->failure(fn()=>H::quarantine($this->app,$this->private,true,static fn()=>[H::SHA,H::TREE],static fn()=>true,fn()=>file_put_contents($this->app.'/redirect.php','new operator file')),'Late legacy file drift');
        self::assertSame('new operator file',file_get_contents($this->app.'/redirect.php'));
        self::assertFileExists($this->app.'/index_public.php');
        $this->failure(fn()=>H::quarantine($this->app,$this->private,true,static fn()=>[H::SHA,H::TREE],static fn()=>true),'Legacy file drift');
    }
}
