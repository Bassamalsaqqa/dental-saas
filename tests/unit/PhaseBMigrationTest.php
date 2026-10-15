<?php

declare(strict_types=1);
namespace Tests\Unit;
use App\Database\Migrations\AddDynamicDoctorScheduling;
use Config\Database;
use Tests\Support\PhaseBFixture;

final class PhaseBMigrationTest extends PhaseBFixture
{
    public function testExpansionRoundTripPreservesLegacyRowsAndRestoresOrderedIndex(): void
    {
        require_once APPPATH.'Database/Migrations/2026-09-26-000001_AddDynamicDoctorScheduling.php';
        $this->assertSame(0,$this->db->table('clinic_provider_profiles')->countAllResults(),'Schema roundtrip requires no unrelated profiles');
        $this->assertSame(0,$this->db->table('appointments')->where('provider_user_id IS NOT NULL',null,false)->countAllResults(),'Schema roundtrip requires no unrelated assigned appointments');
        $row=$this->payload(); unset($row['provider_user_id']);
        $row+=['clinic_id'=>$this->clinic,'appointment_id'=>$this->prefix,'chair_name'=>'Unchanged historical snapshot','created_by'=>$this->actor];
        $this->db->table('appointments')->insert($row); $id=(int)$this->db->insertID();
        $before=$this->db->table('appointments')->where('id',$id)->get()->getRowArray();
        $migration=new AddDynamicDoctorScheduling(Database::forge('tests'));
        try {
            $migration->up(); $migration->up();
            $migration->down();
            $this->db->resetDataCache();
            $this->assertFalse($this->db->fieldExists('provider_user_id','appointments'));
            $without=$this->db->table('appointments')->where('id',$id)->get()->getRowArray();
            unset($before['provider_user_id']); $this->assertSame($before,$without);
            $this->setting('dynamic_doctor_scheduling_enabled',false);
            $service=new \App\Services\AppointmentSchedulingService($this->db);
            $legacy=$service->createForClinic($this->clinic,$this->payload(['chair_name'=>'Legacy room','appointment_time'=>'11:00']),$this->actor);
            $this->assertTrue($legacy['success'],json_encode($legacy));
            $this->setting('dynamic_doctor_scheduling_enabled',true);
            $this->assertSame([],$service->availableSlots($this->clinic,'2026-11-09',30,$this->providers[0]));
            $blocked=$service->createForClinic($this->clinic,$this->payload(['appointment_time'=>'12:00']),$this->actor);
            $this->assertFalse($blocked['success']); $this->assertSame('SCHEMA_NOT_READY',$blocked['code']);
            $migration->up();
            $indexes=$this->db->query("SHOW INDEX FROM appointments WHERE Key_name='idx_appointments_provider_sched'")->getResultArray();
            usort($indexes,static fn($a,$b)=>(int)$a['Seq_in_index']<=>(int)$b['Seq_in_index']);
            $this->assertSame(['clinic_id','provider_user_id','appointment_date','status'],array_column($indexes,'Column_name'));
            $after=$this->db->table('appointments')->where('id',$id)->get()->getRowArray();
            $this->assertNull($after['provider_user_id']); unset($after['provider_user_id']);
            $this->assertSame($before,$after);
            $this->db->table('appointments')->where('id',$id)->update(['provider_user_id'=>$this->providers[0]]);
            try { $migration->down(); $this->fail('Populated provider rollback must be refused'); }
            catch(\RuntimeException $e) { $this->assertStringContainsString('provider-linked',$e->getMessage()); }
            $this->assertTrue($this->db->fieldExists('provider_user_id','appointments'));
        } finally { $migration->up(); }
    }
}
