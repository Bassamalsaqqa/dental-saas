<?php

declare(strict_types=1);
namespace Tests\Unit;
use Tests\Support\PhaseBFixture;

final class PhaseBConcurrencyTest extends PhaseBFixture
{
    public function testSimultaneousBookingsRevalidateAfterWaitingForClinicLock(): void
    {
        $directory=ROOTPATH.'tmp/phase-b-race-'.bin2hex(random_bytes(6));
        mkdir($directory,0700,true);
        $input=$directory.'/input.json';
        file_put_contents($input,json_encode(['actor'=>$this->actor,'email'=>$this->email,'clinic'=>$this->clinic,'payload'=>$this->payload()]));
        $processes=[]; $pipes=[];
        try {
            $this->db->transBegin();
            $this->db->query('SELECT id FROM clinics WHERE id=? FOR UPDATE',[$this->clinic]);
            for($i=0;$i<2;$i++) {
                $processes[$i]=proc_open([PHP_BINARY,ROOTPATH.'tests/_support/phase_b_booking_worker.php',$input,$directory.'/ready'.$i],[0=>['pipe','r'],1=>['pipe','w'],2=>['pipe','w']],$pipes[$i],ROOTPATH);
                $this->assertIsResource($processes[$i]); fclose($pipes[$i][0]);
            }
            $deadline=microtime(true)+15;
            do {
                clearstatcache();
                if (is_file($directory.'/ready0')&&is_file($directory.'/ready1')) break;
                usleep(20000);
            } while(microtime(true)<$deadline);
            $this->assertFileExists($directory.'/ready0');
            $this->assertFileExists($directory.'/ready1');
            $this->db->transCommit();
            $results=[];
            foreach($processes as $i=>$process) {
                $out=stream_get_contents($pipes[$i][1]); $err=stream_get_contents($pipes[$i][2]);
                fclose($pipes[$i][1]); fclose($pipes[$i][2]);
                $code=proc_close($process); unset($processes[$i]);
                $this->assertSame(0,$code,$err.' '.$out);
                $result=json_decode($out,true);
                $this->assertIsArray($result,$out.' '.$err); $results[]=$result;
            }
            $this->assertSame(1,count(array_filter($results,static fn($r)=>$r['success']===true)),json_encode($results));
            $this->assertSame(1,$this->db->table('appointments')->where('clinic_id',$this->clinic)->countAllResults());
        } finally {
            while($this->db->transDepth>0) $this->db->transRollback();
            foreach($processes as $process) { proc_terminate($process); proc_close($process); }
            foreach([$input,$directory.'/ready0',$directory.'/ready1'] as $path) if(is_file($path)) unlink($path);
            rmdir($directory);
        }
    }
}
