<?php

declare(strict_types=1);
namespace Tests\Unit;

use App\Models\ControlPlaneAuditModel;
use App\Services\AppointmentSchedulingService;
use App\Services\ClinicSchedulingConfigurationService;
use App\Services\ProviderScheduleService;
use App\Services\ScheduleImpactAuditStore;
use App\Services\ScheduleImpactService;
use Tests\Support\PhaseBFixture;

/** Independent fixtures; no shared audit state or global fault switches. */
final class ScheduleImpactWorkflowTest extends PhaseBFixture
{
    protected function tearDown(): void
    {
        while($this->db->transDepth>0)$this->db->transRollback();
        $this->db->table('control_plane_audits')->whereIn('actor_user_id',$this->users)->delete();
        parent::tearDown();
    }

    private function book(array $extra=[]): int
    {
        $result=(new AppointmentSchedulingService($this->db))->createForClinic($this->clinic,$this->payload($extra),$this->actor);
        $this->assertTrue($result['success'],json_encode($result)); return (int)$result['id'];
    }

    private function hours(array $extra=[]): array
    {
        return (new ScheduleImpactService($this->db))->validateProposedHoursDto(array_replace([
            'working_hours_start'=>'09:00','working_hours_end'=>'16:00','appointment_duration'=>'30',
            'working_days'=>['monday','tuesday','wednesday','thursday','friday','saturday','sunday'],
            'dynamic_doctor_scheduling_enabled'=>'1'],$extra),$this->clinic);
    }

    private function preview(array $dto,?ScheduleImpactService $impact=null): array
    {
        $impact ??= new ScheduleImpactService($this->db);
        $report=$impact->generateReport($this->db,$this->clinic,'clinic_hours',$dto);
        $token=$impact->issueProof($this->clinic,$this->actor,'clinic_hours',$dto,$report);
        $dispositions=[];
        foreach($report['affected_appointment_ids'] as $id)$dispositions[$id]=['action'=>'retain','reason'=>'Director approved out-of-hours exception'];
        return ['token'=>$token,'acknowledged'=>true,'dispositions'=>$dispositions];
    }

    public function testDirectServiceCallsCompatibleWhenNoConflicts(): void
    {
        $result=(new ClinicSchedulingConfigurationService($this->db))->configureHours($this->clinic,$this->hours(['working_hours_end'=>'18:00']),$this->actor);
        $this->assertTrue($result['success'],json_encode($result));
        $profile=(new ProviderScheduleService($this->db))->configureProfile($this->clinic,$this->providers[0],
            ['is_bookable'=>true,'is_clinician'=>true,'weekly_hours'=>null,'exceptions'=>null],$this->actor);
        $this->assertTrue($profile['success']);
        $this->assertNotNull((new ScheduleImpactService($this->db))->getLatestAuditRecord($this->clinic));
    }

    public function testDirectServicesRejectAffectedHoursAndProfileWithoutProof(): void
    {
        $id=$this->book(['appointment_time'=>'16:30']);
        $before=$this->db->table('appointments')->where('id',$id)->get()->getRowArray();
        $result=(new ClinicSchedulingConfigurationService($this->db))->configureHours($this->clinic,$this->hours(),$this->actor);
        $this->assertFalse($result['success']);$this->assertSame(lang('DoctorScheduleSettings.missingProof'),$result['message']);
        try {
            (new ProviderScheduleService($this->db))->configureProfile($this->clinic,$this->providers[0],
                ['is_bookable'=>false,'is_clinician'=>true,'weekly_hours'=>null,'exceptions'=>null],$this->actor);
            $this->fail('Affected profile mutation must require preview proof.');
        } catch(\DomainException $e){$this->assertSame(lang('DoctorScheduleSettings.missingProof'),$e->getMessage());}
        $this->assertSame($before,$this->db->table('appointments')->where('id',$id)->get()->getRowArray());
        $this->assertSame(0,$this->db->transDepth);
    }

