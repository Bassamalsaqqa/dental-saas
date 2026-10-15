<?php

declare(strict_types=1);
namespace Tests\Unit;
use App\Services\AppointmentSchedulingService;
use Tests\Support\PhaseBFixture;

final class PhaseBClinicalAtomicityTest extends PhaseBFixture
{
    private function procedure(): int
    {
        $this->db->table('treatments')->insert(['clinic_id'=>$this->clinic,'patient_id'=>$this->patient,'treatment_id'=>$this->prefix,'treatment_name'=>'Synthetic accepted work','treatment_type'=>'cleaning','status'=>'active','start_date'=>'2026-10-15','estimated_duration'=>30]);
        $treatment=(int)$this->db->insertID();
        $this->db->table('clinical_procedures')->insert(['clinic_id'=>$this->clinic,'patient_id'=>$this->patient,'treatment_id'=>$treatment,'provider_type'=>'none','procedure_name'=>'Synthetic procedure','status'=>'accepted','created_by'=>$this->actor,'updated_by'=>$this->actor,'status_changed_by'=>$this->actor]);
        return (int)$this->db->insertID();
    }
    public function testAcceptedHandoffLinksFutureAppointmentAndHistoryAtomically(): void
    {
        $id=$this->procedure();
        $result=(new AppointmentSchedulingService($this->db))->createForClinic($this->clinic,$this->payload(['clinical_procedure_id'=>$id]),$this->actor);
        $this->assertTrue($result['success'],json_encode($result));
        $row=$this->db->table('clinical_procedures')->where('id',$id)->get()->getRowArray();
        $this->assertSame('scheduled',$row['status']);
        $this->assertSame((int)$result['id'],(int)$row['scheduled_appointment_id']);
        $this->assertNull($row['appointment_id']);
        $this->assertSame(1,$this->db->table('clinical_procedure_status_history')->where(['procedure_id'=>$id,'to_status'=>'scheduled'])->countAllResults());
    }
    public function testHistoryFailureRollsBackAppointmentAndProcedureLink(): void
    {
        $id=$this->procedure(); $trigger='phaseb_history_'.bin2hex(random_bytes(5));
        $this->db->query("CREATE TRIGGER $trigger BEFORE INSERT ON clinical_procedure_status_history FOR EACH ROW BEGIN IF NEW.clinic_id=".$this->clinic." THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='Synthetic required history failure'; END IF; END");
        try {
            $result=(new AppointmentSchedulingService($this->db))->createForClinic($this->clinic,$this->payload(['clinical_procedure_id'=>$id]),$this->actor);
            $this->assertFalse($result['success'],json_encode($result));
            $this->db->resetTransStatus();
            $row=$this->db->table('clinical_procedures')->where('id',$id)->get()->getRowArray();
            $this->assertSame('accepted',$row['status']); $this->assertNull($row['scheduled_appointment_id']);
            $this->assertSame(0,$this->db->table('appointments')->where('clinic_id',$this->clinic)->countAllResults());
        } finally { $this->db->query("DROP TRIGGER IF EXISTS $trigger"); }
    }
    public function testFailedAuditNeverCommitsBooking(): void
    {
        $trigger='phaseb_audit_'.bin2hex(random_bytes(5));
        $this->db->query("CREATE TRIGGER $trigger BEFORE INSERT ON activity_logs FOR EACH ROW BEGIN IF NEW.clinic_id=".$this->clinic." THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='Synthetic required audit failure'; END IF; END");
        try {
            $result=(new AppointmentSchedulingService($this->db))->createForClinic($this->clinic,$this->payload(),$this->actor);
            $this->assertFalse($result['success'],json_encode($result)); $this->db->resetTransStatus();
            $this->assertSame(0,$this->db->table('appointments')->where('clinic_id',$this->clinic)->countAllResults());
        } finally { $this->db->query("DROP TRIGGER IF EXISTS $trigger"); }
    }
}
