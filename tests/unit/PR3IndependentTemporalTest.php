<?php

declare(strict_types=1);
namespace Tests\Unit;

use App\Services\AppointmentSchedulingService;
use App\Services\AppointmentSlotEditContext;
use App\Services\ClinicSchedulingConfigurationService;
use App\Services\ScheduleImpactService;
use CodeIgniter\I18n\Time;
use Tests\Support\PhaseBFixture;

/** Independent public-boundary tests of the requested clinic-local temporal correction. */
final class PR3IndependentTemporalTest extends PhaseBFixture
{
    protected function setUp():void { parent::setUp();Time::setTestNow('2030-01-07 10:00:00','UTC'); }
    protected function tearDown():void
    {
        Time::setTestNow();
        if(isset($this->actor))$this->db->table('control_plane_audits')->where('actor_user_id',$this->actor)->delete();
        parent::tearDown();
    }
    private function seed(string $time,int $duration=30,string $date='2030-01-07'):int
    {
        $row=$this->payload(['appointment_date'=>$date,'appointment_time'=>$time,'duration'=>$duration]);
        $this->db->table('appointments')->insert($row+['clinic_id'=>$this->clinic,'appointment_id'=>'I'.bin2hex(random_bytes(9)),'chair_name'=>'Immutable doctor snapshot','created_by'=>$this->actor]);
        return (int)$this->db->insertID();
    }
    private function hours():array { return (new ScheduleImpactService($this->db))->validateProposedHoursDto(['working_hours_start'=>'09:00','working_hours_end'=>'17:00','working_days'=>['monday','tuesday','wednesday','thursday','friday','saturday','sunday'],'appointment_duration'=>'30','dynamic_doctor_scheduling_enabled'=>'1'],$this->clinic); }
    public function testNoonPolicyOmitsElapsedIncludesOngoingAndUpcomingWithoutChangingRows():void
    {
        $past=$this->seed('09:00');$ended=$this->seed('11:30');$ongoing=$this->seed('11:45');$exact=$this->seed('12:00');$later=$this->seed('13:00');$tomorrow=$this->seed('09:00',30,'2030-01-08');
        $before=$this->db->table('appointments')->where('clinic_id',$this->clinic)->orderBy('id')->get()->getResultArray();
        $report=(new ScheduleImpactService($this->db))->generateReport($this->db,$this->clinic,'clinic_hours',$this->hours());
        $byId=array_column($report['items'],null,'appointment_id');
        $this->assertSame([$ongoing,$exact,$later,$tomorrow],array_keys($byId));
        $this->assertFalse($byId[$ongoing]['reschedulable']);$this->assertEmpty($byId[$ongoing]['edit_url']);
        $this->assertTrue($byId[$exact]['reschedulable']);$this->assertTrue($byId[$later]['reschedulable']);
        $this->assertSame($before,$this->db->table('appointments')->where('clinic_id',$this->clinic)->orderBy('id')->get()->getResultArray());
        $proofs=new AppointmentSlotEditContext($this->db);
        foreach([$past,$ended,$ongoing] as $id)$this->assertNull($proofs->issue($this->clinic,$id));
        $this->assertIsString($proofs->issue($this->clinic,$exact));
    }
    public function testProofAndMutationRecheckSecondsAfterStartWhileNotesRemainAllowed():void
    {
        $id=$this->seed('12:30');$proofs=new AppointmentSlotEditContext($this->db);$token=$proofs->issue($this->clinic,$id);$this->assertIsString($token);
        Time::setTestNow('2030-01-07 10:30:01','UTC');
        $this->assertNull($proofs->resolve($this->clinic,$id,$token));
        $before=$this->db->table('appointments')->where('id',$id)->get()->getRowArray();$service=new AppointmentSchedulingService($this->db);
        $result=$service->updateForClinic($this->clinic,$id,['appointment_time'=>'14:00'],$this->actor);$this->assertFalse($result['success'],json_encode($result));
        $this->assertSame($before,$this->db->table('appointments')->where('id',$id)->get()->getRowArray());
        $notes=$service->updateForClinic($this->clinic,$id,['notes'=>'Preserved ongoing visit annotation'],$this->actor);$this->assertTrue($notes['success'],json_encode($notes));
        $after=$this->db->table('appointments')->where('id',$id)->get()->getRowArray();unset($before['notes'],$before['updated_at'],$after['notes'],$after['updated_at']);$this->assertSame($before,$after);
        foreach(['11:00','12:30'] as $time){
            $create=$service->createForClinic($this->clinic,$this->payload(['appointment_date'=>'2030-01-07','appointment_time'=>$time,'provider_user_id'=>$this->providers[1]]),$this->actor);
            $this->assertFalse($create['success'],json_encode($create));
        }
    }
    public function testPreviewRemainsValidWithinPhaseButRejectsCrossingIntoOngoing():void
    {
        $id=$this->seed('12:30');$impact=new ScheduleImpactService($this->db);$dto=$this->hours();$dto['working_hours_start']='13:00';
        $report=$impact->generateReport($this->db,$this->clinic,'clinic_hours',$dto);$token=$impact->issueProof($this->clinic,$this->actor,'clinic_hours',$dto,$report);
        Time::setTestNow('2030-01-07 10:15:00','UTC');
        $same=$impact->generateReport($this->db,$this->clinic,'clinic_hours',$dto);$this->assertSame($report['state_digest'],$same['state_digest'],'Clock alone within an unchanged phase must not stale every preview.');
        $dispositions=[$id=>['action'=>'retain','reason'=>'Reviewed upcoming visit']];
        $this->assertNull($impact->validateProofAndDispositions($this->db,$this->clinic,$this->actor,'clinic_hours',$dto,null,$token,true,$dispositions,$same));
        Time::setTestNow('2030-01-07 10:30:01','UTC');
        $result=(new ClinicSchedulingConfigurationService($this->db))->configureHours($this->clinic,$dto,$this->actor,['token'=>$token,'acknowledged'=>true,'dispositions'=>[$id=>['action'=>'retain','reason'=>'Reviewed upcoming visit']]]);
        $this->assertFalse($result['success'],json_encode($result));$this->assertSame(lang('DoctorScheduleSettings.staleProof'),$result['message']);$this->assertSame('09:00',$this->db->table('settings')->where(['clinic_id'=>$this->clinic,'setting_key'=>'working_hours_start'])->get()->getRow('setting_value'));
    }
    public function testClinicTimezoneWinsOverServerTimezoneAtMidnight():void
    {
        $previous=date_default_timezone_get();date_default_timezone_set('Pacific/Honolulu');
        try{
            $this->setting('timezone','Pacific/Kiritimati');Time::setTestNow('2030-01-07 10:15:00','UTC');
            $past=$this->seed('23:30',30,'2030-01-07');$ongoing=$this->seed('00:00',30,'2030-01-08');$upcoming=$this->seed('00:30',30,'2030-01-08');
            $report=(new ScheduleImpactService($this->db))->generateReport($this->db,$this->clinic,'clinic_hours',$this->hours());$ids=array_column($report['items'],'appointment_id');
            $this->assertNotContains($past,$ids);$this->assertContains($ongoing,$ids);$this->assertContains($upcoming,$ids);
            $proof=new AppointmentSlotEditContext($this->db);$this->assertNull($proof->issue($this->clinic,$ongoing));$this->assertIsString($proof->issue($this->clinic,$upcoming));
        }finally{date_default_timezone_set($previous);}
    }
    public function testEnglishArabicRecheckMarksOngoingAndNeverOffersItsReschedule():void
    {
        $past=$this->seed('09:00');$ongoing=$this->seed('11:45');$later=$this->seed('13:00');
        foreach(['en','ar'] as $locale){
            $this->login($locale);$response=$this->withSession(session()->get())->withHeaders([])->get('/settings/schedule-impact-recheck');$response->assertStatus(200);
            $html=$response->response()->getBody();$doc=new \DOMDocument();@$doc->loadHTML('<?xml encoding="UTF-8">'.$html);$xp=new \DOMXPath($doc);
            $markers=$xp->query('//span[@data-temporal-state="in_progress"]');$this->assertSame(1,$markers->length);
            $this->assertStringContainsString(lang('DoctorScheduleSettings.inProgressVisit',[],$locale),$markers->item(0)->textContent);
            if($locale==='ar'){$this->assertSame(1,preg_match('/[\x{0600}-\x{06ff}]/u',$markers->item(0)->textContent));$this->assertStringNotContainsString('???',$markers->item(0)->textContent);}
            $this->assertSame(0,$xp->query('//a[contains(@href,"appointment/'.$ongoing.'/edit")]')->length);
            $dto=$this->hours();$dto['working_hours_start']='14:00';
            $security=service('security');$hash=$security->getHash();$cookie=config('Security')->cookieName;
            $preview=$this->withSession(session()->get())->withHeaders(['Cookie'=>$cookie.'='.$hash,'X-CSRF-TOKEN'=>$hash])->post('/settings/preview-schedule-impact',['target_type'=>'clinic_hours']+$dto);$preview->assertStatus(200);
            $previewDoc=new \DOMDocument();@$previewDoc->loadHTML('<?xml encoding="UTF-8">'.$preview->response()->getBody());$previewXp=new \DOMXPath($previewDoc);
            $this->assertSame(0,$previewXp->query('//a[contains(@href,"appointment/'.$ongoing.'/edit")]')->length);
            $this->assertSame(1,$previewXp->query('//a[contains(@href,"appointment/'.$later.'/edit")]')->length);
            $this->assertSame(0,$xp->query('//td/span[text()="#'.$past.'"]')->length);
        }
    }

