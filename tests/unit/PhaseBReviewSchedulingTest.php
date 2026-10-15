<?php

declare(strict_types=1);
namespace Tests\Unit;

use App\Libraries\ApiRuntimeContext;
use App\Services\AppointmentSchedulingService;
use App\Services\ProviderDirectoryService;
use App\Services\ProviderScheduleService;
use Tests\Support\PhaseBFixture;

/** Independent behavioral acceptance of AGY scheduling, using synthetic data only. */
final class PhaseBReviewSchedulingTest extends PhaseBFixture
{
    public function testDirectoryHasFourDistinctIdsAndRejectsInactiveOrForeignMembershipRole(): void
    {
        $directory=new ProviderDirectoryService($this->db);
        $ids=array_column($directory->listForClinic($this->clinic),'user_id');
        $this->assertCount(4,$ids);
        $this->assertSame(4,count(array_unique($ids)));
        $this->db->table('users')->where('id',$this->providers[0])->update(['active'=>0]);
        $this->assertNull($directory->findBookable($this->clinic,$this->providers[0]));
        $this->db->table('roles')->where('id',$this->role)->update(['clinic_id'=>$this->otherClinic,'is_doctor'=>1]);
        $this->db->table('clinic_users')->where(['clinic_id'=>$this->clinic,'user_id'=>$this->providers[1]])->update(['role_id'=>$this->role]);
        $this->assertNull($directory->findBookable($this->clinic,$this->providers[1]));
    }

    public function testOverlapRejectedAdjacencyAllowedAndDifferentDoctorHasCapacity(): void
    {
        $service=new AppointmentSchedulingService($this->db);
        $one=$service->createForClinic($this->clinic,$this->payload(),$this->actor);
        $this->assertTrue($one['success'],json_encode($one));
        $overlap=$service->createForClinic($this->clinic,$this->payload(['appointment_time'=>'10:15']),$this->actor);
        $this->assertFalse($overlap['success'],json_encode($overlap));
        $adjacent=$service->createForClinic($this->clinic,$this->payload(['appointment_time'=>'10:30']),$this->actor);
        $this->assertTrue($adjacent['success'],json_encode($adjacent));
        $parallel=$service->createForClinic($this->clinic,$this->payload(['provider_user_id'=>$this->providers[1]]),$this->actor);
        $this->assertTrue($parallel['success'],json_encode($parallel));
        $this->assertSame(3,$this->db->table('appointments')->where('clinic_id',$this->clinic)->countAllResults());
    }

    public function testLegacyUnassignedAppointmentBlocksAllDoctorIdsWithoutRewritingSnapshot(): void
    {
        $legacy=$this->payload(); unset($legacy['provider_user_id']);
        $legacy['clinic_id']=$this->clinic; $legacy['chair_name']='Historical Doctor Label';
        $legacy['appointment_id']=$this->prefix.'_legacy'; $legacy['created_by']=$this->actor;
        $this->db->table('appointments')->insert($legacy); $id=(int)$this->db->insertID();
        $before=$this->db->table('appointments')->where('id',$id)->get()->getRowArray();
        $service=new AppointmentSchedulingService($this->db);
        foreach($this->providers as $provider) {
            $result=$service->createForClinic($this->clinic,$this->payload(['provider_user_id'=>$provider]),$this->actor);
            $this->assertFalse($result['success'],json_encode($result));
        }
        $this->assertSame($before,$this->db->table('appointments')->where('id',$id)->get()->getRowArray());
    }

