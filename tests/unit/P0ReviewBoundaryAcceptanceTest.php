<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Libraries\ApiRuntimeContext;
use App\Services\ClinicMembershipService;
use App\Services\PermissionService;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;
use Config\Database;
use PHPUnit\Framework\Attributes\DataProvider;

/** Reviewer-owned exploit and real transaction regressions. Fixtures commit only in the disposable DB. */
final class P0ReviewBoundaryAcceptanceTest extends CIUnitTestCase
{
    use FeatureTestTrait;

    protected $db;
    private string $prefix;
    private int $actor;
    private int $target;
    private int $clinicA;
    private int $clinicB;
    private int $actorRole;
    private int $staffRole;
    private int $plan;
    private array $users = [];

    protected function setUp(): void
    {
        parent::setUp();
        ApiRuntimeContext::clear();
        $this->db = Database::connect();
        $this->assertSame(0, $this->db->transDepth);
        $this->prefix = 'boundary-review-' . bin2hex(random_bytes(6));
        $this->actor = $this->user('actor');
        $this->target = $this->user('target');
        $this->clinicA = $this->clinic('a');
        $this->clinicB = $this->clinic('b');
        $this->actorRole = $this->customRole('manager', $this->clinicA);
        $this->staffRole = $this->customRole('staff', null);
        foreach (['create', 'edit', 'delete'] as $action) {
            $permission = $this->db->table('permissions')->where('module', 'users')->where('action', $action)->get()->getRowArray();
            $this->assertNotEmpty($permission, 'Canonical disposable permission missing: users:' . $action);
            $this->db->table('role_permissions')->insert(['role_id' => $this->actorRole, 'permission_id' => $permission['id']]);
        }
        $this->member($this->clinicA, $this->actor, $this->actorRole);
        $this->member($this->clinicA, $this->target, $this->staffRole);
        $this->member($this->clinicB, $this->target, $this->staffRole);
        $this->db->table('plans')->insert(['name' => $this->prefix, 'status' => 'active', 'limits_json' => '{"users_max":100}', 'features_json' => '{}']);
        $this->plan = (int) $this->db->insertID();
        foreach ([$this->clinicA, $this->clinicB] as $clinic) {
            $this->db->table('clinic_subscriptions')->insert(['clinic_id' => $clinic, 'plan_id' => $this->plan,
                'status' => 'active', 'start_at' => date('Y-m-d H:i:s', time() - 3600), 'end_at' => null]);
        }
        $this->login();
    }

    protected function tearDown(): void
    {
        ApiRuntimeContext::clear();
        while ($this->db && $this->db->transDepth > 0) {
            $this->db->transRollback();
        }
        $this->db->resetTransStatus();
        if (isset($this->clinicA, $this->clinicB)) {
            foreach (['settings', 'plan_audits', 'clinic_subscriptions', 'clinic_users'] as $table) {
                $this->db->table($table)->whereIn('clinic_id', [$this->clinicA, $this->clinicB])->delete();
            }
        }
        if (isset($this->actor)) {
            $this->db->table('control_plane_audits')->where('actor_user_id', $this->actor)->delete();
        }
        if (isset($this->prefix)) {
            $ownedUsers = $this->db->table('users')->select('id')->like('email', $this->prefix . '-', 'after')->get()->getResultArray();
            $userIds = array_map(static fn(array $row): int => (int) $row['id'], $ownedUsers);
            if ($userIds) {
                foreach (['api_tokens', 'user_roles', 'users_groups', 'user_permissions'] as $table) {
                    $this->db->table($table)->whereIn('user_id', $userIds)->delete();
                }
                $roles = $this->db->table('roles')->select('id')->whereIn('created_by', $userIds)->get()->getResultArray();
                $roleIds = array_column($roles, 'id');
                if ($roleIds) {
                    $this->db->table('role_permissions')->whereIn('role_id', $roleIds)->delete();
                    $this->db->table('roles')->whereIn('id', $roleIds)->delete();
                }
                $this->db->table('users')->whereIn('id', $userIds)->delete();
            }
        }
        if (isset($this->clinicA, $this->clinicB)) {
            $this->db->table('clinics')->whereIn('id', [$this->clinicA, $this->clinicB])->delete();
        }
        if (isset($this->plan)) {
            $this->db->table('plans')->where('id', $this->plan)->delete();
        }
        session()->remove(['identity', 'email', 'user_id', 'last_check', 'active_clinic_id', 'active_clinic_role_id',
            'active_clinic_membership_id', 'ui_locale', 'cp_ui_locale', 'global_mode', 'is_impersonating', 'impersonated_clinic_id']);
        parent::tearDown();
    }