    public function testDstGapAndFoldCannotReceiveProofOrBeBooked():void
    {
        $this->setting('timezone','America/New_York');$service=new AppointmentSchedulingService($this->db);$proof=new AppointmentSlotEditContext($this->db);
        foreach([
            ['2030-03-10 06:00:00','2030-03-10','02:30','INVALID_DST_INTERVAL'],
            ['2030-11-03 04:30:00','2030-11-03','01:30','AMBIGUOUS_DST_INTERVAL'],
        ] as [$clock,$date,$time,$expectedCode]){
            Time::setTestNow($clock,'UTC');$id=$this->seed($time,30,$date);
            $this->assertNull($proof->issue($this->clinic,$id),'Invalid local DST interval cannot be omitted from advice.');
            $result=$service->createForClinic($this->clinic,$this->payload(['appointment_date'=>$date,'appointment_time'=>$time,'provider_user_id'=>$this->providers[1]]),$this->actor);
            $this->assertFalse($result['success'],json_encode($result));$this->assertSame($expectedCode,$result['code']);
            $before=$this->db->table('appointments')->where('id',$id)->get()->getRowArray();
            $report=(new ScheduleImpactService($this->db))->generateReport($this->db,$this->clinic,'clinic_hours',$this->hours());
            $items=array_column($report['items'],null,'appointment_id');$this->assertArrayHasKey($id,$items);
            $this->assertFalse($items[$id]['before_outcome']['compliant']);$this->assertFalse($items[$id]['reschedulable']);
            $this->assertSame($before,$this->db->table('appointments')->where('id',$id)->get()->getRowArray());
            $this->db->table('appointments')->where(['id'=>$id,'clinic_id'=>$this->clinic])->delete();
        }
    }