    public function testLunchAndLeaveIntersectClinicHoursEvenWhenSessionPointsElsewhere(): void
    {
        $schedule=new ProviderScheduleService($this->db);
        $schedule->configureProfile($this->clinic,$this->providers[0],['is_bookable'=>true,'weekly_hours'=>['monday'=>[['start'=>'08:00','end'=>'12:00'],['start'=>'13:00','end'=>'19:00']]],'exceptions'=>['2026-11-16'=>[]]],$this->actor);
        session()->set('active_clinic_id',$this->otherClinic);
        $this->assertSame([['start'=>'09:00','end'=>'12:00'],['start'=>'13:00','end'=>'17:00']],$schedule->getEffectiveSchedule($this->clinic,$this->providers[0],'2026-11-09'));
        $this->assertSame([],$schedule->getEffectiveSchedule($this->clinic,$this->providers[0],'2026-11-16'));
    }

    public function testBearerCannotBorrowSessionActorOrClinicAndHasNoImpersonationBypass(): void
    {
        $service=new AppointmentSchedulingService($this->db);
        ApiRuntimeContext::setUserId($this->actor); ApiRuntimeContext::setClinicId($this->otherClinic);
        session()->set(['global_mode'=>false,'is_impersonating'=>true,'impersonated_clinic_id'=>$this->clinic]);
        $result=$service->createForClinic($this->clinic,$this->payload(),$this->actor,'bearer');
        $this->assertFalse($result['success'],json_encode($result));
        $this->assertSame(0,$this->db->table('appointments')->where('clinic_id',$this->clinic)->countAllResults());
        ApiRuntimeContext::setClinicId($this->clinic);
        $result=$service->createForClinic($this->clinic,$this->payload(),$this->actor,'bearer');
        $this->assertTrue($result['success'],json_encode($result));
    }

    public function testInvalidDurationDateAndProviderInputsNeverWrite(): void
    {
        $service=new AppointmentSchedulingService($this->db);
        foreach([['duration'=>'30junk'],['duration'=>0],['duration'=>481],['provider_user_id'=>[$this->providers[0]]],['provider_user_id'=>'1e2'],['provider_user_id'=>$this->actor],['appointment_date'=>'2026-02-30'],['appointment_time'=>'24:10'],['appointment_time'=>'23:45','duration'=>60]] as $extra) {
            $result=$service->createForClinic($this->clinic,$this->payload($extra),$this->actor);
            $this->assertFalse($result['success'],json_encode($extra).' '.json_encode($result));
        }
        $this->assertSame(0,$this->db->table('appointments')->where('clinic_id',$this->clinic)->countAllResults());
    }

    public function testRealWebCreateAndPublicBearerUseStableDoctorAndOwnContext(): void
    {
        $security=service('security'); $hash=$security->getHash(); $config=config('Security');
        $web=$this->withSession(session()->get())->withHeaders(['X-Requested-With'=>'XMLHttpRequest','Cookie'=>$config->cookieName.'='.$hash,'X-CSRF-TOKEN'=>$hash])->post('/appointment/store',$this->payload([$config->tokenName=>$hash]));
        $web->assertStatus(200);
        $this->assertSame(1,$this->db->table('appointments')->where(['clinic_id'=>$this->clinic,'provider_user_id'=>$this->providers[0]])->countAllResults());
        $token=(new \App\Models\ApiTokenModel())->generateToken($this->actor,'phase-b-synthetic');
        session()->set('active_clinic_id',$this->otherClinic);
        $api=$this->withSession(session()->get())->withHeaders(['Authorization'=>'Bearer '.$token,'X-Clinic-Id'=>(string)$this->clinic,'Content-Type'=>'application/json'])->withBody(json_encode($this->payload(['appointment_time'=>'11:00'])))->post('/api/v1/appointments');
        $api->assertStatus(201);
        $this->assertFalse($api->isRedirect());
        $this->assertSame(2,$this->db->table('appointments')->where('clinic_id',$this->clinic)->countAllResults());
        $this->assertSame(0,$this->db->table('appointments')->where('clinic_id',$this->otherClinic)->countAllResults());
        $this->assertNull(ApiRuntimeContext::getUserId());
    }
    public function testDstGapFoldAndCrossingIntervalsFailWhileOrdinaryLocalTimeWorks(): void
    {
        $this->setting('timezone','America/New_York');
        $this->setting('working_hours_start','00:00'); $this->setting('working_hours_end','23:59');
        $service=new AppointmentSchedulingService($this->db);
        foreach([['appointment_date'=>'2027-03-14','appointment_time'=>'02:30'],['appointment_date'=>'2027-11-07','appointment_time'=>'01:30'],['appointment_date'=>'2027-03-14','appointment_time'=>'01:45','duration'=>90],['appointment_date'=>'2027-11-07','appointment_time'=>'00:45','duration'=>90]] as $extra) {
            $result=$service->createForClinic($this->clinic,$this->payload($extra),$this->actor);
            $this->assertFalse($result['success'],json_encode($result));
        }
        $ordinary=$service->createForClinic($this->clinic,$this->payload(['appointment_date'=>'2027-11-08']),$this->actor);
        $this->assertTrue($ordinary['success'],json_encode($ordinary));
    }

