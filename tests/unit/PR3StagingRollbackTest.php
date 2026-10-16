<?php

declare(strict_types=1);
namespace Tests\Unit;

use App\Services\ClinicSchedulingConfigurationService;
use App\Services\ProviderDirectoryService;
use App\Services\ScheduleImpactAuditStore;
use App\Services\ScheduleImpactService;
use CodeIgniter\I18n\Time;
use Tests\Support\PhaseBFixture;

/** Configuration recovery is a new audited change; it never rewrites appointment history. */
final class PR3StagingRollbackTest extends PhaseBFixture
{
    protected function tearDown(): void
    {
        while($this->db->transDepth>0){$this->db->transRollback();}
        $this->db->table('control_plane_audits')->whereIn('actor_user_id',$this->users)->delete();
        Time::setTestNow();
        parent::tearDown();
    }

    public function testPreviousHoursAreRestoredThroughFreshProofAndNewAudit(): void
    {
        Time::setTestNow('2030-01-07 06:00:00','UTC');
        $this->db->table('appointments')->insert($this->payload(['appointment_date'=>'2030-01-08','appointment_time'=>'16:30'])+
            ['clinic_id'=>$this->clinic,'appointment_id'=>'R'.bin2hex(random_bytes(8)),'chair_name'=>'Preserved original snapshot','created_by'=>$this->actor]);
        $id=(int)$this->db->insertID();$before=$this->db->table('appointments')->where('id',$id)->get()->getRowArray();
        $impact=new ScheduleImpactService($this->db);$writer=new ClinicSchedulingConfigurationService($this->db);
        $first=$this->hours('16:00');$proof=$this->preview($impact,$first);
        $this->assertTrue($writer->configureHours($this->clinic,$first,$this->actor,$proof)['success']);
        $pointerRow=$this->db->table('settings')->where(['clinic_id'=>$this->clinic,'setting_key'=>'schedule_impact_latest_audit'])->get()->getRowArray();
        $firstPointer=json_decode($pointerRow['setting_value'],true,512,JSON_THROW_ON_ERROR);
        $store=new ScheduleImpactAuditStore($this->db);$firstBundle=$store->read($this->clinic,$firstPointer);$this->assertNotNull($firstBundle);
        $this->assertSame('17:00',$firstBundle['before_configuration']['working_hours_end']);
        $this->assertSame('16:00',$firstBundle['after_configuration']['working_hours_end']);
        $this->assertSame('16:30:00',$firstBundle['affected_items'][0]['stored_appointment_time']);

        $restore=$this->hours('17:00');$restoreProof=$this->preview($impact,$restore);
        $this->assertSame([],$restoreProof['dispositions']);
        $this->assertTrue($writer->configureHours($this->clinic,$restore,$this->actor,$restoreProof)['success']);
        $latest=$impact->getLatestAuditRecord($this->clinic);$this->assertNotNull($latest);
        $this->assertSame('16:00',$latest['before_configuration']['working_hours_end']);
        $this->assertSame('17:00',$latest['after_configuration']['working_hours_end']);
        $this->assertNotSame($firstBundle['review_id'],$latest['review_id']);
        $this->assertSame($firstBundle,$store->read($this->clinic,$firstPointer),'Restoration must retain original immutable review evidence.');
        $this->assertSame($before,$this->db->table('appointments')->where('id',$id)->get()->getRowArray());
        $this->assertSame(2,$this->db->table('control_plane_audits')->where(['actor_user_id'=>$this->actor,'event_type'=>'doctor_scheduling_settings_update'])->countAllResults());
        $this->assertSame([],$impact->getRetainedExceptions($this->clinic));
        $this->assertSame(0,$this->db->transDepth);
        file_put_contents(ROOTPATH.'tmp/pr3-staging-rollback.json',json_encode(['scope'=>'Synthetic checked hours restoration; no code rollback or feature-off activation.',
            'original_review_id'=>$firstBundle['review_id'],'restoration_review_id'=>$latest['review_id'],'appointment_sha256'=>hash('sha256',json_encode($before,JSON_THROW_ON_ERROR)),
            'previous_hours_restored'=>true,'original_review_preserved'=>true,'appointment_unchanged'=>true],JSON_PRETTY_PRINT|JSON_THROW_ON_ERROR));
    }

    public function testUnconfiguredClinicStaysDefaultOffAndReadsDoNotCreateFeatureFlag(): void
    {
        $before=$this->db->table('settings')->where('clinic_id',$this->otherClinic)->get()->getResultArray();
        $directory=new ProviderDirectoryService($this->db);
        $this->assertFalse($directory->isEnabled($this->otherClinic));
        $context=$directory->getBookingContext($this->otherClinic);
        $this->assertFalse($context['enabled']);$this->assertSame([],$context['providers']);
        $this->assertSame($before,$this->db->table('settings')->where('clinic_id',$this->otherClinic)->get()->getResultArray());
    }

    private function hours(string $end): array
    {
        return (new ScheduleImpactService($this->db))->validateProposedHoursDto(['working_hours_start'=>'09:00','working_hours_end'=>$end,
            'working_days'=>['monday','tuesday','wednesday','thursday','friday','saturday','sunday'],'appointment_duration'=>'30','dynamic_doctor_scheduling_enabled'=>'1'],$this->clinic);
    }

    private function preview(ScheduleImpactService $impact,array $dto): array
    {
        $report=$impact->generateReport($this->db,$this->clinic,'clinic_hours',$dto);$dispositions=[];
        foreach($report['affected_appointment_ids'] as $id){$dispositions[$id]=['action'=>'retain','reason'=>'Synthetic operator explicitly reviewed this unchanged appointment'];}
        return ['token'=>$impact->issueProof($this->clinic,$this->actor,'clinic_hours',$dto,$report),'acknowledged'=>true,'dispositions'=>$dispositions];
    }
}
