<?php

declare(strict_types=1);
namespace Tests\Unit;

use App\Services\AppointmentSchedulingService;
use App\Services\ProviderDirectoryService;
use Tests\Support\PhaseBFixture;

final class PR2EscapingReviewTest extends PhaseBFixture
{
    public function testPatientNamesAndPhoneRemainLiteralTextInEnglishArabicBookingSurfaces(): void
    {
        $created=(new AppointmentSchedulingService($this->db))->createForClinic($this->clinic,$this->payload(),$this->actor);
        $this->assertTrue($created['success']);
        $this->db->table('patients')->where('id',$this->patient)->update(['first_name'=>'<svg>','last_name'=>'</option><script>bad()</script>','phone'=>'"><img src=x>']);
        foreach(['en','ar'] as $locale) {
            $this->login($locale);
            foreach(['/appointment/'.$created['id'].'/edit','/appointment/create?patient_id='.$this->patient] as $url) {
                $response=$this->withSession(session()->get())->withHeaders([])->get($url);$response->assertStatus(200);
                $html=$response->getBody();$doc=new \DOMDocument();@$doc->loadHTML('<?xml encoding="UTF-8">'.$html);$xpath=new \DOMXPath($doc);
                $this->assertSame(0,$xpath->query('//svg|//img[@src="x"]|//script[contains(text(),"bad()")]')->length);
                $option=$xpath->query('//select[@name="patient_id"]/option[@value="'.$this->patient.'"]')->item(0);
                $this->assertNotNull($option);
                $this->assertStringContainsString('<svg> </option><script>bad()</script>',$option->textContent);
                $this->assertStringContainsString('"><img src=x>',$option->textContent);
            }
        }
    }

    public function testCaptureActualDuplicateDoctorControlsAndColumnsForMobileBrowserRegression(): void
    {
        helper(['form','url','settings','auth','navigation']);
        foreach($this->providers as $provider) $this->db->table('users')->where('id',$provider)->update(['first_name'=>str_repeat('A',50),'last_name'=>str_repeat('B',50)]);
        $created=(new AppointmentSchedulingService($this->db))->createForClinic($this->clinic,$this->payload(),$this->actor);$this->assertTrue($created['success']);
        foreach(['en','ar'] as $locale) {
            foreach($this->providers as $provider) $this->db->table('users')->where('id',$provider)->update(['first_name'=>str_repeat($locale==='ar'?"\u{0623}":'A',50),'last_name'=>str_repeat($locale==='ar'?"\u{0644}":'B',50)]);
            $this->login($locale);service('request')->setLocale($locale);
            $context=(new ProviderDirectoryService($this->db))->getBookingContext($this->clinic);
            $create=$this->withSession(session()->get())->withHeaders([])->get('/appointment/create');$create->assertStatus(200);
            $edit=$this->withSession(session()->get())->withHeaders([])->get('/appointment/'.$created['id'].'/edit');$edit->assertStatus(200);
            file_put_contents(ROOTPATH.'tmp/pr2-rendered-edit-'.$locale.'.html',$edit->getBody());
            $shared=view('appointment/partials/form_fields',['booking_context'=>$context]);
            $quick=view('appointment/partials/quick_create_modal',['booking_context'=>$context]);
            $surfaces=['full-create'=>$create->getBody(),'edit'=>$edit->getBody(),'shared'=>$shared,'quick'=>$quick];$controls='';
            foreach($surfaces as $name=>$html) {
                $doc=new \DOMDocument();@$doc->loadHTML('<?xml encoding="UTF-8">'.$html);$xpath=new \DOMXPath($doc);
                $select=$xpath->query('//select[@name="provider_user_id"]')->item(0);$this->assertNotNull($select,$name);
                $texts=[];
                foreach($this->providers as $id) {
                    $option=$xpath->query('//select[@name="provider_user_id"]/option[@value="'.$id.'"]')->item(0);
                    $this->assertNotNull($option,$name);$this->assertStringContainsString('#'.$id,$option->textContent);$texts[]=$option->textContent;
                }
                $this->assertSame(4,count(array_unique($texts)),$name);
                $select->setAttribute('id','pr2-'.$name.'-provider');
                $controls.='<section data-booking-surface="'.$name.'"><label>'.$name.'</label>'.$doc->saveHTML($select).'</section>';
            }
            $board=view('appointment/partials/doctor_dayboard',['booking_context'=>$context,'appointments'=>[],'selected_date'=>'2026-11-09']);
            $dir=$locale==='ar'?'rtl':'ltr';$css='file:///'.str_replace('\\','/',ROOTPATH).'public/assets/css/tailwind.css';
            $html='<!doctype html><html lang="'.$locale.'" dir="'.$dir.'"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"><link rel="stylesheet" href="'.$css.'"><link rel="stylesheet" href="pr2-select2-4.1.0-rc.0.min.css"><style>*{box-sizing:border-box}body{margin:0;padding:12px;font-family:Arial,sans-serif}section{width:100%;margin-block-end:12px}select{width:100%;max-width:100%}</style></head><body>'.$controls.$board.'<script src="pr2-jquery-3.7.1.min.js"></script><script src="pr2-select2-4.1.0-rc.0.min.js"></script></body></html>';
            file_put_contents(ROOTPATH.'tmp/pr2-mobile-'.$locale.'.html',$html);
            $this->assertStringNotContainsString('DoctorScheduling.',$board);
        }
    }
}
