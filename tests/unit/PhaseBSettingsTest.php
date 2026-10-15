<?php

declare(strict_types=1);
namespace Tests\Unit;

use App\Controllers\Settings;
use App\Libraries\ApiRuntimeContext;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;
use Config\Database;

final class PhaseBSettingsTest extends CIUnitTestCase
{
    use FeatureTestTrait;
    protected $db;
    private string $prefix;
    private int $actor;
    private int $clinic;
    private int $foreignClinic;
    private int $role;
    private int $patient;
    private int $plan;

    protected function setUp(): void
    {
        parent::setUp();
        ApiRuntimeContext::clear();
        $this->db = Database::connect();
        $this->assertSame(0, $this->db->transDepth);
        $this->assertTrue($this->db->tableExists('clinic_provider_profiles'));
        $this->prefix = 'phaseb-settings-' . bin2hex(random_bytes(6));
        $this->db->table('users')->insert(['ip_address'=>'127.0.0.1','username'=>$this->prefix,
            'email'=>$this->prefix.'@example.test','password'=>password_hash('Synthetic-only-42',PASSWORD_BCRYPT),
            'first_name'=>'Synthetic','last_name'=>'Settings','active'=>1,'created_on'=>time()]);
        $this->actor = (int) $this->db->insertID();
        foreach (['clinic','foreignClinic'] as $name) {
            $this->db->table('clinics')->insert(['name'=>$this->prefix.'-'.$name,'slug'=>$this->prefix.'-'.$name,
                'uuid'=>bin2hex(random_bytes(16)),'status'=>'active']);
            $this->$name = (int) $this->db->insertID();
        }
        $this->db->table('roles')->insert(['name'=>$this->prefix,'slug'=>$this->prefix,'clinic_id'=>$this->clinic,
            'created_by'=>$this->actor,'is_active'=>1,'is_system'=>0]);
        $this->role = (int) $this->db->insertID();
        $permission = $this->db->table('permissions')->where('module','settings')->where('action','edit')->get()->getRowArray();
        $this->assertNotEmpty($permission);
        $this->db->table('role_permissions')->insert(['role_id'=>$this->role,'permission_id'=>$permission['id'],'granted'=>1]);
        $this->db->table('clinic_users')->insert(['clinic_id'=>$this->clinic,'user_id'=>$this->actor,'role_id'=>$this->role,'status'=>'active']);
        $this->db->table('patients')->insert(['clinic_id'=>$this->clinic,'patient_id'=>$this->prefix,'first_name'=>'Synthetic','last_name'=>'Patient','phone'=>'0599999999','status'=>'active']);
        $this->patient = (int) $this->db->insertID();
        $this->db->table('plans')->insert(['name'=>$this->prefix,'status'=>'active','limits_json'=>'{}','features_json'=>'{}']);
        $this->plan = (int) $this->db->insertID();
        $this->db->table('clinic_subscriptions')->insert(['clinic_id'=>$this->clinic,'plan_id'=>$this->plan,'status'=>'active','start_at'=>gmdate('Y-m-d H:i:s',time()-3600),'end_at'=>null]);
        $this->login();
    }

    protected function tearDown(): void
    {
        ApiRuntimeContext::clear();
        while ($this->db && $this->db->transDepth > 0) $this->db->transRollback();
        $this->db->resetTransStatus();
        if (isset($this->clinic, $this->foreignClinic)) {
            foreach (['appointments','clinic_provider_profiles','settings','plan_audits','clinic_subscriptions','patients','clinic_users'] as $table) {
                $this->db->table($table)->whereIn('clinic_id',[$this->clinic,$this->foreignClinic])->delete();
            }
        }
        if (isset($this->actor)) {
            $this->db->table('control_plane_audits')->where('actor_user_id',$this->actor)->delete();
            $this->db->table('role_permissions')->where('role_id',$this->role)->delete();
            $this->db->table('roles')->where('id',$this->role)->delete();
            $this->db->table('users')->where('id',$this->actor)->delete();
        }
        if (isset($this->clinic, $this->foreignClinic)) $this->db->table('clinics')->whereIn('id',[$this->clinic,$this->foreignClinic])->delete();
        if (isset($this->plan)) $this->db->table('plans')->where('id',$this->plan)->delete();
        session()->remove(['identity','email','user_id','last_check','global_mode','active_clinic_id','active_clinic_role_id','is_impersonating','impersonated_clinic_id','ui_locale']);
        parent::tearDown();
    }