    public function testAuditedRetainPreservesAppointmentAndOnlyCompactPointerIsStored(): void
    {
        $id=$this->book(['appointment_time'=>'16:30']);
        $before=$this->db->table('appointments')->where('id',$id)->get()->getRowArray();
        $dto=$this->hours();$proof=$this->preview($dto);
        $result=(new ClinicSchedulingConfigurationService($this->db))->configureHours($this->clinic,$dto,$this->actor,$proof);
        $this->assertTrue($result['success'],json_encode($result));
        $this->assertSame($before,$this->db->table('appointments')->where('id',$id)->get()->getRowArray());
        $impact=new ScheduleImpactService($this->db);$audit=$impact->getLatestAuditRecord($this->clinic);
        $this->assertNotNull($audit);$this->assertSame($this->actor,$audit['actor_user_id']);
        $this->assertSame(1,$audit['affected_count']);$this->assertSame($proof['dispositions'],$audit['dispositions']);
        $retained=$impact->getRetainedExceptions($this->clinic);
        $this->assertSame($proof['dispositions'][$id]['reason'],$retained[$id]['reason']);
        $row=$this->db->table('settings')->where(['clinic_id'=>$this->clinic,'setting_key'=>'schedule_impact_latest_audit'])->get()->getRowArray();
        $pointer=json_decode($row['setting_value'],true,512,JSON_THROW_ON_ERROR);
        $this->assertCount(8,$pointer);$this->assertLessThan(1000,strlen($row['setting_value']));
        $this->assertArrayNotHasKey('affected_items',$pointer);
        $this->assertNull($this->db->table('settings')->where(['clinic_id'=>$this->clinic,'setting_key'=>'schedule_retained_exceptions'])->get()->getRowArray());
        $this->assertArrayNotHasKey($this->clinic.':clinic_hours:0',session('schedule_impact_proofs')??[]);
    }

    public function testInterveningBookingAndRoleScopeChangeInvalidateProof(): void
    {
        $id=$this->book(['appointment_time'=>'16:30']);$dto=$this->hours();$proof=$this->preview($dto);
        $this->book(['appointment_time'=>'11:00']);
        $result=(new ClinicSchedulingConfigurationService($this->db))->configureHours($this->clinic,$dto,$this->actor,$proof);
        $this->assertFalse($result['success']);$this->assertSame(lang('DoctorScheduleSettings.staleProof'),$result['message']);
        // Use an owned custom role: never mutate shared canonical doctor seed rows.
        $this->db->table('roles')->where('id',$this->role)->update(['is_doctor'=>1]);
        $this->db->table('clinic_users')->where(['clinic_id'=>$this->clinic,'user_id'=>$this->providers[0]])->update(['role_id'=>$this->role]);
        $impact=new ScheduleImpactService($this->db);$report=$impact->generateReport($this->db,$this->clinic,'clinic_hours',$dto);
        $token=$impact->issueProof($this->clinic,$this->actor,'clinic_hours',$dto,$report);
        // Alter a provider-only role's scope, without changing actor authorization.
        $this->db->table('roles')->insert(['name'=>$this->prefix.'doctor','slug'=>$this->prefix.'doctor','clinic_id'=>$this->clinic,'is_active'=>1,'is_doctor'=>1,'created_by'=>$this->actor]);
        $role=(int)$this->db->insertID();
        try {
            $this->db->table('clinic_users')->where(['clinic_id'=>$this->clinic,'user_id'=>$this->providers[0]])->update(['role_id'=>$role]);
            $report=$impact->generateReport($this->db,$this->clinic,'clinic_hours',$dto);
            $token=$impact->issueProof($this->clinic,$this->actor,'clinic_hours',$dto,$report);
            $this->db->table('roles')->where('id',$role)->update(['clinic_id'=>$this->otherClinic]);
            $fresh=$impact->generateReport($this->db,$this->clinic,'clinic_hours',$dto);
            $this->assertNotSame($report['state_digest'],$fresh['state_digest']);
            $this->assertSame('staleProof',$impact->validateProofAndDispositions($this->db,$this->clinic,$this->actor,'clinic_hours',$dto,null,$token,true,
                [$id=>['action'=>'retain','reason'=>'Reviewed exception']],$fresh));
        } finally {
            $this->db->table('clinic_users')->where(['clinic_id'=>$this->clinic,'user_id'=>$this->providers[0]])->update(['role_id'=>$this->role]);
            $this->db->table('roles')->where('id',$role)->delete();
        }
    }

