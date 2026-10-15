<?php

declare(strict_types=1);

namespace Tests\Support;

use App\Libraries\ApiRuntimeContext;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;
use Config\Database;

/** Synthetic, committed fixtures: scheduling services must own their transactions. */
abstract class PhaseBFixture extends CIUnitTestCase
{
    use FeatureTestTrait;
    protected $db;
    protected int $clinic;
    protected int $otherClinic;
    protected int $actor;
    protected int $patient;
    protected int $plan;
    protected int $role;
    protected array $providers = [];
    protected array $users = [];
    protected string $email;
    protected string $prefix;

    protected function setUp(): void
    {
        parent::setUp();
        ApiRuntimeContext::clear();
        session()->destroy();
        $this->db = Database::connect();
        $this->assertSame(0, $this->db->transDepth);
        $this->prefix = 'phaseb_' . bin2hex(random_bytes(6));
        $this->clinic = $this->newClinic('a');
        $this->otherClinic = $this->newClinic('b');
        $this->db->table('plans')->insert(['name'=>$this->prefix,'status'=>'active','limits_json'=>'{}','features_json'=>'{}']);
        $this->plan = (int)$this->db->insertID();
        foreach ([$this->clinic, $this->otherClinic] as $cid) {
            $this->db->table('clinic_subscriptions')->insert(['clinic_id'=>$cid,'plan_id'=>$this->plan,'status'=>'active','start_at'=>'2020-01-01 00:00:00','end_at'=>null]);
        }
        $this->actor = $this->newUser('Operator');
        $this->email = $this->db->table('users')->where('id',$this->actor)->get()->getRow('email');
        $this->db->table('roles')->insert(['name'=>$this->prefix,'slug'=>$this->prefix,'clinic_id'=>$this->clinic,'is_active'=>1,'is_system'=>0,'created_by'=>$this->actor]);
        $this->role = (int)$this->db->insertID();
        foreach (['appointments'=>['view','create','edit','delete'], 'settings'=>['view','edit'], 'patients'=>['view']] as $module=>$actions) {
            foreach ($actions as $action) {
                $permission = $this->db->table('permissions')->where(['module'=>$module,'action'=>$action])->get()->getRowArray();
                if (!$permission) {
                    $this->db->table('permissions')->insert(['name'=>ucfirst($action).' '.$module,'module'=>$module,'action'=>$action]);
                    $permission = ['id'=>(int)$this->db->insertID()];
                }
                $this->db->table('role_permissions')->insert(['role_id'=>$this->role,'permission_id'=>$permission['id'],'granted'=>1]);
            }
        }
        $this->membership($this->actor,$this->clinic,$this->role);
        $doctor = $this->db->table('roles')->where('slug','doctor')->get()->getRowArray();
        if (!$doctor) { throw new \RuntimeException('Canonical synthetic doctor role missing'); }
        for ($i=0;$i<4;$i++) {
            $id=$this->newUser('Duplicate');
            $this->providers[]=$id;
            $this->membership($id,$this->clinic,(int)$doctor['id']);
        }
        $this->db->table('patients')->insert(['clinic_id'=>$this->clinic,'patient_id'=>$this->prefix,'first_name'=>'Synthetic','last_name'=>'Patient','gender'=>'other','status'=>'active','created_by'=>$this->actor]);
        $this->patient=(int)$this->db->insertID();
        foreach (['dynamic_doctor_scheduling_enabled'=>true,'working_days'=>['monday','tuesday','wednesday','thursday','friday','saturday','sunday'],'working_hours_start'=>'09:00','working_hours_end'=>'17:00','timezone'=>'Asia/Hebron','appointment_duration'=>30,'booking_chair_names'=>['Legacy room'],'booking_chair_count'=>1] as $key=>$value) {
            $this->setting($key,$value);
        }
        $this->login();
    }

    protected function tearDown(): void
    {
        ApiRuntimeContext::clear();
        while ($this->db && $this->db->transDepth>0) { $this->db->transRollback(); }
        if (isset($this->clinic)) {
            foreach (['appointment_status_history','clinical_procedure_status_history','clinical_procedures','treatments','activity_logs','clinic_provider_profiles','appointments','settings','patients','clinic_subscriptions','clinic_users'] as $table) {
                if ($this->db->tableExists($table) && $this->db->fieldExists('clinic_id',$table)) {
                    $this->db->table($table)->whereIn('clinic_id',[$this->clinic,$this->otherClinic])->delete();
                }
            }
            if (isset($this->role)) {
                $this->db->table('role_permissions')->where('role_id',$this->role)->delete();
                $this->db->table('roles')->where('id',$this->role)->delete();
            }
            foreach (['api_tokens','activity_logs','users_groups','user_roles','user_permissions'] as $table) {
                if ($this->users && $this->db->tableExists($table)) { $this->db->table($table)->whereIn('user_id',$this->users)->delete(); }
            }
            if ($this->users) { $this->db->table('users')->whereIn('id',$this->users)->delete(); }
            $this->db->table('clinics')->whereIn('id',[$this->clinic,$this->otherClinic])->delete();
            if (isset($this->plan)) { $this->db->table('plans')->where('id',$this->plan)->delete(); }
        }
        $this->db->resetTransStatus();
        session()->destroy();
        parent::tearDown();
    }

    protected function newClinic(string $suffix): int
    {
        $this->db->table('clinics')->insert(['name'=>$this->prefix.$suffix,'slug'=>$this->prefix.$suffix,'uuid'=>bin2hex(random_bytes(16)),'status'=>'active']);
        return (int)$this->db->insertID();
    }
    protected function newUser(string $name): int
    {
        $email=$this->prefix.'_'.count($this->users).'@example.test';
        $this->db->table('users')->insert(['ip_address'=>'127.0.0.1','username'=>$email,'email'=>$email,'password'=>password_hash('Synthetic-pass-42',PASSWORD_BCRYPT),'first_name'=>$name,'last_name'=>'Doctor','active'=>1,'created_on'=>time()]);
        $id=(int)$this->db->insertID(); $this->users[]=$id; return $id;
    }
    protected function membership(int $user,int $clinic,int $role): void
    {
        $this->db->table('clinic_users')->insert(['user_id'=>$user,'clinic_id'=>$clinic,'role_id'=>$role,'status'=>'active']);
    }
    protected function setting(string $key,$value): void
    {
        $this->db->table('settings')->where(['clinic_id'=>$this->clinic,'setting_key'=>$key])->delete();
        $type=is_array($value)?'json':(is_bool($value)?'boolean':(is_int($value)?'number':'string'));
        $serialized=is_array($value)?json_encode($value):(is_bool($value)?($value?'1':'0'):(string)$value);
        $this->db->table('settings')->insert(['clinic_id'=>$this->clinic,'setting_key'=>$key,'setting_value'=>$serialized,'setting_type'=>$type]);
    }
    protected function login(string $locale='en'): void
    {
        ApiRuntimeContext::clear();
        session()->set(['user_id'=>$this->actor,'identity'=>$this->email,'active_clinic_id'=>$this->clinic,'global_mode'=>false,'ui_locale'=>$locale,'is_impersonating'=>false]);
        $this->withSession(session()->get())->withHeaders([]);
    }
    protected function payload(array $extra=[]): array
    {
        return array_replace(['patient_id'=>$this->patient,'provider_user_id'=>$this->providers[0],'appointment_date'=>'2026-11-09','appointment_time'=>'10:00','duration'=>30,'appointment_type'=>'consultation','status'=>'scheduled','notes'=>'Synthetic scheduling acceptance'],$extra);
    }
}