    public function testRoutedEnglishArabicNotesOnlyPreserveInactiveProviderAndCalendarIncludesEntireDay(): void
    {
        $service=new AppointmentSchedulingService($this->db);
        $created=$service->createForClinic($this->clinic,$this->payload(),$this->actor);
        $this->assertTrue($created['success'],json_encode($created)); $id=(int)$created['id'];
        $this->db->table('users')->whereIn('id',$this->providers)->update(['active'=>0]);
        foreach(['en','ar'] as $locale) {
            $this->login($locale);
            $edit=$this->withSession(session()->get())->withHeaders([])->get('/appointment/'.$id.'/edit'); $edit->assertStatus(200);
            file_put_contents(ROOTPATH.'tmp/phase-b-rendered-edit-'.$locale.'.html',$edit->getBody());
            $hash=service('security')->getHash();$config=config('Security');
            $posted=$this->payload(['notes'=>'Updated '.$locale,$config->tokenName=>$hash]); unset($posted['provider_user_id']);
            $response=$this->withSession(session()->get())->withHeaders(['X-Requested-With'=>'XMLHttpRequest','Cookie'=>$config->cookieName.'='.$hash,'X-CSRF-TOKEN'=>$hash])->post('/appointment/'.$id.'/update',$posted);
            $response->assertStatus(200);
            $row=$this->db->table('appointments')->where('id',$id)->get()->getRowArray();
            $this->assertSame($this->providers[0],(int)$row['provider_user_id']); $this->assertSame('Updated '.$locale,$row['notes']);
        }
        for($i=0;$i<12;$i++) {
            $row=$this->payload(['notes'=>'Synthetic board row '.$i]); unset($row['provider_user_id']);
            $row+=['clinic_id'=>$this->clinic,'chair_name'=>'Unresolved '.$i,'appointment_id'=>'B'.bin2hex(random_bytes(6)).$i,'created_by'=>$this->actor];
            $this->db->table('appointments')->insert($row);
        }
        // Test actual routed calendar controller payload, including rows beyond list pagination.
        $permission=$this->db->table('permissions')->where(['module'=>'appointments','action'=>'calendar'])->get()->getRowArray();
        if($permission)$this->db->table('role_permissions')->insert(['role_id'=>$this->role,'permission_id'=>$permission['id'],'granted'=>1]);
        $this->login();
        $calendar=$this->withSession(session()->get())->withHeaders([])->get('/appointment/calendar?date=2026-11-09');
        $calendar->assertStatus(200);
        $html=$calendar->getBody();
        $this->assertStringContainsString('Unresolved 11',$html);
        $this->assertStringContainsString('data-chosen-day="2026-11-09"',$html);
    }