    #[DataProvider('locales')]
    public function testRealPlatformPostLinksPreservesAndReactivates(string $locale): void
    {
        $before=$this->db->table('users')->where('id',$this->target)->get()->getRowArray();
        $this->db->table('clinic_users')->where('clinic_id',$this->clinicA)->where('user_id',$this->target)->delete();
        $this->platformSession();session()->set('cp_ui_locale',$locale);
        $response=$this->platformPost(['clinic_id'=>(string)$this->clinicA,'role_id'=>(string)$this->staffRole]);
        $response->assertStatus(302);
        $this->assertStringContainsString('/controlplane/users/'.$this->target,$response->response()->getHeaderLine('Location'));
        $membership=$this->membership($this->clinicA,$this->target);
        $this->assertSame($this->staffRole,(int)$membership['role_id']);
        $this->assertSame('active',$membership['status']);
        $this->assertSame(lang('UserRole.membershipSaved', [], $locale),session()->getFlashdata('success'));
        $this->platformPost(['clinic_id'=>(string)$this->clinicA,'role_id'=>(string)$this->actorRole])->assertStatus(302);
        $this->assertSame($this->staffRole,(int)$this->membership($this->clinicA,$this->target)['role_id']);
        $this->assertNotEmpty(session()->getFlashdata('error'));
        $this->db->table('clinic_users')->where('id',$membership['id'])->update(['status'=>'inactive']);
        $this->platformPost(['clinic_id'=>(string)$this->clinicA,'role_id'=>(string)$this->actorRole])->assertStatus(302);
        $reactivated=$this->membership($this->clinicA,$this->target);
        $this->assertSame($membership['id'],$reactivated['id']);
        $this->assertSame('active',$reactivated['status']);
        $this->assertSame($this->actorRole,(int)$reactivated['role_id']);
        $this->assertSame(1,$this->db->table('clinic_users')->where('clinic_id',$this->clinicA)->where('user_id',$this->target)->countAllResults());
        $this->assertSame($before,$this->db->table('users')->where('id',$this->target)->get()->getRowArray());
        $this->assertSame($this->staffRole,(int)$this->membership($this->clinicB,$this->target)['role_id']);
    }
    public static function locales():array {return [['en'],['ar']];}

    #[DataProvider('badIds')]
    public function testRealPlatformPostRejectsNonIdsWithoutMutation(string $field,mixed $value): void
    {
        $this->platformSession();
        $before=$this->membership($this->clinicA,$this->target);$audit=$this->auditCount();
        $data=['clinic_id'=>(string)$this->clinicA,'role_id'=>(string)$this->staffRole];$data[$field]=$value;
        $this->platformPost($data)->assertStatus(302);
        $this->assertNotEmpty(session()->getFlashdata('error'));
        $this->assertSame($before,$this->membership($this->clinicA,$this->target));
        $this->assertSame($audit,$this->auditCount());
    }
    public static function badIds():array
    {
        $out=[];foreach(['clinic_id','role_id'] as $field) foreach([null,[],['1'],'0','-1','+1','1.0','1e2','1garbage',' 1','9223372036854775808'] as $i=>$value) $out[$field.'-'.$i]=[$field,$value];return $out;
    }

    public function testAppApiNeverEnablesWebImpersonationEvenForARealMember(): void
    {
        $this->platformGrant();
        foreach([true,false] as $withMembership){
            $this->login();
            if(!$withMembership)$this->db->table('clinic_users')->where('clinic_id',$this->clinicA)->where('user_id',$this->actor)->delete();
            session()->set(['is_impersonating'=>true,'impersonated_clinic_id'=>$this->clinicA]);
            $response=$this->withSession(session()->get())->withHeaders([])->get('/app-api/v1/patients');
            $response->assertStatus(403);
            $this->assertSame('tenant_context_forbidden',json_decode($response->getJSON(),true)['error']['code']??null);
            $this->assertStringNotContainsString('<html',$response->response()->getBody());
            $this->login();session()->set(['is_impersonating'=>true,'impersonated_clinic_id'=>$this->clinicA]);
            $hash=service('security')->getHash();$config=config('Security');
            $mutation=$this->withSession(session()->get())->withHeaders(['Cookie'=>$config->cookieName.'='.$hash,'X-CSRF-TOKEN'=>$hash])->post('/app-api/v1/patients',[$config->tokenName=>$hash,'first_name'=>'Synthetic','last_name'=>'Denied']);
            $mutation->assertStatus(403);
            $this->assertSame('tenant_context_forbidden',json_decode($mutation->getJSON(),true)['error']['code']??null);
        }
    }

    public function testWebImpersonationUsesSessionRegardlessOfUnrelatedBearerHeader():void
    {
        $this->platformGrant();$this->login();
        $this->db->table('clinic_users')->where('clinic_id',$this->clinicA)->where('user_id',$this->actor)->delete();
        session()->set(['is_impersonating'=>true,'impersonated_clinic_id'=>$this->clinicA]);
        $this->withSession(session()->get())->withHeaders(['Authorization'=>'Bearer unrelated-invalid-token'])->get('/profile')->assertStatus(200);
        $api=$this->withSession(session()->get())->withHeaders(['Authorization'=>'Bearer unrelated-invalid-token','X-Clinic-Id'=>(string)$this->clinicA])->get('/api/v1/search/users');
        $api->assertStatus(401);
        $this->assertSame('unauthenticated',json_decode($api->getJSON(),true)['error']['code']??null);
    }

