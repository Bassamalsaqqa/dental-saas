<?php

declare(strict_types=1);
namespace Tests\Unit;

use App\Libraries\ApiRuntimeContext;
use App\Services\AppointmentSchedulingService;
use App\Services\AppointmentSlotEditContext;
use Tests\Support\PhaseBFixture;

final class PR2SlotAdviceReviewTest extends PhaseBFixture
{
    private function book(array $extra=[]): int
    {
        $result=(new AppointmentSchedulingService($this->db))->createForClinic($this->clinic,$this->payload($extra),$this->actor);
        $this->assertTrue($result['success'],json_encode($result)); return (int)$result['id'];
    }
    private function times(?int $exclude=null,?string $proof=null,?string $chair=null): array
    {
        return array_column((new AppointmentSchedulingService($this->db))->availableSlots($this->clinic,'2026-11-09',30,$this->providers[0],$exclude,$chair,$proof),'value');
    }
    public function testArbitraryExclusionCannotHideBookingAndValidEditProofDoesNotAuthorizeCreation(): void
    {
        $id=$this->book();$before=$this->db->table('appointments')->where('id',$id)->get()->getRowArray();
        $this->assertNotContains('10:00',$this->times());
        $this->assertNotContains('10:00',$this->times($id));
        $proof=(new AppointmentSlotEditContext($this->db))->issue($this->clinic,$id);$this->assertNotNull($proof);
        $this->assertContains('10:00',$this->times($id,$proof));
        $this->assertNotContains('10:00',$this->times($id,str_repeat('0',64)));
        $result=(new AppointmentSchedulingService($this->db))->createForClinic($this->clinic,$this->payload(['exclude_id'=>$id,'edit_context'=>$proof]),$this->actor);
        $this->assertFalse($result['success']);$this->assertSame('SLOT_UNAVAILABLE',$result['code']);
        $this->assertSame($before,$this->db->table('appointments')->where('id',$id)->get()->getRowArray());
        $this->assertSame(1,$this->db->table('appointments')->where('clinic_id',$this->clinic)->countAllResults());
    }
    public function testProofBoundToAppointmentClinicSessionAndCurrentEditPermission(): void
    {
        $one=$this->book();$two=$this->book(['appointment_time'=>'11:00']);$contexts=new AppointmentSlotEditContext($this->db);
        $proof=$contexts->issue($this->clinic,$one);
        $this->assertNull($contexts->resolve($this->clinic,$two,$proof));
        $this->assertNull($contexts->resolve($this->otherClinic,$one,$proof));
        $this->assertNotContains('11:00',$this->times($two,$proof));
        ApiRuntimeContext::setUserId($this->actor);ApiRuntimeContext::setClinicId($this->clinic);
        $this->assertNull($contexts->resolve($this->clinic,$one,$proof));ApiRuntimeContext::clear();
        $permissionIds=array_column($this->db->table('permissions')->where('module','appointments')->whereIn('action',['edit','update'])->get()->getResultArray(),'id');
        $this->db->table('role_permissions')->where('role_id',$this->role)->whereIn('permission_id',$permissionIds)->delete();
        $this->assertNull($contexts->resolve($this->clinic,$one,$proof));
        $this->assertNotContains('10:00',$this->times($one,$proof));
        $this->assertNull($contexts->issue($this->clinic,$two));
    }
    public function testExpiredOrDifferentBrowserSessionProofIsIgnored(): void
    {
        $id=$this->book();$context=new AppointmentSlotEditContext($this->db);$proof=$context->issue($this->clinic,$id);
        $stored=session('appointment_slot_edit_contexts');$stored[$this->clinic.':'.$id]['expires_at']=time()-1;session()->set('appointment_slot_edit_contexts',$stored);
        $this->assertNotContains('10:00',$this->times($id,$proof));
        $fresh=$context->issue($this->clinic,$id);$this->assertContains('10:00',$this->times($id,$fresh));
        session()->remove('appointment_slot_edit_contexts');session()->destroy();$this->login();$this->assertNotContains('10:00',$this->times($id,$fresh));
    }
    public function testLegacyResourcesFollowSameAdvisoryExclusionBoundary(): void
    {
        $this->setting('dynamic_doctor_scheduling_enabled',false);$id=$this->book(['chair_name'=>'Legacy room']);
        $this->assertNotContains('10:00',$this->times($id,null,'Legacy room'));
        $proof=(new AppointmentSlotEditContext($this->db))->issue($this->clinic,$id);
        $this->assertContains('10:00',$this->times($id,$proof,'Legacy room'));
        $this->assertSame('Legacy room',$this->db->table('appointments')->where('id',$id)->get()->getRow('chair_name'));
        $this->assertNull($this->db->table('appointments')->where('id',$id)->get()->getRow('provider_user_id'));
    }
    public function testRoutedEditProofAllowsOnlyItsVerifiedAppointmentAndMalformedAdviceFailsClosed(): void
    {
        $id=$this->book();$base='/appointment/available-time-slots?date=2026-11-09&duration=30&provider_user_id='.$this->providers[0];
        $raw=$this->withSession(session()->get())->withHeaders([])->get($base.'&exclude_id='.$id);$raw->assertStatus(200);
        $decoded=json_decode($raw->response()->getBody(),true);$this->assertIsArray($decoded,$raw->response()->getBody());
        $this->assertNotContains('10:00',array_column($decoded,'value'));
        $edit=$this->withSession(session()->get())->withHeaders([])->get('/appointment/'.$id.'/edit');$edit->assertStatus(200);
        $this->assertSame(1,preg_match('/const slotEditContext = ("[a-f0-9]{64}");/',$edit->getBody(),$match));
        $proof=json_decode($match[1],true);
        $valid=$this->withSession(session()->get())->withHeaders([])->get($base.'&exclude_id='.$id.'&edit_context='.$proof);$valid->assertStatus(200);
        $this->assertContains('10:00',array_column(json_decode($valid->response()->getBody(),true),'value'));
        foreach(['exclude_id[]=1','exclude_id=1e2','exclude_id='.$id.'&edit_context[]=bad'] as $query){
            $response=$this->withSession(session()->get())->withHeaders([])->get($base.'&'.$query);$response->assertStatus(200);
            $this->assertNotContains('10:00',array_column(json_decode($response->response()->getBody(),true),'value'));
        }
    }
}
