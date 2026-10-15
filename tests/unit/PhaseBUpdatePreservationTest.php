<?php

declare(strict_types=1);
namespace Tests\Unit;

use App\Libraries\ApiRuntimeContext;
use App\Services\AppointmentSchedulingService;
use App\Services\ProviderDirectoryService;
use Tests\Support\PhaseBFixture;

/** Independent behavioral regressions for update ownership and historical preservation. */
final class PhaseBUpdatePreservationTest extends PhaseBFixture
{
    public function testNotesOnlyFutureVisitPreservesInactiveDoctorIdentitySnapshotAndSlot(): void
    {
        $service=new AppointmentSchedulingService($this->db);
        $id=$this->create($service);
        $before=$this->row($id);
        $this->db->table('users')->where('id',$this->providers[0])->update(['active'=>0]);
        $result=$service->updateForClinic($this->clinic,$id,['notes'=>'Administrative note after provider departure'],$this->actor);
        $this->assertTrue($result['success'],json_encode($result));
        $after=$this->row($id);
        $this->assertSame('Administrative note after provider departure',$after['notes']);
        $this->assertPreservedBooking($before,$after);
        $this->assertSame(0,$this->db->transDepth);
    }

    public function testPatientAndClinicCannotBeSubstitutedOnUpdate(): void
    {
        $service=new AppointmentSchedulingService($this->db);
        $id=$this->create($service);
        $this->db->table('patients')->insert(['clinic_id'=>$this->clinic,'patient_id'=>$this->prefix.'_other',
            'first_name'=>'Synthetic','last_name'=>'Other','gender'=>'other','status'=>'active','created_by'=>$this->actor]);
        $otherPatient=(int)$this->db->insertID();
        $before=$this->row($id);
        $result=$service->updateForClinic($this->clinic,$id,['patient_id'=>$otherPatient,'notes'=>'Attempted patient swap'],$this->actor);
        $this->assertFalse($result['success'],json_encode($result));
        $this->assertSame($before,$this->row($id));
        $result=$service->updateForClinic($this->otherClinic,$id,['notes'=>'Cross-clinic write'],$this->actor);
        $this->assertFalse($result['success'],json_encode($result));
        $this->assertSame($before,$this->row($id));
        $service->updateForClinic($this->clinic,$id,['clinic_id'=>$this->otherClinic,'notes'=>'Body clinic cannot select authority'],$this->actor);
        $this->assertSame($this->clinic,(int)$this->row($id)['clinic_id']);
        $this->assertSame($this->patient,(int)$this->row($id)['patient_id']);
        $this->assertSame(0,$this->db->table('appointments')->where('clinic_id',$this->otherClinic)->countAllResults());
    }

    public function testLockedVisitCannotChangeScheduleTypeProviderOrVisitStage(): void
    {
        $service=new AppointmentSchedulingService($this->db);
        $id=$this->create($service);
        $this->db->table('appointments')->where('id',$id)->update(['status'=>'in_chair']);
        $before=$this->row($id);
        foreach([['appointment_date'=>'2026-11-10'],['appointment_time'=>'11:00'],['duration'=>45],
            ['appointment_type'=>'treatment'],['provider_user_id'=>$this->providers[1]],['status'=>'completed']] as $change){
            $result=$service->updateForClinic($this->clinic,$id,$change,$this->actor);
            $this->assertFalse($result['success'],json_encode($change).' '.json_encode($result));
            $this->assertSame($before,$this->row($id));
        }
        $result=$service->updateForClinic($this->clinic,$id,['notes'=>'Allowed clinical visit annotation'],$this->actor);
        $this->assertTrue($result['success'],json_encode($result));
        $this->assertPreservedBooking($before,$this->row($id));
    }

    public function testDoctorRenameNeverOverwritesSnapshotOnSameDoctorReschedule(): void
    {
        $service=new AppointmentSchedulingService($this->db);
        $id=$this->create($service);
        $before=$this->row($id);
        $this->db->table('users')->where('id',$this->providers[0])->update(['first_name'=>'Renamed','last_name'=>'Clinician']);
        $result=$service->updateForClinic($this->clinic,$id,['appointment_time'=>'11:00','provider_user_id'=>$this->providers[0]],$this->actor);
        $this->assertTrue($result['success'],json_encode($result));
        $after=$this->row($id);
        $this->assertSame($before['chair_name'],$after['chair_name']);
        $this->assertSame($before['provider_user_id'],$after['provider_user_id']);
        $this->assertSame('11:00',substr($after['appointment_time'],0,5));
        $this->assertSame($before['appointment_id'],$after['appointment_id']);
    }

