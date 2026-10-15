<?php

declare(strict_types=1);
namespace Tests\Unit;
use App\Services\ProviderDirectoryService;
use Tests\Support\PhaseBFixture;

final class PhaseBWorkflowRenderingTest extends PhaseBFixture
{
    public function testEnglishAndArabicDayboardsKeepEveryProviderAndUnresolvedVisitVisible(): void
    {
        helper(['form','url','settings','auth','navigation']);
        $context=(new ProviderDirectoryService($this->db))->getBookingContext($this->clinic);
        $rows=[];
        foreach($this->providers as $i=>$id) $rows[]=['id'=>100+$i,'provider_user_id'=>$id,'appointment_date'=>'2026-11-09','appointment_time'=>'10:00:00','duration'=>30,'status'=>'scheduled','first_name'=>'Synthetic '.$i,'last_name'=>'Patient'];
        $rows[]=['id'=>200,'provider_user_id'=>null,'chair_name'=>'Unresolved historical label','appointment_date'=>'2026-11-09','appointment_time'=>'11:00:00','duration'=>30,'status'=>'scheduled','first_name'=>'Unresolved','last_name'=>'Visit'];
        $rows[]=['id'=>201,'provider_user_id'=>99999999,'chair_name'=>'Inactive historical doctor','appointment_date'=>'2026-11-09','appointment_time'=>'12:00:00','duration'=>30,'status'=>'scheduled','first_name'=>'Inactive','last_name'=>'Visit'];
        foreach(['en','ar'] as $locale) {
            service('request')->setLocale($locale);
            $html=view('appointment/partials/doctor_dayboard',['booking_context'=>$context,'appointments'=>$rows,'dayboard_appointments'=>$rows,'selected_date'=>'2026-11-09']);
            foreach($this->providers as $id) $this->assertStringContainsString('data-provider-user-id="'.$id.'"',$html);
            $this->assertStringContainsString('Unresolved Visit',$html);
            $this->assertStringContainsString('Inactive Visit',$html);
            $this->assertStringNotContainsString('DoctorScheduling.',$html);
            $this->assertStringContainsString('provider_user_id='.$this->providers[3],$html);
        }
    }
    public function testSharedQuickFormUsesStableIdsWithoutSelectingTheFirstDoctor(): void
    {
        helper(['form','url','settings','auth','navigation']);
        foreach(['en','ar'] as $locale) {
            service('request')->setLocale($locale);
            $html=view('appointment/partials/form_fields',['booking_context'=>(new ProviderDirectoryService($this->db))->getBookingContext($this->clinic),'duration_options'=>[30,60]]);
            $this->assertStringContainsString('name="provider_user_id"',$html);
            $this->assertStringNotContainsString('name="chair_name"',$html);
            $doc=new \DOMDocument(); @$doc->loadHTML('<?xml encoding="UTF-8">'.$html);
            $xpath=new \DOMXPath($doc);
            $this->assertSame(4,$xpath->query('//select[@name="provider_user_id"]/option[@value!=""]')->length);
            $this->assertSame(0,$xpath->query('//select[@name="provider_user_id"]/option[@value!=""][@selected]')->length);
        }
    }
    public function testDisabledModeRetainsLegacyFieldsAndNoDoctorColumns(): void
    {
        helper(['form','url','settings','auth','navigation']);
        $this->setting('dynamic_doctor_scheduling_enabled',false);
        $context=(new ProviderDirectoryService($this->db))->getBookingContext($this->clinic);
        $this->assertFalse($context['enabled']); $this->assertSame([],$context['providers']);
        $html=view('appointment/partials/form_fields',['booking_context'=>$context,'chair_options'=>['Legacy room']]);
        $this->assertStringContainsString('name="chair_name"',$html);
        $this->assertStringNotContainsString('name="provider_user_id"',$html);
    }
}