    public function testLegacyDefaultRemainsOffAndHoursAreTenantScoped(): void
    {
        $this->setting($this->foreignClinic,'working_hours_start','07:00');
        $result = $this->save($this->hours());
        $this->assertTrue($result['success']);
        $this->assertSame('0',$this->value('dynamic_doctor_scheduling_enabled'));
        $this->assertSame('09:00',$this->value('working_hours_start'));
        $this->assertSame('07:00',$this->db->table('settings')->where('clinic_id',$this->foreignClinic)->where('setting_key','working_hours_start')->get()->getRowArray()['setting_value']);
        $this->assertSame(0,$this->db->transDepth);
        $this->assertSame(1,$this->db->table('control_plane_audits')->where('actor_user_id',$this->actor)->countAllResults());
    }

    public function testDynamicModeIgnoresLegacyCapacityAndRefusesUnsafeDisable(): void
    {
        $this->setting($this->clinic,'booking_chair_count','2');
        $this->setting($this->clinic,'booking_chair_names','["Historical A","Historical B"]','json');
        $input = $this->hours();
        unset($input['booking_chair_count'],$input['booking_chair_names']);
        $input['dynamic_doctor_scheduling_enabled']='1';
        $this->assertTrue($this->save($input)['success']);
        $this->assertSame('2',$this->value('booking_chair_count'));
        $this->assertSame('["Historical A","Historical B"]',$this->value('booking_chair_names'));
        $this->db->table('appointments')->insert(['clinic_id'=>$this->clinic,'patient_id'=>$this->patient,'provider_user_id'=>$this->actor,
            'appointment_id'=>$this->prefix,'appointment_date'=>'2030-01-07','appointment_time'=>'10:00:00','duration'=>30,
            'appointment_type'=>'consultation','chair_name'=>'Historical label','status'=>'scheduled','created_by'=>$this->actor]);
        $before=$this->db->table('appointments')->where('clinic_id',$this->clinic)->get()->getResultArray();
        $this->assertFalse($this->save(array_replace($this->hours(),['dynamic_doctor_scheduling_enabled'=>'0']))['success']);
        $this->assertSame('1',$this->value('dynamic_doctor_scheduling_enabled'));
        $this->assertSame($before,$this->db->table('appointments')->where('clinic_id',$this->clinic)->get()->getResultArray());
    }

    public function testInvalidHoursAndForgedContextNeverPersist(): void
    {
        foreach ([['working_hours_start'=>'18:00'],['working_days'=>['not-a-day']],['booking_chair_names'=>['Duplicate','Duplicate'],'booking_chair_count'=>'2']] as $change) {
            $this->assertFalse($this->save(array_replace($this->hours(),$change))['success']);
        }
        $this->assertSame(0,$this->db->table('settings')->where('clinic_id',$this->clinic)->countAllResults());
        session()->set('active_clinic_id',$this->foreignClinic);
        $this->assertFalse($this->save($this->hours())['success']);
        $this->login();session()->set('global_mode',true);
        $this->assertFalse($this->save($this->hours())['success']);
        $this->login();ApiRuntimeContext::setUserId($this->actor);ApiRuntimeContext::setClinicId($this->clinic);
        $this->assertFalse($this->save($this->hours())['success']);
    }