    public function testAuditActorComesFromValidatedContextNotInputOrForeignBrowserSession(): void
    {
        $service=new AppointmentSchedulingService($this->db);
        $id=$this->create($service,['created_by'=>$this->providers[0],'actor_user_id'=>$this->providers[0],'user_id'=>$this->providers[0]]);
        $this->assertAudit($id,$this->actor);
        $before=$this->row($id);
        $count=$this->db->table('activity_logs')->where('clinic_id',$this->clinic)->countAllResults();
        $forged=$service->updateForClinic($this->clinic,$id,['notes'=>'Forged actor'],$this->providers[0]);
        $this->assertFalse($forged['success'],json_encode($forged));
        $this->assertSame($before,$this->row($id));
        $this->assertSame($count,$this->db->table('activity_logs')->where('clinic_id',$this->clinic)->countAllResults());
        session()->set(['user_id'=>$this->providers[0],'identity'=>'unrelated-browser@example.test','active_clinic_id'=>$this->otherClinic,
            'global_mode'=>true,'is_impersonating'=>true,'impersonated_clinic_id'=>$this->otherClinic]);
        ApiRuntimeContext::setUserId($this->actor);ApiRuntimeContext::setClinicId($this->clinic);
        $api=$service->createForClinic($this->clinic,$this->payload(['appointment_time'=>'11:00','created_by'=>$this->providers[0],
            'actor_user_id'=>$this->providers[0]]),$this->actor,'bearer');
        $this->assertTrue($api['success'],json_encode($api));
        $this->assertSame($this->actor,(int)$this->row((int)$api['id'])['created_by']);
        $this->assertAudit((int)$api['id'],$this->actor);
    }

    public function testFourDuplicateNamedDoctorsHaveIndependentStableCapacity(): void
    {
        $directory=new ProviderDirectoryService($this->db);
        $listed=$directory->listForClinic($this->clinic);
        $this->assertCount(4,$listed);
        $this->assertCount(4,array_unique(array_map('intval',array_column($listed,'user_id'))));
        $service=new AppointmentSchedulingService($this->db);
        $snapshots=[];
        foreach($this->providers as $provider){
            $id=$this->create($service,['provider_user_id'=>$provider]);
            $row=$this->row($id);
            $this->assertSame($provider,(int)$row['provider_user_id']);
            $snapshots[]=$row['chair_name'];
        }
        $this->assertCount(1,array_unique($snapshots));
        $this->assertSame(4,$this->db->table('appointments')->where('clinic_id',$this->clinic)->countAllResults());
    }

    public function testFeatureOffNeverReinterpretsProviderLinkedVisitAsLegacyResource(): void
    {
        $service=new AppointmentSchedulingService($this->db);
        $id=$this->create($service);
        $before=$this->row($id);
        // Simulate preexisting configuration drift; the public settings form itself refuses unsafe disabling.
        $this->setting('dynamic_doctor_scheduling_enabled',false);
        $note=$service->updateForClinic($this->clinic,$id,['notes'=>'Preserve provider while mode is off'],$this->actor);
        $this->assertTrue($note['success'],json_encode($note));
        $this->assertPreservedBooking($before,$this->row($id));
        $before=$this->row($id);
        foreach([['appointment_time'=>'11:00'],['provider_user_id'=>$this->providers[1]],['chair_name'=>'Legacy room']] as $change){
            $result=$service->updateForClinic($this->clinic,$id,$change,$this->actor);
            $this->assertFalse($result['success'],json_encode($change).' '.json_encode($result));
            $this->assertSame($before,$this->row($id));
        }
    }

    public function testFailedBeginAndCommitCannotClaimSuccessOrLeaveAppointmentAndAudit(): void
    {
        foreach(['transBegin','transCommit'] as $method){
            $connection=$this->getMockBuilder(\CodeIgniter\Database\MySQLi\Connection::class)
                ->setConstructorArgs([config('Database')->tests])->onlyMethods([$method])->getMock();
            // CI resolves driver builders/results from the concrete mock connection class name.
            foreach(['Builder','Result'] as $type){
                $alias=str_replace('Connection',$type,get_class($connection));
                if(!class_exists($alias,false))class_alias($type==='Builder'?\CodeIgniter\Database\MySQLi\Builder::class:\CodeIgniter\Database\MySQLi\Result::class,$alias);
            }
            $connection->method($method)->willReturn(false);
            try{
                $result=(new AppointmentSchedulingService($connection))->createForClinic($this->clinic,$this->payload(),$this->actor);
                $this->assertFalse($result['success'],$method.' '.json_encode($result));
                $this->assertSame(0,$connection->transDepth,$method);
                $this->assertSame(0,$this->db->table('appointments')->where('clinic_id',$this->clinic)->countAllResults(),$method);
                $this->assertSame(0,$this->db->table('activity_logs')->where('clinic_id',$this->clinic)->countAllResults(),$method);
            } finally {
                while($connection->transDepth>0)$connection->transRollback();
                $connection->close();
            }
        }
    }

    private function create(AppointmentSchedulingService $service,array $extra=[]):int
    {
        $result=$service->createForClinic($this->clinic,$this->payload($extra),$this->actor);
        $this->assertTrue($result['success'],json_encode($result));
        return (int)$result['id'];
    }
    private function row(int $id):array {return $this->db->table('appointments')->where('id',$id)->get()->getRowArray();}
    private function assertPreservedBooking(array $before,array $after):void
    {
        foreach(['id','clinic_id','patient_id','appointment_id','provider_user_id','chair_name','appointment_date','appointment_time',
            'duration','appointment_type','status','created_by','created_at'] as $field)$this->assertSame($before[$field],$after[$field],$field);
    }
    private function assertAudit(int $id,int $actor):void
    {
        $logs=$this->db->table('activity_logs')->where(['entity_type'=>'appointment','entity_id'=>$id])->get()->getResultArray();
        $this->assertNotEmpty($logs);
        foreach($logs as $log){$this->assertSame($actor,(int)$log['user_id']);$this->assertSame($this->clinic,(int)$log['clinic_id']);}
    }
}