    public function testSameDaySlotAdviceUsesSecondsAndClinicClockInBothModes(): void
    {
        $service=new AppointmentSchedulingService($this->db);
        foreach([true,false] as $dynamic){
            $this->setting('dynamic_doctor_scheduling_enabled',$dynamic);
            Time::setTestNow('2030-01-07 10:00:00','UTC');
            $atStart=array_column($service->availableSlots($this->clinic,'2030-01-07',30,$dynamic?$this->providers[0]:null,null,'Legacy room'),'value');
            $this->assertContains('12:00',$atStart);$this->assertNotContains('11:45',$atStart);
            Time::setTestNow('2030-01-07 10:00:01','UTC');
            $afterStart=array_column($service->availableSlots($this->clinic,'2030-01-07',30,$dynamic?$this->providers[0]:null,null,'Legacy room'),'value');
            $this->assertNotContains('12:00',$afterStart);$this->assertContains('12:15',$afterStart);
            $this->assertSame([],$service->availableSlots($this->clinic,'2030-01-06',30,$dynamic?$this->providers[0]:null));
        }
    }

    public function testUnsupportedStoredSecondsAreVisibleConflictsAndNeverEditable(): void
    {
        $id=$this->seed('13:00:37');$before=$this->db->table('appointments')->where('id',$id)->get()->getRowArray();
        $report=(new ScheduleImpactService($this->db))->generateReport($this->db,$this->clinic,'clinic_hours',$this->hours());
        $item=array_column($report['items'],null,'appointment_id')[$id];
        $this->assertSame('invalid',$item['temporal_state']);$this->assertFalse($item['before_outcome']['compliant']);$this->assertFalse($item['after_outcome']['compliant']);
        $this->assertContains($id,$report['affected_appointment_ids']);$this->assertFalse($item['reschedulable']);
        $this->assertNull((new AppointmentSlotEditContext($this->db))->issue($this->clinic,$id));
        $this->assertSame($before,$this->db->table('appointments')->where('id',$id)->get()->getRowArray());
    }