    public function testFailedSecondRequiredAuditPartRollsBackConfigurationFirstPartAndPreservesProof(): void
    {
        $id=$this->book(['appointment_time'=>'16:30']);
        // A real large future horizon guarantees multiple audit parts without inflating any reason beyond its allowed size.
        for($i=1;$i<120;$i++) {
            $row=$this->payload(['appointment_date'=>(new \DateTimeImmutable('2026-11-09'))->modify('+'.$i.' days')->format('Y-m-d'),'appointment_time'=>'16:30']);
            $row+=['clinic_id'=>$this->clinic,'chair_name'=>'Synthetic snapshot','appointment_id'=>'F'.bin2hex(random_bytes(8)),'created_by'=>$this->actor];
            $this->db->table('appointments')->insert($row);
        }
        $appointments=$this->db->table('appointments')->where('clinic_id',$this->clinic)->orderBy('id')->get()->getResultArray();
        $beforeSettings=$this->db->table('settings')->where('clinic_id',$this->clinic)->orderBy('id')->get()->getResultArray();
        $beforeAudit=$this->db->table('control_plane_audits')->where('actor_user_id',$this->actor)->countAllResults();
        $model=new class($this->db) extends ControlPlaneAuditModel {
            public int $attempts=0; public array $savedParts=[]; public ?string $observedEnd=null;
            public function insert($row=null,bool $returnID=true) {
                $this->attempts++;
                $this->observedEnd=$this->db->table('settings')->where(['clinic_id'=>session('active_clinic_id'),'setting_key'=>'working_hours_end'])->get()->getRow('setting_value');
                if($this->attempts===2)throw new \RuntimeException('Synthetic second required part insert failed.');
                $id=parent::insert($row,$returnID);$this->savedParts[]=$id;return $id;
            }
        };
        $impact=new ScheduleImpactService($this->db,null,null,null,new ScheduleImpactAuditStore($this->db,$model));
        $dto=$this->hours();$proof=$this->preview($dto,$impact);
        $result=(new ClinicSchedulingConfigurationService($this->db,$impact))->configureHours($this->clinic,$dto,$this->actor,$proof);
        $this->assertFalse($result['success']);$this->assertSame(lang('DoctorScheduleSettings.saveFailed'),$result['message']);
        $this->assertSame(2,$model->attempts);$this->assertCount(1,$model->savedParts);
        $this->assertSame('16:00',$model->observedEnd,'Failure must occur after the proposed configuration was written in the same transaction.');
        $this->assertSame($beforeSettings,$this->db->table('settings')->where('clinic_id',$this->clinic)->orderBy('id')->get()->getResultArray());
        $this->assertSame($appointments,$this->db->table('appointments')->where('clinic_id',$this->clinic)->orderBy('id')->get()->getResultArray());
        $this->assertSame($beforeAudit,$this->db->table('control_plane_audits')->where('actor_user_id',$this->actor)->countAllResults());
        $this->assertSame(hash('sha256',$proof['token']),session('schedule_impact_proofs')[$this->clinic.':clinic_hours:0']['token_hash']);
        $this->assertSame(0,$this->db->transDepth);
    }

    private function persistLargeBundle(): array
    {
        $arabic="\u{0627}\u{0644}\u{0639}\u{0631}\u{0628}\u{064a}\u{0629}";
        $bundle=['target_type'=>'clinic_hours','affected_count'=>100,'affected_items'=>[],
            'dispositions'=>[],'before_configuration'=>$this->hours(['working_hours_end'=>'17:00']),'after_configuration'=>$this->hours()];
        for($i=1;$i<=100;$i++) {
            $id=90000+$i;$reason=str_repeat($arabic.' ',20).$id;
            $bundle['affected_items'][]=['appointment_id'=>$id,'provider_user_id'=>$this->providers[0],
                'before_outcome'=>['compliant'=>true,'reason'=>'Before ordinary clinic hours'],
                'after_outcome'=>['compliant'=>false,'reason'=>str_repeat($arabic.' ',35)]];
            $bundle['dispositions'][$id]=['action'=>'retain','reason'=>$reason];
        }
        $this->assertGreaterThan(65535,strlen(json_encode($bundle,JSON_THROW_ON_ERROR|JSON_UNESCAPED_UNICODE)));
        $this->assertTrue($this->db->transBegin());
        try {
            $pointer=(new ScheduleImpactAuditStore($this->db))->write($this->clinic,$this->actor,$bundle);
            $this->setting('schedule_impact_latest_audit',$pointer);
            $this->assertTrue($this->db->transCommit());
        } finally {if($this->db->transDepth>0)$this->db->transRollback();}
        return [$pointer,$bundle];
    }

    public function testLosslessArabicBundleOver64KiBHasByteBoundedPartsAndCompactPointer(): void
    {
        [$pointer,$expected]=$this->persistLargeBundle();
        $this->assertGreaterThan(1,$pointer['parts']);$this->assertCount(8,$pointer);
        $actual=(new ScheduleImpactService($this->db))->getLatestAuditRecord($this->clinic);
        $this->assertNotNull($actual);
        $this->assertSame($expected['affected_items'],$actual['affected_items']);
        $this->assertSame($expected['dispositions'],$actual['dispositions']);
        $parts=$this->db->table('control_plane_audits')->where('actor_user_id',$this->actor)->where('event_type','schedule_impact_bundle_part')->get()->getResultArray();
        $this->assertCount($pointer['parts'],$parts);
        foreach($parts as $row)$this->assertLessThanOrEqual(60000,strlen($row['metadata_json']));
    }