    public function testNestedHoursTransactionDoesNotOwnOrCommitCaller(): void
    {
        $this->assertTrue($this->db->transBegin());
        $this->assertFalse($this->save($this->hours())['success']);
        $this->assertSame(1,$this->db->transDepth);
        $this->db->transRollback();
        $this->assertNull($this->value('working_hours_start'));
    }

    public function testStructuredLunchAndLeaveInputsPreserveClearMeaning(): void
    {
        $method=new \ReflectionMethod(Settings::class,'providerScheduleInput');
        $dto=$method->invoke(new Settings(),['is_bookable'=>'1','is_clinician'=>'1','weekly_override'=>'1',
            'weekly'=>['monday'=>[['start'=>'09:00','end'=>'12:00'],['start'=>'13:00','end'=>'17:00']]],
            'exceptions'=>[['date'=>'2030-01-08','leave'=>'1'],['date'=>'','windows'=>[]]]]);
        $this->assertTrue($dto['is_bookable']);$this->assertTrue($dto['is_clinician']);
        $this->assertSame([['start'=>'09:00','end'=>'12:00'],['start'=>'13:00','end'=>'17:00']],$dto['weekly_hours']['monday']);
        $this->assertSame([],$dto['weekly_hours']['tuesday']);
        $this->assertSame(['2030-01-08'=>[]],$dto['exceptions']);
        $this->expectException(\DomainException::class);
        $method->invoke(new Settings(),['weekly_override'=>'1','weekly'=>['monday'=>[['start'=>'09:00','end'=>'13:00'],['start'=>'12:00','end'=>'17:00']]]]);
    }

    public function testRealHoursRouteKeepsArabicSuccessAndEnforcesPermission(): void
    {
        session()->set('ui_locale','ar');
        $this->post('/settings/updateWorkingHours',$this->hours())->assertStatus(302);
        $this->assertSame(lang('DoctorScheduleSettings.saved',[],'ar'),session()->getFlashdata('success'));
        $this->db->table('role_permissions')->where('role_id',$this->role)->delete();
        $before=$this->db->table('settings')->where('clinic_id',$this->clinic)->get()->getResultArray();
        $result=$this->post('/settings/updateWorkingHours',array_replace($this->hours(),['working_hours_start'=>'08:00']));
        $result->assertStatus(302);
        $this->assertSame($before,$this->db->table('settings')->where('clinic_id',$this->clinic)->get()->getResultArray());
    }

    public function testSettingsCandidatesAreCanonicalAndBilingualFormsUseStructuredWindows(): void
    {
        $method=new \ReflectionMethod(Settings::class,'providerSettingsContext');
        $context=$method->invoke(new Settings(),$this->clinic);
        $this->assertSame([$this->actor],array_map('intval',array_column($context['members'],'user_id')));
        $this->db->table('roles')->where('id',$this->role)->update(['clinic_id'=>$this->foreignClinic]);
        $this->assertSame([],$method->invoke(new Settings(),$this->clinic)['members']);
        $members=[];
        for($id=1;$id<=4;$id++) $members[]=['user_id'=>$id,'name'=>'Same name','role_slug'=>'custom','profile'=>[
            'weekly_hours_json'=>'{"monday":[{"start":"09:00","end":"12:00"},{"start":"13:00","end":"17:00"}]}',
            'exceptions_json'=>'{"2030-01-08":[]}','is_clinician'=>1,'is_bookable'=>1]];
        foreach(['en','ar'] as $locale){
            service('language')->setLocale($locale);
            $html=view('settings/partials/provider_schedules',['provider_settings'=>['schema_ready'=>true,'members'=>$members]]);
            $this->assertStringContainsString(lang('DoctorScheduleSettings.providers',[],$locale),$html);
            $this->assertStringContainsString('weekly[monday][1][start]',$html);
            $this->assertStringContainsString('exceptions[0][leave]',$html);
            $this->assertStringNotContainsString('<textarea',$html);
            for($id=1;$id<=4;$id++)$this->assertStringContainsString('data-provider-settings-id="'.$id.'"',$html);
        }
        service('language')->setLocale('en');
    }