    public function testRetainedEvidenceExpiresAtVisitEndUsingSameClinicClock(): void
    {
        $id=$this->seed('12:30');$impact=new ScheduleImpactService($this->db);$dto=$this->hours();$dto['working_hours_start']='14:00';
        $report=$impact->generateReport($this->db,$this->clinic,'clinic_hours',$dto);$token=$impact->issueProof($this->clinic,$this->actor,'clinic_hours',$dto,$report);
        $result=(new ClinicSchedulingConfigurationService($this->db))->configureHours($this->clinic,$dto,$this->actor,['token'=>$token,'acknowledged'=>true,'dispositions'=>[$id=>['action'=>'retain','reason'=>'Staff reviewed and retained the original booking']]]);
        $this->assertTrue($result['success'],json_encode($result));
        $this->assertArrayHasKey($id,$impact->getRetainedExceptions($this->clinic));
        Time::setTestNow('2030-01-07 10:45:00','UTC');$this->assertArrayHasKey($id,$impact->getRetainedExceptions($this->clinic));
        Time::setTestNow('2030-01-07 11:00:00','UTC');$this->assertArrayNotHasKey($id,$impact->getRetainedExceptions($this->clinic));
        $this->assertSame('12:30:00',$this->db->table('appointments')->where('id',$id)->get()->getRow('appointment_time'));
    }

    public function testRetentionBindsExactStoredSecondsForCanonicalAndInvalidVisits(): void
    {
        $id=$this->seed('13:00');$impact=new ScheduleImpactService($this->db);$dto=$this->hours();$dto['working_hours_start']='14:00';
        $configuration=new ClinicSchedulingConfigurationService($this->db);
        foreach(['13:00:00','13:00:37'] as $time){
            $this->db->table('appointments')->where(['id'=>$id,'clinic_id'=>$this->clinic])->update(['appointment_time'=>$time]);
            $report=$impact->generateReport($this->db,$this->clinic,'clinic_hours',$dto);$proof=$impact->issueProof($this->clinic,$this->actor,'clinic_hours',$dto,$report);
            $result=$configuration->configureHours($this->clinic,$dto,$this->actor,['token'=>$proof,'acknowledged'=>true,'dispositions'=>[$id=>['action'=>'retain','reason'=>'Acknowledged the exact unchanged appointment interval']]]);
            $this->assertTrue($result['success'],json_encode($result));$this->assertArrayHasKey($id,$impact->getRetainedExceptions($this->clinic));
            $audit=$impact->getLatestAuditRecord($this->clinic);$this->assertSame($time,$audit['affected_items'][0]['stored_appointment_time']);
            $this->db->table('appointments')->where(['id'=>$id,'clinic_id'=>$this->clinic])->update(['appointment_time'=>'13:00:38']);
            $this->assertArrayNotHasKey($id,$impact->getRetainedExceptions($this->clinic));
        }
    }

}