    public function testMissingAuditPartFromItsOwnBundleReturnsNoTrustedEvidence(): void
    {
        [$pointer]=$this->persistLargeBundle();
        $this->assertNotNull((new ScheduleImpactService($this->db))->getLatestAuditRecord($this->clinic));
        $row=$this->db->table('control_plane_audits')->where('actor_user_id',$this->actor)
            ->where('event_type','schedule_impact_bundle_part')->where('id >=',$pointer['first_id'])->where('id <=',$pointer['last_id'])->orderBy('id')->get()->getResultArray()[1];
        $metadata=json_decode($row['metadata_json'],true,512,JSON_THROW_ON_ERROR);$this->assertSame(1,$metadata['part_index']);
        $this->db->table('control_plane_audits')->where('id',$row['id'])->where('actor_user_id',$this->actor)->delete();
        $this->assertNull((new ScheduleImpactService($this->db))->getLatestAuditRecord($this->clinic));
        $this->assertNull((new ScheduleImpactAuditStore($this->db))->read($this->otherClinic,$pointer));
    }

    public function testNoImpactExtraneousDispositionsAreRejectedAndRetainedTupleExpiresAfterReschedule(): void
    {
        $dto=$this->hours(['working_hours_end'=>'18:00']);$impact=new ScheduleImpactService($this->db);
        $report=$impact->generateReport($this->db,$this->clinic,'clinic_hours',$dto);
        $token=$impact->issueProof($this->clinic,$this->actor,'clinic_hours',$dto,$report);
        $this->assertSame('foreignDisposition',$impact->validateProofAndDispositions($this->db,$this->clinic,$this->actor,'clinic_hours',$dto,null,$token,true,
            [99999=>['action'=>'retain','reason'=>'Forged unaffected visit']],$report));
        $id=$this->book(['appointment_time'=>'16:30']);$dto=$this->hours();$proof=$this->preview($dto);
        $result=(new ClinicSchedulingConfigurationService($this->db))->configureHours($this->clinic,$dto,$this->actor,$proof);
        $this->assertTrue($result['success'],json_encode($result));$this->assertArrayHasKey($id,$impact->getRetainedExceptions($this->clinic));
        $moved=(new AppointmentSchedulingService($this->db))->updateForClinic($this->clinic,$id,['appointment_time'=>'11:00'],$this->actor);
        $this->assertTrue($moved['success'],json_encode($moved));$this->assertArrayNotHasKey($id,$impact->getRetainedExceptions($this->clinic));
    }

    private function csrfPost(string $path,array $input)
    {
        $security=config('Security');$hash=service('security')->getHash();$input[$security->tokenName]=$hash;
        return $this->withSession(session()->get())->withHeaders(['Cookie'=>$security->cookieName.'='.$hash,
            'X-CSRF-TOKEN'=>$hash,'X-Requested-With'=>'XMLHttpRequest'])->post($path,$input);
    }

    public function testRealRoutedEnglishArabicPreviewApplyAndRecheckWithCsrfAndPermissionChecks(): void
    {
        $id=$this->book(['appointment_time'=>'16:30']);$dto=$this->hours();
        $this->login();$this->db->table('role_permissions')->where('role_id',$this->role)
            ->whereIn('permission_id',array_column($this->db->table('permissions')->where('module','settings')->get()->getResultArray(),'id'))->update(['granted'=>0]);
        $denied=$this->csrfPost('/settings/preview-schedule-impact',$dto+['target_type'=>'clinic_hours']);$denied->assertStatus(403);
        $this->db->table('role_permissions')->where('role_id',$this->role)->update(['granted'=>1]);
        foreach(['en','ar'] as $locale) {
            $this->login($locale);
            $preview=$this->csrfPost('/settings/preview-schedule-impact',$dto+['target_type'=>'clinic_hours']);$preview->assertStatus(200);
            $body=$preview->response()->getBody();$this->assertStringContainsString(lang('DoctorScheduleSettings.impactReport',[],$locale),$body);
            $this->assertStringContainsString('#'.$id,$body);$this->assertStringContainsString('dir="'.($locale==='ar'?'rtl':'ltr').'"',$body);
            $this->assertSame(1,preg_match('/name="proof_token" value="([a-f0-9]{64})"/',$body,$matches));
            if($locale==='ar') {
                $apply=$this->csrfPost('/settings/apply-schedule-impact',['target_type'=>'clinic_hours','proof_token'=>$matches[1],
                    'proposed_dto_json'=>json_encode($dto,JSON_THROW_ON_ERROR),'acknowledged'=>'1',
                    'dispositions_json'=>json_encode([$id=>['action'=>'retain','reason'=>'Confirmed operator exception']],JSON_THROW_ON_ERROR)]);
                $apply->assertStatus(302);$this->assertTrue(session()->has('success'));
                $recheck=$this->withSession(session()->get())->withHeaders([])->get('/settings/schedule-impact-recheck');$recheck->assertStatus(200);
                $this->assertStringContainsString(lang('DoctorScheduleSettings.recheckReport',[],'ar'),$recheck->response()->getBody());
            }
        }
    }
}