    public function testMedicalAssistantNeedsExplicitQualificationAndBookability(): void
    {
        $this->db->table('roles')->where('id',$this->role)->update(['is_medical'=>1,'is_doctor'=>0]);
        $directory=new ProviderDirectoryService($this->db);
        $this->assertNull($directory->findBookable($this->clinic,$this->actor));
        (new ProviderScheduleService($this->db))->configureProfile($this->clinic,$this->actor,['is_clinician'=>true,'is_bookable'=>true],$this->actor);
        $this->assertSame($this->actor,$directory->findBookable($this->clinic,$this->actor)['user_id']);
        $this->assertCount(5,$directory->getBookingContext($this->clinic)['providers']);
        (new ProviderScheduleService($this->db))->configureProfile($this->clinic,$this->actor,['is_clinician'=>true,'is_bookable'=>false],$this->actor);
        $this->assertNull($directory->findBookable($this->clinic,$this->actor));
        $this->assertCount(4,$directory->getBookingContext($this->clinic)['providers']);
    }

    public function testReconciliationReadsOnlyAndNeverInfersIdentityFromMatchingName(): void
    {
        $legacy=$this->payload();unset($legacy['provider_user_id']);
        $legacy+=['clinic_id'=>$this->clinic,'chair_name'=>'Duplicate Doctor','appointment_id'=>'R'.bin2hex(random_bytes(6)),'created_by'=>$this->actor];
        $this->db->table('appointments')->insert($legacy);
        $before=$this->db->table('appointments')->where('clinic_id',$this->clinic)->get()->getResultArray();
        $auditBefore=$this->db->table('activity_logs')->where('clinic_id',$this->clinic)->countAllResults();
        $report=(new \App\Services\LegacyChairReconciliationService($this->db))->generateReport($this->clinic,$this->actor);
        $this->assertSame(1,$report['total_legacy_appointments']);
        $this->assertSame([],$report['report_entries'][0]['potential_candidate_ids']);
        $this->assertSame('unresolved',$report['report_entries'][0]['ambiguity_status']);
        $this->assertSame($before,$this->db->table('appointments')->where('clinic_id',$this->clinic)->get()->getResultArray());
        $this->assertSame($auditBefore,$this->db->table('activity_logs')->where('clinic_id',$this->clinic)->countAllResults());
        try { (new \App\Services\LegacyChairReconciliationService($this->db))->generateReport($this->otherClinic,$this->actor);$this->fail('Foreign clinic report must fail'); }
        catch(\DomainException $e){$this->assertStringContainsString('Unauthorized',$e->getMessage());}
    }

    public function testValidLongDoctorNameFitsHistoricalSnapshotWithoutLosingDirectoryName(): void
    {
        $this->db->table('users')->where('id',$this->providers[0])->update(['first_name'=>str_repeat('A',50),'last_name'=>str_repeat('B',50)]);
        $provider=(new ProviderDirectoryService($this->db))->findBookable($this->clinic,$this->providers[0]);
        $this->assertSame(101,mb_strlen($provider['name']));
        $result=(new AppointmentSchedulingService($this->db))->createForClinic($this->clinic,$this->payload(),$this->actor);
        $this->assertTrue($result['success'],json_encode($result));
        $this->assertSame(100,mb_strlen($result['appointment']['chair_name']));
        $this->assertSame($this->providers[0],(int)$result['appointment']['provider_user_id']);
    }

    public function testFullPageFallbackPrefillsExplicitDoctorIdAndDay(): void
    {
        foreach(['en','ar'] as $locale) {
            $this->login($locale);
            $response=$this->withSession(session()->get())->withHeaders([])->get('/appointment/create?provider_user_id='.$this->providers[3].'&appointment_date=2026-11-10');
            $response->assertStatus(200); $doc=new \DOMDocument();@$doc->loadHTML($response->getBody());$xpath=new \DOMXPath($doc);
            $option=$xpath->query('//select[@name="provider_user_id"]/option[@selected]');
            $this->assertSame(1,$option->length);$this->assertSame((string)$this->providers[3],$option->item(0)->getAttribute('value'));
            $date=$xpath->query('//input[@name="appointment_date"]')->item(0)->getAttribute('value');
            $this->assertSame('2026-11-10',$date);
        }
    }

}
