<?php
namespace Tests\unit;

use PHPUnit\Framework\TestCase;

final class SafeTestConnectionBootstrapTest extends TestCase
{
    public function testChildBridgeRejectsUnverifiedTargetsWithoutDisclosingPayload(): void
    {
        $script=dirname(__DIR__,2).'/scripts/TestRunnerConnectionBootstrap.php';
        $valid=['hostname'=>'127.0.0.1','port'=>3307,'database'=>'democa_dental_saas_test','username'=>'synthetic','password'=>'SYNTHETIC-PRIVATE-PAYLOAD','DBDriver'=>'MySQLi','DBPrefix'=>''];
        $cases=[array_replace($valid,['hostname'=>'remote.invalid']),array_replace($valid,['database'=>'democa_dental_saas']),array_replace($valid,['port'=>80]),array_diff_key($valid,['password'=>true]),'invalid-json'];
        foreach ($cases as $case) {
            $env=getenv();$env['DENTA_VERIFIED_TEST_CONNECTION']=is_array($case)?json_encode($case):$case;
            $process=proc_open([PHP_BINARY,$script],[0=>['pipe','r'],1=>['pipe','w'],2=>['pipe','w']],$pipes,dirname(__DIR__,2),$env);
            self::assertIsResource($process);fclose($pipes[0]);
            $output=stream_get_contents($pipes[1]).stream_get_contents($pipes[2]);fclose($pipes[1]);fclose($pipes[2]);
            self::assertNotSame(0,proc_close($process));
            self::assertStringNotContainsString('SYNTHETIC-PRIVATE-PAYLOAD',$output);
            self::assertStringContainsString('Verified isolated child connection required.',$output);
        }
    }
}
