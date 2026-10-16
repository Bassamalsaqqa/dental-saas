<?php

declare(strict_types=1);
namespace Tests\Unit;

use App\Services\AppointmentSchedulingService;
use App\Services\ClinicSchedulingConfigurationService;
use App\Services\ScheduleImpactService;
use Tests\Support\PhaseBFixture;

/** Independent adversarial service-boundary checks using only synthetic rows. */
final class ScheduleImpactIndependentReviewTest extends PhaseBFixture
{
    private function hours(array $extra=[]): array
    {
        return array_replace(['working_hours_start'=>'09:00','working_hours_end'=>'17:00',
            'appointment_duration'=>'30','working_days'=>['monday','tuesday','wednesday','thursday','friday','saturday','sunday'],
            'dynamic_doctor_scheduling_enabled'=>'1'],$extra);
    }
    private function book(): int
    {
        $r=(new AppointmentSchedulingService($this->db))->createForClinic($this->clinic,$this->payload(),$this->actor);
        $this->assertTrue($r['success'],json_encode($r));return (int)$r['id'];
    }
    public function testZeroImpactRejectsInjectedForeignDispositionAndPreservesConfiguration():void
    {
        $before=$this->db->table('settings')->where('clinic_id',$this->clinic)->orderBy('id')->get()->getResultArray();
        $result=(new ClinicSchedulingConfigurationService($this->db))->configureHours($this->clinic,$this->hours(),$this->actor,
            ['token'=>str_repeat('a',64),'acknowledged'=>true,'dispositions'=>[999999=>['action'=>'retain','reason'=>'Forged foreign resolution']]]);
        $this->assertFalse($result['success'],json_encode($result));
        $this->assertSame($before,$this->db->table('settings')->where('clinic_id',$this->clinic)->orderBy('id')->get()->getResultArray());
    }
    public function testReportCanRescheduleOwnedFutureVisitAndOmitsPatientData():void
    {
        $id=$this->book();$impact=new ScheduleImpactService($this->db);
        $report=$impact->generateReport($this->db,$this->clinic,'clinic_hours',$this->hours(['working_hours_end'=>'10:15']));
        $item=array_values(array_filter($report['items'],static fn($row)=>(int)$row['appointment_id']===$id))[0];
        $this->assertTrue($item['reschedulable']);$this->assertNotEmpty($item['edit_url']);
        $this->assertFalse($item['after_outcome']['compliant']);$this->assertTrue($item['before_outcome']['compliant']);
        $this->assertArrayNotHasKey('patient_id',$item);$this->assertArrayNotHasKey('notes',$item);
    }
    public function testRoleClinicChangeInSameSecondInvalidatesPreview():void
    {
        $id=$this->book();$impact=new ScheduleImpactService($this->db);$dto=$this->hours(['working_hours_end'=>'10:15']);
        $report=$impact->generateReport($this->db,$this->clinic,'clinic_hours',$dto);
        $proof=$impact->issueProof($this->clinic,$this->actor,'clinic_hours',$dto,$report);
        // Separate role affects only this synthetic doctor; no shared canonical role mutation.
        $this->db->table('roles')->insert(['name'=>$this->prefix.'_doctor','slug'=>$this->prefix.'_doctor','clinic_id'=>$this->clinic,'is_active'=>1,'is_doctor'=>1,'is_system'=>0,'created_by'=>$this->actor]);
        $roleId=(int)$this->db->insertID();
        try{
            $this->db->table('clinic_users')->where(['clinic_id'=>$this->clinic,'user_id'=>$this->providers[0]])->update(['role_id'=>$roleId]);
            $report=$impact->generateReport($this->db,$this->clinic,'clinic_hours',$dto);
            $proof=$impact->issueProof($this->clinic,$this->actor,'clinic_hours',$dto,$report);
            $this->db->table('roles')->where('id',$roleId)->update(['clinic_id'=>$this->otherClinic]);
            $result=(new ClinicSchedulingConfigurationService($this->db))->configureHours($this->clinic,$dto,$this->actor,
                ['token'=>$proof,'acknowledged'=>true,'dispositions'=>[$id=>['action'=>'retain','reason'=>'Reviewed exception']]]);
            $this->assertFalse($result['success'],json_encode($result));
            $this->assertSame('17:00',$this->db->table('settings')->where(['clinic_id'=>$this->clinic,'setting_key'=>'working_hours_end'])->get()->getRow('setting_value'));
        } finally {
            $doctor=$this->db->table('roles')->where('slug','doctor')->get()->getRow('id');
            $this->db->table('clinic_users')->where(['clinic_id'=>$this->clinic,'user_id'=>$this->providers[0]])->update(['role_id'=>$doctor]);
            $this->db->table('roles')->where('id',$roleId)->delete();
        }
    }
    public function testRealEnglishAndArabicPreviewApplyRecheckPreservesVisit(): void
    {
        $id=$this->book();$before=$this->db->table('appointments')->where('id',$id)->get()->getRowArray();
        foreach(['en','ar'] as $locale){
            $this->setting('working_hours_end','17:00');$this->login($locale);
            $preview=$this->postImpact('/settings/preview-schedule-impact',$this->hours(['working_hours_end'=>'10:15'])+['target_type'=>'clinic_hours']);
            $this->assertSame(200,$preview->response()->getStatusCode(),json_encode(['location'=>$preview->response()->getHeaderLine('Location'),'error'=>session()->getFlashdata('error')]));
            $doc=new \DOMDocument();@$doc->loadHTML('<?xml encoding="UTF-8">'.$preview->response()->getBody());$xpath=new \DOMXPath($doc);
            $proof=$xpath->query('//input[@name="proof_token"]')->item(0)?->getAttribute('value');
            $json=$xpath->query('//input[@name="proposed_dto_json"]')->item(0)?->getAttribute('value');
            $this->assertIsString($proof);$this->assertIsString($json);
            $apply=$this->postImpact('/settings/apply-schedule-impact',['target_type'=>'clinic_hours','proof_token'=>$proof,'proposed_dto_json'=>$json,
                'acknowledged'=>'1','dispositions_json'=>json_encode([$id=>['action'=>'retain','reason'=>'Reviewed ??????? <script>literal()</script>']],JSON_THROW_ON_ERROR)]);
            $apply->assertRedirectTo(base_url('settings/schedule-impact-recheck'));
            $this->assertSame('10:15',$this->db->table('settings')->where(['clinic_id'=>$this->clinic,'setting_key'=>'working_hours_end'])->get()->getRow('setting_value'));
            $this->assertSame($before,$this->db->table('appointments')->where('id',$id)->get()->getRowArray());
            $health=$this->withSession(session()->get())->withHeaders([])->get('/settings/schedule-impact-recheck');$health->assertStatus(200);
            $this->assertStringContainsString('Reviewed ??????? &lt;script&gt;literal()&lt;/script&gt;',$health->response()->getBody());
            $replay=$this->postImpact('/settings/apply-schedule-impact',['target_type'=>'clinic_hours','proof_token'=>$proof,'proposed_dto_json'=>$json,'acknowledged'=>'1',
                'dispositions_json'=>json_encode([$id=>['action'=>'retain','reason'=>'Replayed exception']])]);
            $replay->assertRedirectTo(base_url('settings?tab=hours'));
        }
    }
    public function testRoutedProfilePreviewApplyAndMalformedInputAreClinicScoped():void
    {
        $id=$this->book();$before=$this->db->table('appointments')->where('id',$id)->get()->getRowArray();
        foreach(['en','ar'] as $locale){
            $this->login($locale);
            $preview=$this->postImpact('/settings/preview-schedule-impact',['target_type'=>'provider_profile','provider_user_id'=>(string)$this->providers[0],
                'is_bookable'=>'0','is_clinician'=>'0']);
            $this->assertSame(200,$preview->response()->getStatusCode(),(string)session()->getFlashdata('error'));
            $doc=new \DOMDocument();@$doc->loadHTML($preview->response()->getBody());$xp=new \DOMXPath($doc);
            $proof=$xp->query('//input[@name="proof_token"]')->item(0)->getAttribute('value');
            $json=$xp->query('//input[@name="proposed_dto_json"]')->item(0)->getAttribute('value');
            $input=['target_type'=>'provider_profile','target_id'=>(string)$this->providers[0],'proof_token'=>$proof,'proposed_dto_json'=>$json,
                'acknowledged'=>'1','dispositions_json'=>json_encode([$id=>['action'=>'retain','reason'=>'Clinic confirmed retained visit']])];
            $bad=$this->postImpact('/settings/apply-schedule-impact',array_replace($input,['acknowledged'=>'false']));
            $bad->assertRedirectTo(base_url('settings?tab=hours'));
            $applied=$this->postImpact('/settings/apply-schedule-impact',$input);$applied->assertRedirectTo(base_url('settings/schedule-impact-recheck'));
            $this->assertSame($before,$this->db->table('appointments')->where('id',$id)->get()->getRowArray());
            $this->db->table('clinic_provider_profiles')->where(['clinic_id'=>$this->clinic,'user_id'=>$this->providers[0]])->update(['is_bookable'=>1]);
        }
        foreach([['target_type'=>['clinic_hours']],['target_type'=>'provider_profile','provider_user_id'=>'99999999'],
            ['target_type'=>'provider_profile','provider_user_id'=>[$this->providers[0]]]] as $input){
            $result=$this->postImpact('/settings/preview-schedule-impact',$input);$result->assertRedirectTo(base_url('settings?tab=hours'));
            $this->assertStringNotContainsString('99999999',$result->response()->getBody());
        }
    }
    public function testValidReplacementRepairsCorruptProfileWithoutFalseAfterConflict():void
    {
        $id=$this->book();$this->db->table('clinic_provider_profiles')->insert(['clinic_id'=>$this->clinic,'user_id'=>$this->providers[0],
            'is_bookable'=>1,'is_clinician'=>0,'weekly_hours_json'=>'{"monday":"invalid"}','exceptions_json'=>null]);
        $impact=new ScheduleImpactService($this->db);$dto=$impact->validateProposedProfileDto(['is_bookable'=>true,'is_clinician'=>false,'weekly_hours'=>null,'exceptions'=>null]);
        $report=$impact->generateReport($this->db,$this->clinic,'provider_profile',$dto,$this->providers[0]);
        $this->assertFalse($report['items'][0]['before_outcome']['compliant']);
        $this->assertTrue($report['items'][0]['after_outcome']['compliant']);$this->assertSame([],$report['affected_appointment_ids']);
    }
    private function postImpact(string $path,array $data)
    {
        $security=service('security');$config=config('Security');$hash=$security->getHash();
        return $this->withSession(session()->get())->withHeaders(['Cookie'=>$config->cookieName.'='.$hash,'X-CSRF-TOKEN'=>$hash])
            ->call('post',$path,$data+[$config->tokenName=>$hash]);
    }
    protected function tearDown():void
    {
        if(isset($this->actor))$this->db->table('control_plane_audits')->where('actor_user_id',$this->actor)->delete();
        parent::tearDown();
    }

}