    #[DataProvider('invalidRoleKinds')]
    public function testCanonicalRoleDenialAcrossActualSessionEntryRoutes(string $kind):void
    {
        if($kind==='inactive') {
            $this->db->table('roles')->where('id',$this->actorRole)->update(['is_active'=>0]);
            $roleId=$this->actorRole;
        } elseif($kind==='foreign') {
            $roleId=$this->customRole('foreign',$this->clinicB);
        } elseif($kind==='platform') {
            $roleId=$this->systemRole('super_admin');
        } else {
            // Canonical FK cascades a deleted role's memberships; do not disable constraints.
            $this->db->table('roles')->where('id',$this->actorRole)->delete();
            $roleId=null;
        }
        if($roleId!==null)$this->db->table('clinic_users')->where('clinic_id',$this->clinicA)->where('user_id',$this->actor)->update(['role_id'=>$roleId]);
        foreach(['/profile','/file/download/999999999','/file/inline/999999999','/api/notifications','/app-api/v1/patients'] as $path){
            $this->login();session()->set(['active_clinic_role_id'=>$this->staffRole,'active_clinic_membership_id'=>999999]);
            $response=$this->withSession(session()->get())->withHeaders(['Accept'=>'application/json','X-Requested-With'=>'XMLHttpRequest'])->get($path);
            $response->assertStatus(403);
            $this->assertSame('tenant_context_forbidden',json_decode($response->getJSON(),true)['error']['code']??null,$path);
            $this->assertNull(session()->get('active_clinic_role_id'));
            $this->assertNull(session()->get('active_clinic_membership_id'));
            $this->assertStringNotContainsString('<html',$response->response()->getBody());
        }
    }
    public static function invalidRoleKinds():array {return [['inactive'],['foreign'],['platform'],['missing']];}

    private function platformGrant():void
    {
        $this->db->table('user_roles')->insert(['user_id'=>$this->actor,'role_id'=>$this->systemRole('super_admin'),'assigned_by'=>$this->actor,'assigned_at'=>date('Y-m-d H:i:s'),'is_active'=>1]);
    }
    private function platformSession():void
    {
        $this->platformGrant();$this->login();
        session()->remove(['active_clinic_id','active_clinic_role_id','active_clinic_membership_id']);
        session()->set('global_mode',true);
    }
    private function platformPost(array $data)
    {
        $hash=service('security')->getHash();$config=config('Security');
        return $this->withSession(session()->get())->withHeaders(['Referer'=>base_url('controlplane/users/'.$this->target),'Cookie'=>$config->cookieName.'='.$hash,'X-CSRF-TOKEN'=>$hash])->post('/controlplane/users/'.$this->target.'/memberships/add',array_merge($data,[$config->tokenName=>$hash]));
    }

    private function login(): void
    {
        session()->remove(['is_impersonating', 'impersonated_clinic_id', 'active_clinic_role_id', 'active_clinic_membership_id']);
        session()->set(['identity' => $this->prefix . '-actor@example.test', 'user_id' => $this->actor,
            'last_check' => time(), 'global_mode' => false, 'active_clinic_id' => $this->clinicA]);
    }
    private function auditCount(): int { return $this->db->table('control_plane_audits')->where('actor_user_id', $this->actor)->countAllResults(); }
    private function membership(int $clinic, int $user): array { return $this->db->table('clinic_users')->where('clinic_id', $clinic)->where('user_id', $user)->get()->getRowArray(); }
    private function user(string $name, int $active = 1): int
    {
        $this->db->table('users')->insert(['ip_address' => '127.0.0.1', 'username' => $this->prefix . '-' . $name,
            'email' => $this->prefix . '-' . $name . '@example.test', 'password' => password_hash('Synthetic-password-42', PASSWORD_BCRYPT),
            'first_name' => 'Synthetic', 'last_name' => 'Fixture', 'active' => $active, 'created_on' => time()]);
        return (int) $this->db->insertID();
    }
    private function clinic(string $suffix): int
    {
        $this->db->table('clinics')->insert(['name' => $this->prefix . '-' . $suffix, 'slug' => $this->prefix . '-' . $suffix,
            'uuid' => bin2hex(random_bytes(16)), 'status' => 'active']);
        return (int) $this->db->insertID();
    }
    private function customRole(string $name, ?int $clinic): int
    {
        $this->db->table('roles')->insert(['name' => $this->prefix . '-' . $name, 'slug' => $this->prefix . '-' . $name,
            'clinic_id' => $clinic, 'created_by' => $this->actor, 'is_active' => 1, 'is_system' => 0]);
        return (int) $this->db->insertID();
    }
    private function member(int $clinic, int $user, int $role): void
    {
        $this->db->table('clinic_users')->insert(['clinic_id' => $clinic, 'user_id' => $user, 'role_id' => $role, 'status' => 'active']);
    }
    private function systemRole(string $slug): int
    {
        $row = $this->db->table('roles')->where('slug', $slug)->where('clinic_id', null)->get()->getRowArray();
        $this->assertNotEmpty($row);
        return (int) $row['id'];
    }
}
