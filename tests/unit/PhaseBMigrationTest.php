<?php

declare(strict_types=1);
namespace Tests\Unit;
use App\Database\Migrations\AddDynamicDoctorScheduling;
use Config\Database;
use Tests\Support\PhaseBFixture;

final class PhaseBMigrationTest extends PhaseBFixture
{
    public function testRetainedExpansionPreservesLegacyRowsAndOrderedIndex(): void
    {
        require_once APPPATH.'Database/Migrations/2026-09-26-000001_AddDynamicDoctorScheduling.php';
        $row=$this->payload(); unset($row['provider_user_id']);
        $row+=['clinic_id'=>$this->clinic,'appointment_id'=>$this->prefix,'chair_name'=>'Unchanged historical snapshot','created_by'=>$this->actor];
        $this->db->table('appointments')->insert($row); $id=(int)$this->db->insertID();
        $before=$this->db->table('appointments')->where('id',$id)->get()->getRowArray();
        $migration=new AddDynamicDoctorScheduling(Database::forge($this->db));
        $migration->up(); $migration->up();
        $schemas=[];
        foreach(['appointments','clinic_provider_profiles']as$table)$schemas[$table]=$this->db->query('SHOW CREATE TABLE '.$this->db->escapeIdentifiers($table))->getRowArray();
        try {(new AddDynamicDoctorScheduling(Database::forge($this->db)))->down();$this->fail('Unproven ownership cannot authorize schema removal');}
        catch(\RuntimeException$e){$this->assertStringContainsString('ownership',$e->getMessage());}
        foreach($schemas as$table=>$ddl)$this->assertSame($ddl,$this->db->query('SHOW CREATE TABLE '.$this->db->escapeIdentifiers($table))->getRowArray());
        $this->assertSame($before,$this->db->table('appointments')->where('id',$id)->get()->getRowArray());
        $indexes=$this->db->query("SHOW INDEX FROM appointments WHERE Key_name='idx_appointments_provider_sched'")->getResultArray();
        usort($indexes,static fn($a,$b)=>(int)$a['Seq_in_index']<=>(int)$b['Seq_in_index']);
        $this->assertSame(['clinic_id','provider_user_id','appointment_date','status'],array_column($indexes,'Column_name'));
        $this->assertNull($before['provider_user_id']);
        $this->setting('dynamic_doctor_scheduling_enabled',false);
        $legacy=(new \App\Services\AppointmentSchedulingService($this->db))->createForClinic($this->clinic,$this->payload(['chair_name'=>'Legacy room','appointment_time'=>'11:00']),$this->actor);
        $this->assertTrue($legacy['success'],json_encode($legacy));
        $this->setting('dynamic_doctor_scheduling_enabled',true);
        $this->db->table('appointments')->where('id',$id)->update(['provider_user_id'=>$this->providers[0]]);
        $linked=$this->db->table('appointments')->where('id',$id)->get()->getRowArray();
        try {(new AddDynamicDoctorScheduling(Database::forge($this->db)))->down();$this->fail('Linked provider history must be retained');}
        catch(\RuntimeException$e){$this->assertStringContainsString('ownership',$e->getMessage());}
        $this->assertSame($linked,$this->db->table('appointments')->where('id',$id)->get()->getRowArray());
    }

    public function testMissingSchemaGuardWithoutDroppingSharedObjects(): void
    {
        // Exercise the existing dependency seam instead of deleting shared schema.
        $directory=new class($this->db) extends \App\Services\ProviderDirectoryService {
            public function isEnabled(int $clinicId):bool{return true;}
            public function isSchemaReady():bool{return false;}
        };
        $service=new \App\Services\AppointmentSchedulingService($this->db,directoryService:$directory);
        $before=$this->db->table('appointments')->where('clinic_id',$this->clinic)->get()->getResultArray();
        $this->assertSame([],$service->availableSlots($this->clinic,'2026-11-09',30,$this->providers[0]));
        $result=$service->createForClinic($this->clinic,$this->payload(['appointment_time'=>'12:00']),$this->actor);
        $this->assertFalse($result['success']);$this->assertSame('SCHEMA_NOT_READY',$result['code']);
        $this->assertSame($before,$this->db->table('appointments')->where('clinic_id',$this->clinic)->get()->getResultArray());
    }

}
