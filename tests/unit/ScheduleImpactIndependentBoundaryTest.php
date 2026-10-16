<?php

declare(strict_types=1);
namespace Tests\Unit;

use App\Services\AppointmentSchedulingService;
use App\Services\AppointmentSlotEditContext;
use Tests\Support\PhaseBFixture;

/** Independent public-boundary regressions; synthetic rows, no DDL or migrations. */
final class ScheduleImpactIndependentBoundaryTest extends PhaseBFixture
{
    private function book(): int
    {
        $result=(new AppointmentSchedulingService($this->db))->createForClinic($this->clinic,$this->payload(),$this->actor);
        $this->assertTrue($result['success'],json_encode($result));
        return (int)$result['id'];
    }

    public function testProofRechecksVisitStageAndCompletedNotesRemainEditable(): void
    {
        $id=$this->book(); $proofs=new AppointmentSlotEditContext($this->db);
        foreach(['completed','in_chair','cancelled','no_show'] as $stage){
            $this->db->table('appointments')->where(['id'=>$id,'clinic_id'=>$this->clinic])->update(['status'=>'scheduled']);
            $proof=$proofs->issue($this->clinic,$id); $this->assertIsString($proof);
            $this->db->table('appointments')->where(['id'=>$id,'clinic_id'=>$this->clinic])->update(['status'=>$stage]);
            $this->assertNull($proofs->resolve($this->clinic,$id,$proof),$stage.' stale proof');
            $this->assertNull($proofs->issue($this->clinic,$id),$stage.' new proof');
        }
        $this->db->table('appointments')->where('id',$id)->update(['status'=>'completed']);
        $before=$this->db->table('appointments')->where('id',$id)->get()->getRowArray();
        $result=(new AppointmentSchedulingService($this->db))->updateForClinic($this->clinic,$id,['notes'=>'Completed visit administrative note'],$this->actor);
        $this->assertTrue($result['success'],json_encode($result));
        $after=$this->db->table('appointments')->where('id',$id)->get()->getRowArray();
        foreach(['id','clinic_id','patient_id','provider_user_id','chair_name','appointment_date','appointment_time','duration','status'] as $key)$this->assertSame($before[$key],$after[$key],$key);
        $this->assertSame('Completed visit administrative note',$after['notes']);
    }

    public function testProofRejectsPastInactivePatientAndFeatureOffAssignedVisits(): void
    {
        $id=$this->book(); $proofs=new AppointmentSlotEditContext($this->db);
        foreach([
            ['appointments',['appointment_date'=>'2001-01-01']],
            ['patients',['status'=>'inactive']],
            ['settings',['setting_value'=>'0']],
        ] as [$table,$change]){
            $proof=$proofs->issue($this->clinic,$id); $this->assertIsString($proof);
            $where=$table==='appointments'?['id'=>$id,'clinic_id'=>$this->clinic]:($table==='patients'?['id'=>$this->patient,'clinic_id'=>$this->clinic]:['clinic_id'=>$this->clinic,'setting_key'=>'dynamic_doctor_scheduling_enabled']);
            $this->db->table($table)->where($where)->update($change);
            $this->assertNull($proofs->resolve($this->clinic,$id,$proof),$table);
            $this->assertNull($proofs->issue($this->clinic,$id),$table);
            $restore=$table==='appointments'?['appointment_date'=>'2026-11-09']:($table==='patients'?['status'=>'active']:['setting_value'=>'1']);
            $this->db->table($table)->where($where)->update($restore);
        }
    }

    public function testFutureAcceptedProcedureLinkDoesNotLockButEncounterLinkDoes(): void
    {
        $id=$this->book(); $proofs=new AppointmentSlotEditContext($this->db);
        $this->db->table('treatments')->insert(['clinic_id'=>$this->clinic,'patient_id'=>$this->patient,'treatment_id'=>$this->prefix,'treatment_name'=>'Synthetic','treatment_type'=>'cleaning','status'=>'active','start_date'=>'2026-10-15','estimated_duration'=>30]);
        $treatment=(int)$this->db->insertID();
        $this->db->table('clinical_procedures')->insert(['clinic_id'=>$this->clinic,'patient_id'=>$this->patient,'treatment_id'=>$treatment,'provider_type'=>'none','procedure_name'=>'Synthetic future work','status'=>'scheduled','scheduled_appointment_id'=>$id,'created_by'=>$this->actor,'updated_by'=>$this->actor,'status_changed_by'=>$this->actor]);
        $procedure=(int)$this->db->insertID();
        $proof=$proofs->issue($this->clinic,$id); $this->assertIsString($proof);
        $this->assertSame($id,$proofs->resolve($this->clinic,$id,$proof));
        $this->db->table('clinical_procedures')->where(['id'=>$procedure,'clinic_id'=>$this->clinic])->update(['appointment_id'=>$id]);
        $this->assertNull($proofs->resolve($this->clinic,$id,$proof));
        $this->assertNull($proofs->issue($this->clinic,$id));
        $blocked=(new AppointmentSchedulingService($this->db))->updateForClinic($this->clinic,$id,['appointment_time'=>'11:00'],$this->actor);
        $this->assertFalse($blocked['success'],json_encode($blocked));
        $this->assertSame('10:00:00',$this->db->table('appointments')->where('id',$id)->get()->getRow('appointment_time'));
    }
}
