<?php

declare(strict_types=1);
namespace Tests\Unit;

use App\Controllers\ControlPlane\Users;
use App\Services\PermissionService;
use CodeIgniter\Test\CIUnitTestCase;
use Config\Database;

final class ControlPlaneSuperAdminDisplayTest extends CIUnitTestCase
{
    protected $db;
    protected function setUp(): void
    {
        parent::setUp();
        helper('settings');
        $this->db = Database::connect();
        $this->assertTrue($this->db->transBegin());
        session()->remove(['active_clinic_id','is_impersonating','impersonated_clinic_id']);
        session()->set('global_mode',true);
    }
    protected function tearDown(): void
    {
        $this->db->transRollback();
        session()->remove('global_mode');
        parent::tearDown();
    }
    public function testDirectoryLabelUsesCanonicalPlatformAuthority(): void
    {
        $prefix='p2-display-'.bin2hex(random_bytes(5));
        $platform=(new PermissionService())->getSuperAdminRoleId();
        $group=$this->db->table('groups')->where('name','admin')->get()->getRowArray();
        $this->assertNotEmpty($platform, 'Canonical platform role required.');
        if (!$group) {
            $this->db->table('groups')->insert(['name'=>'admin','description'=>'Synthetic P2 group fixture']);
            $group=['id'=>(int)$this->db->insertID()];
        }
        $expected=[];
        foreach(['group_only','valid','expired','inactive_assignment','disabled_user'] as $kind){
            $this->db->table('users')->insert(['ip_address'=>'127.0.0.1','username'=>$prefix.'-'.$kind,'email'=>$prefix.'-'.$kind.'@example.test','password'=>'unused-synthetic-hash','first_name'=>'Synthetic','last_name'=>$kind,'active'=>$kind==='disabled_user'?0:1,'created_on'=>time()]);
            $id=(int)$this->db->insertID();
            // Real platform assignment does not require an authentication admin-group row.
            if($kind!=='valid')$this->db->table('users_groups')->insert(['user_id'=>$id,'group_id'=>$group['id']]);
            if($kind!=='group_only')$this->db->table('user_roles')->insert(['user_id'=>$id,'role_id'=>$platform,'assigned_by'=>$id,'assigned_at'=>gmdate('Y-m-d H:i:s'),'is_active'=>$kind==='inactive_assignment'?0:1,'expires_at'=>$kind==='expired'?gmdate('Y-m-d H:i:s',time()-3600):null]);
            $expected[$id]=$kind==='valid'?'Super Admin':'User';
        }
        $method=new \ReflectionMethod(Users::class,'buildUserRows');
        $rows=$method->invoke(new Users(),'',$prefix);
        $this->assertCount(5,$rows);
        foreach($rows as $row){
            $this->assertSame($expected[(int)$row['id']],$row['role'],$row['email']);
            $this->assertSame($row['role']==='Super Admin',(new PermissionService())->isSuperAdmin((int)$row['id']));
        }
    }
}