    public function testProfileWritesRequireExactSessionAuthorityAndCanonicalTarget(): void
    {
        $service=new \App\Services\ProviderScheduleService($this->db);
        $dto=['is_bookable'=>true,'is_clinician'=>true,'weekly_hours'=>null,'exceptions'=>[]];
        $result=$service->configureProfile($this->clinic,$this->actor,$dto,$this->actor);
        $this->assertTrue($result['success']);
        $this->assertTrue($result['profile']['is_clinician']);
        $before=$this->db->table('clinic_provider_profiles')->where('clinic_id',$this->clinic)->get()->getResultArray();
        foreach(['forged','foreign','impersonation','bearer','inactive'] as $kind){
            $this->login();ApiRuntimeContext::clear();
            $actor=$this->actor;
            if($kind==='forged')$actor++;
            if($kind==='foreign')session()->set('active_clinic_id',$this->foreignClinic);
            if($kind==='impersonation')session()->set(['is_impersonating'=>true,'impersonated_clinic_id'=>$this->clinic]);
            if($kind==='bearer'){ApiRuntimeContext::setUserId($this->actor);ApiRuntimeContext::setClinicId($this->clinic);}
            if($kind==='inactive')$this->db->table('roles')->where('id',$this->role)->update(['is_active'=>0]);
            try{$service->configureProfile($this->clinic,$this->actor,$dto,$actor);$this->fail('Profile mutation must deny '.$kind);}
            catch(\DomainException $e){$this->assertNotEmpty($e->getMessage());}
            $this->assertSame($before,$this->db->table('clinic_provider_profiles')->where('clinic_id',$this->clinic)->get()->getResultArray());
            $this->assertSame(0,$this->db->transDepth);
        }
        ApiRuntimeContext::clear();
    }

    public function testProfileWindowsIntersectExplicitClinicHoursAndDatesFailClosed(): void
    {
        $this->save($this->hours());
        $service=new \App\Services\ProviderScheduleService($this->db);
        $service->configureProfile($this->clinic,$this->actor,['is_clinician'=>true,'weekly_hours'=>['monday'=>[
            ['start'=>'08:00','end'=>'12:00'],['start'=>'13:00','end'=>'19:00']]],'exceptions'=>['2030-01-14'=>[]]],$this->actor);
        session()->set('active_clinic_id',$this->foreignClinic);
        $this->assertSame([['start'=>'09:00','end'=>'12:00'],['start'=>'13:00','end'=>'17:00']],$service->getEffectiveSchedule($this->clinic,$this->actor,'2030-01-07'));
        $this->assertSame([],$service->getEffectiveSchedule($this->clinic,$this->actor,'2030-01-08'));
        $this->assertSame([],$service->getEffectiveSchedule($this->clinic,$this->actor,'2030-01-14'));
        $this->assertSame([],$service->getEffectiveSchedule($this->clinic,$this->actor,'2030-02-30'));
        $this->login();
        foreach([['weekly_hours'=>['monday'=>[['start'=>'25:00','end'=>'26:00']]]],['exceptions'=>['2030-02-30'=>[]]],['is_clinician'=>['1']]] as $dto){
            try{$service->configureProfile($this->clinic,$this->actor,$dto,$this->actor);$this->fail('Invalid profile must fail');}
            catch(\DomainException $e){$this->assertSame(lang('DoctorScheduleSettings.invalidInput'),$e->getMessage());}
        }
        $this->db->transBegin();
        try{$service->configureProfile($this->clinic,$this->actor,[],$this->actor);$this->fail('Nested profile transaction must fail');}
        catch(\DomainException $e){$this->assertSame(1,$this->db->transDepth);}
        $this->db->transRollback();
    }

    public function testTimezoneCannotReinterpretExistingVisitsButOtherSystemPreferencesStillSave(): void
    {
        $service=new \App\Services\ClinicSchedulingConfigurationService($this->db);
        $dto=['timezone'=>'Asia/Jerusalem','date_format'=>'Y-m-d','time_format'=>'24','ui_language'=>'en','currency'=>'ILS','currency_position'=>'before','finance_auto_post_inventory_cogs'=>0];
        $this->assertTrue($service->configureSystem($this->clinic,$dto,$this->actor)['success']);
        $this->db->table('appointments')->insert(['clinic_id'=>$this->clinic,'patient_id'=>$this->patient,'appointment_id'=>$this->prefix,
            'appointment_date'=>'2030-01-07','appointment_time'=>'10:00:00','duration'=>30,'appointment_type'=>'consultation','chair_name'=>'Legacy','status'=>'scheduled','created_by'=>$this->actor]);
        $this->assertFalse($service->configureSystem($this->clinic,array_replace($dto,['timezone'=>'UTC','ui_language'=>'ar']),$this->actor)['success']);
        $this->assertSame('Asia/Jerusalem',$this->value('timezone'));
        $this->assertSame('en',$this->value('ui_language'));
        $this->assertTrue($service->configureSystem($this->clinic,array_replace($dto,['ui_language'=>'ar']),$this->actor)['success']);
        $this->assertSame('ar',$this->value('ui_language'));
    }

    public function testMalformedStoredProfileCannotFallBackToOpenClinicHours(): void
    {
        $this->save($this->hours());
        $service=new \App\Services\ProviderScheduleService($this->db);
        $service->configureProfile($this->clinic,$this->actor,['is_clinician'=>true],$this->actor);
        foreach(['{broken','null','"not-windows"','{"monday":[{"start":"25:00","end":"26:00"}]}'] as $json){
            $this->db->table('clinic_provider_profiles')->where(['clinic_id'=>$this->clinic,'user_id'=>$this->actor])->update(['weekly_hours_json'=>$json]);
            try{$service->getEffectiveSchedule($this->clinic,$this->actor,'2030-01-07');$this->fail('Corrupt schedule must not inherit open hours');}
            catch(\DomainException $e){$this->assertSame(lang('DoctorScheduleSettings.invalidInput'),$e->getMessage());}
        }
    }

    private function post(string $path,array $data)
    {
        $security=service('security');$config=config('Security');$hash=$security->getHash();
        return $this->withSession(session()->get())->withHeaders(['Cookie'=>$config->cookieName.'='.$hash,'X-CSRF-TOKEN'=>$hash])
            ->call('post',$path,array_merge($data,[$config->tokenName=>$hash]));
    }
    private function save(array $input):array {return (new \ReflectionMethod(Settings::class,'persistSchedulingSettings'))->invoke(new Settings(),$input);}
    private function value(string $key):?string {return $this->db->table('settings')->where('clinic_id',$this->clinic)->where('setting_key',$key)->get()->getRowArray()['setting_value']??null;}
    private function setting(int $clinic,string $key,string $value,string $type='string'):void {$this->db->table('settings')->insert(['clinic_id'=>$clinic,'setting_key'=>$key,'setting_value'=>$value,'setting_type'=>$type]);}
    private function hours():array {return ['working_hours_start'=>'09:00','working_hours_end'=>'17:00','working_days'=>['monday','tuesday','wednesday','thursday','friday'],'appointment_duration'=>'30','booking_chair_count'=>'1','booking_chair_names'=>['Legacy doctor']];}
    private function login():void {session()->remove(['is_impersonating','impersonated_clinic_id','active_clinic_role_id']);session()->set(['identity'=>$this->prefix.'@example.test','user_id'=>$this->actor,'last_check'=>time(),'global_mode'=>false,'active_clinic_id'=>$this->clinic]);}
}
